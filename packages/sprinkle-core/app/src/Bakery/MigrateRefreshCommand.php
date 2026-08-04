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
 * migrate:refresh Bakery Command.
 * Refresh the database by rolling back the last migrations and running them up again.
 */
class MigrateRefreshCommand extends Command
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
        $this->setName('migrate:refresh')
             ->setDescription('Rollback the last migration operation and run it up again')
             ->addOption('pretend', 'p', InputOption::VALUE_NONE, 'Run actions in "dry run" mode.')
             ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force the operation to run without confirmation.')
             ->addOption('database', 'd', InputOption::VALUE_REQUIRED, 'The database connection to use.')
             ->addOption('steps', 's', InputOption::VALUE_REQUIRED, 'Number of batch to rollback', 1);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Migration refresh');

        // Get options
        $steps = (int) $input->getOption('steps');
        $pretend = (bool) $input->getOption('pretend');
        $force = (bool) $input->getOption('force');

        // Set connection to the selected database
        $database = $input->getOption('database');
        if ($database !== '') {
            $this->io->info("Running {$this->getName()} with `$database` database connection");
            $this->db->getDatabaseManager()->setDefaultConnection($database);
        }

        // Display steps in verbose mode.
        if ($this->io->isVerbose()) {
            $this->io->info("Refreshing $steps step(s)");
        }

        // Switch to pretend if requested
        if ($pretend) {
            $this->io->warning("This command can't be pretended.");

            return self::FAILURE;
        }

        return $this->executeRefresh($steps, $force);
    }

    /**
     * Run refresh.
     *
     * @param int  $steps
     * @param bool $force Force command to run without confirmation
     *
     * @return int Symfony exit code
     */
    protected function executeRefresh(int $steps, bool $force): int
    {
        // Get migrations for rollback
        try {
            $migrations = $this->migrator->getMigrationsForRollback($steps);
        } catch (MigrationDependencyNotMetException|MigrationNotFoundException $e) {
            $this->io->error("Database refresh can't be performed. " . $e->getMessage());

            return self::FAILURE;
        }

        // Don't go further if no migration to rollback
        if (count($migrations) === 0) {
            $this->io->warning('Nothing to refresh');

            return self::SUCCESS;
        }

        // Show migrations about to be rollback
        if ($this->config->getBool('bakery.confirm_sensitive_command', true) || $this->io->isVerbose()) {
            $this->io->section('Migrations to refresh');
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
            // N.B.: Should not happens, only if two operations get executed
            // while waiting for confirmation.
            $this->io->warning('Nothing rollbacked !');

            return self::FAILURE;
        }

        // Display info
        $this->io->section('Migrations rollbacked : ');
        $this->io->listing($rollbacked);

        // Get pending migrations. Might not be displayed, but need to test
        // dependencies in case of issue.
        try {
            $pending = $this->migrator->getPending();
        } catch (MigrationDependencyNotMetException|MigrationNotFoundException $e) {
            $this->io->error("Database migration can't be performed. " . $e->getMessage());

            return self::FAILURE;
        }

        // Show migrations about to be rollback
        if ($this->io->isVerbose()) {
            $this->io->section('Pending migrations');
            $this->io->listing($pending);
        }

        // Perform migrate.
        try {
            $migrated = $this->migrator->migrate();
        } catch (\Exception $e) {
            $this->io->error($e->getMessage());

            return self::FAILURE;
        }

        // Display info
        if (count($migrated) === 0) {
            // N.B.: Should not happens, only if pending get empty while
            // waiting for confirmation
            $this->io->warning('Nothing migrated !');

            return self::FAILURE;
        }

        // Display info
        $this->io->section('Migrations applied : ');
        $this->io->listing($migrated);

        $this->io->success('Refresh successful !');

        return self::SUCCESS;
    }
}
