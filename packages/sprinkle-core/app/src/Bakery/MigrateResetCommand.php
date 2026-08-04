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
 * migrate:reset Bakery Command
 * Reset the database to a clean state.
 */
class MigrateResetCommand extends Command
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
        $this->setName('migrate:reset')
             ->setDescription('Reset the whole database to an empty state, rolling back all migrations.')
             ->addOption('pretend', 'p', InputOption::VALUE_NONE, 'Run actions in "dry run" mode.')
             ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force the operation to run without confirmation.')
             ->addOption('database', 'd', InputOption::VALUE_REQUIRED, 'The database connection to use.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Database Migration Reset');

        // Get options
        $pretend = (bool) $input->getOption('pretend');
        $force = (bool) $input->getOption('force');
        $database = (string) $input->getOption('database');

        // Set connection to the selected database
        if ($database !== '') {
            $this->io->info("Running {$this->getName()} with `$database` database connection");
            $this->db->getDatabaseManager()->setDefaultConnection($database);
        }

        // Check if the hard option is used
        if ($pretend) {
            return $this->pretendReset();
        } else {
            return $this->performReset($force);
        }
    }

    /**
     * Reset the whole database to an empty state by rolling back all migrations.
     *
     * @param bool $force Force command to run without confirmation.
     *
     * @return int Symfony exit code
     */
    protected function performReset(bool $force): int
    {
        // Get migrations for reset
        try {
            $migrations = $this->migrator->getMigrationsForReset();
        } catch (MigrationDependencyNotMetException|MigrationNotFoundException $e) {
            $this->io->error("Database reset can't be performed. " . $e->getMessage());

            return self::FAILURE;
        }

        // Don't go further if no migration to reset
        if (count($migrations) === 0) {
            $this->io->success('Nothing to reset');

            return self::SUCCESS;
        }

        // Show migrations about to be reset
        if ($this->config->getBool('bakery.confirm_sensitive_command', true) || $this->io->isVerbose()) {
            $this->io->section('Migrations to reset');
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
            $rollbacked = $this->migrator->reset();
        } catch (\Exception $e) {
            $this->io->error($e->getMessage());

            return self::FAILURE;
        }

        if (count($rollbacked) === 0) {
            // N.B.: Should not happens, only if tow operation get executed
            // while waiting for confirmation.
            $this->io->warning('Nothing was reset !');

            return self::SUCCESS;
        }

        // Delete the repository
        if ($this->migrator->repositoryExists()) {
            if ($this->io->isVerbose()) {
                $this->io->writeln('<info>> Deleting migration repository</info>');
            }
            $this->migrator->getRepository()->delete();
        }

        // Show success
        $this->io->section('Migrations reset : ');
        $this->io->listing($rollbacked);
        $this->io->success('Reset successful !');

        return self::SUCCESS;
    }

    /**
     * Run the migrate in pretend mode.
     *
     * @return int Symfony exit code
     */
    protected function pretendReset(): int
    {
        $this->io->note("Running {$this->getName()} in pretend mode");

        // Get pretend queries
        try {
            $data = $this->migrator->pretendToReset();
        } catch (\Exception $e) {
            $this->io->error("Database reset can't be performed. " . $e->getMessage());

            return self::FAILURE;
        }

        if (count($data) === 0) {
            $this->io->success('Nothing to reset');

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
