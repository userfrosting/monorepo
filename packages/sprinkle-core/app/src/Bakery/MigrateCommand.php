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
 * migrate Bakery Command
 * Perform database migration.
 */
class MigrateCommand extends Command
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
    protected function configure(): void
    {
        $this->setName('migrate')
             ->setDescription('Perform database migration')
             ->setHelp('This command runs all the pending database migrations.')
             ->addOption('pretend', 'p', InputOption::VALUE_NONE, 'Run actions in "dry run" mode.')
             ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force the operation to run without confirmation.')
             ->addOption('database', 'd', InputOption::VALUE_REQUIRED, 'The database connection to use.')
             ->addOption('step', 's', InputOption::VALUE_NONE, 'Migrations will be run so they can be rolled back individually.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Database Migrator');

        // Get options
        $pretend = (bool) $input->getOption('pretend');
        $step = (bool) $input->getOption('step');
        $force = (bool) $input->getOption('force');
        $database = strval($input->getOption('database'));

        // Set connection to the selected database
        if ($database !== '') {
            $this->io->info("Running {$this->getName()} with `$database` database connection");
            $this->db->getDatabaseManager()->setDefaultConnection($database);
        }

        // Switch to pretend if requested
        if ($pretend) {
            return $this->executePretendToMigrate();
        }

        return $this->executeMigrate($step, $force);
    }

    /**
     * Run migrate.
     *
     * @param bool $step
     * @param bool $force Force command to run without confirmation
     *
     * @return int Symfony exit code
     */
    protected function executeMigrate(bool $step, bool $force): int
    {
        // Get pending migrations
        try {
            $pending = $this->migrator->getPending();
        } catch (MigrationDependencyNotMetException|MigrationNotFoundException $e) {
            $this->io->error("Database migration can't be performed. " . $e->getMessage());

            return self::FAILURE;
        }

        // Don't go further if no migration is pending
        if (count($pending) === 0) {
            $this->io->success('Nothing to migrate');

            return self::SUCCESS;
        }

        // Display steps in verbose mode.
        if ($this->io->isVerbose()) {
            $this->io->info('Using individual steps : ' . (($step) ? 'Yes' : 'No'));
        }

        // Show migrations about to be ran
        if ($this->config->getBool('bakery.confirm_sensitive_command', true) || $this->io->isVerbose()) {
            $this->io->section('Pending migrations');
            $this->io->listing($pending);
        }

        // Confirm action if required (for example in production mode).
        if ($this->config->getBool('bakery.confirm_sensitive_command', true) && !$force) {
            if (!$this->io->confirm('Do you really wish to continue ?', false)) {
                return self::SUCCESS;
            }
        }

        // Perform migrations.
        try {
            $migrated = $this->migrator->migrate($step);
        } catch (\Exception $e) {
            $this->io->error($e->getMessage());

            return self::FAILURE;
        }

        if (count($migrated) === 0) {
            // N.B.: Should not happens, only if pending get empty while
            // waiting for confirmation
            $this->io->warning('Nothing migrated !');
        } else {
            $this->io->section('Migrations applied : ');
            $this->io->listing($migrated);
            $this->io->success('Migration successful !');
        }

        return self::SUCCESS;
    }

    /**
     * Run the migrate in pretend mode.
     *
     * @return int Symfony exit code
     */
    protected function executePretendToMigrate(): int
    {
        $this->io->note("Running {$this->getName()} in pretend mode");

        // Get pretend queries
        try {
            $data = $this->migrator->pretendToMigrate();
        } catch (\Exception $e) {
            $this->io->error("Database migration can't be performed. " . $e->getMessage());

            return self::FAILURE;
        }

        if (count($data) === 0) {
            $this->io->success('Nothing to migrate');

            return self::SUCCESS;
        }

        // Display information
        foreach ($data as $migration => $queries) {
            $this->io->section($migration);
            $this->io->listing(array_column($queries, 'query'));
        }

        return self::SUCCESS;
    }
}
