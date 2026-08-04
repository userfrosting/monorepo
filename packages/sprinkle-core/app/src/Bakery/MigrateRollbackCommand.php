<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Bakery;

use DI\Attribute\Inject;
use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use UserFrosting\Bakery\WithSymfonyStyle;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Core\Database\Migrator\Migrator;
use UserFrosting\Sprinkle\Core\Exceptions\MigrationDependencyNotMetException;
use UserFrosting\Sprinkle\Core\Exceptions\MigrationNotFoundException;

/**
 * migrate:rollback Bakery Command
 * Rollback the last migrations ran against the database.
 */
class MigrateRollbackCommand extends Command
{
    use WithSymfonyStyle;

    #[Inject]
    protected Migrator $migrator;

    #[Inject]
    protected Capsule $db;

    #[Inject]
    protected Config $config;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('migrate:rollback')
             ->setDescription('Rollback last database migration')
             ->addOption('pretend', 'p', InputOption::VALUE_NONE, 'Run actions in "dry run" mode.')
             ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force the operation to run without confirmation.')
             ->addOption('database', 'd', InputOption::VALUE_REQUIRED, 'The database connection to use.')
             ->addOption('steps', 's', InputOption::VALUE_REQUIRED, 'Number of batch to rollback.', 1);
        //  ->addOption('migration', 'm', InputOption::VALUE_REQUIRED, 'The specific migration to rollback.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Database Migration Rollback');

        // Get options
        $steps = (int) $input->getOption('steps');
        $pretend = (bool) $input->getOption('pretend');
        // $migration = (string) $input->getOption('migration');
        $force = (bool) $input->getOption('force');

        // Set connection to the selected database
        $database = $input->getOption('database');
        if ($database !== '') {
            $this->io->info("Running {$this->getName()} with `$database` database connection");
            $this->db->getDatabaseManager()->setDefaultConnection($database);
        }

        // TODO : Do single $migration...
        // validateRollbackMigration

        // Display steps in verbose mode.
        if ($this->io->isVerbose()) {
            $this->io->info("Rolling back $steps step(s)");
        }

        // Switch to pretend if requested
        if ($pretend) {
            return $this->executePretendToRollback($steps);
        }

        return $this->executeRollback($steps, $force);
    }

    /**
     * Run migrate.
     *
     * @param int  $steps
     * @param bool $force Force command to run without confirmation
     *
     * @return int Symfony exit code
     */
    protected function executeRollback(int $steps, bool $force): int
    {
        // Get migrations for rollback
        try {
            $migrations = $this->migrator->getMigrationsForRollback($steps);
        } catch (MigrationDependencyNotMetException|MigrationNotFoundException $e) {
            $this->io->error("Database rollback can't be performed. " . $e->getMessage());

            return self::FAILURE;
        }

        // Don't go further if no migration to rollback
        if (count($migrations) === 0) {
            $this->io->success('Nothing to rollback');

            return self::SUCCESS;
        }

        // Show migrations about to be rollback
        if ($this->config->getBool('bakery.confirm_sensitive_command', true) || $this->io->isVerbose()) {
            $this->io->section('Migrations to rollback');
            $this->io->listing($migrations);
        }

        // Confirm action if required (for example in production mode).
        if ($this->config->getBool('bakery.confirm_sensitive_command', true) && !$force) {
            if (!$this->io->confirm('Do you really wish to continue ?', false)) {
                return self::SUCCESS;
            }
        }

        // Perform rollback.
        try {
            $rollbacked = $this->migrator->rollback($steps);
        } catch (\Exception $e) {
            $this->io->error($e->getMessage());

            return self::FAILURE;
        }

        if (count($rollbacked) === 0) {
            // N.B.: Should not happens, only if tow operation get executed
            // while waiting for confirmation.
            $this->io->warning('Nothing rollbacked !');
        } else {
            $this->io->section('Migrations rollbacked : ');
            $this->io->listing($rollbacked);
            $this->io->success('Rollback successful !');
        }

        return self::SUCCESS;
    }

    // protected function executeMigrationRollback($migration): int
    // {

    // }

    /**
     * Run the migrate in pretend mode.
     *
     * @return int Symfony exit code
     */
    protected function executePretendToRollback(int $steps): int
    {
        $this->io->note("Running {$this->getName()} in pretend mode");

        // Get pretend queries
        try {
            $data = $this->migrator->pretendToRollback($steps);
        } catch (\Exception $e) {
            $this->io->error("Database rollback can't be performed. " . $e->getMessage());

            return self::FAILURE;
        }

        if (count($data) === 0) {
            $this->io->success('Nothing to rollback');

            return self::SUCCESS;
        }

        // Display information
        foreach ($data as $migration => $queries) {
            $this->io->section($migration);
            $this->io->listing(array_column($queries, 'query'));
        }

        return self::SUCCESS;
    }

    // protected function executePretendToMigrationRollback($migration): int
    // {

    // }
}
