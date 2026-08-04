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

/**
 * migrate:clean Bakery Command
 * Remove stale migrations from the database.
 */
class MigrateCleanCommand extends Command
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
        $this->setName('migrate:clean')
             ->setDescription('Remove stale migrations from the repository.')
             ->setHelp('Removes stale migrations from the repository that are not available as a class. E.g. if you run a migration and then delete the migration class file prior to running `down()` for that migration, it becomes stale. This should be used as a last resort. If a migration is a dependency of another migration you probably want to try to restore the files instead of running this command to avoid further issues.')
             ->addOption('database', 'd', InputOption::VALUE_REQUIRED, 'The database connection to use.')
             ->addOption('force', 'f', InputOption::VALUE_NONE, 'Do not prompt for confirmation.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Migration clean');

        // Get options
        $database = (string) $input->getOption('database');
        $force = (bool) $input->getOption('force');

        // Set connection to the selected database
        if ($database !== '') {
            $this->io->info("Running {$this->getName()} with `$database` database connection");
            $this->db->getDatabaseManager()->setDefaultConnection($database);
        }

        // Get stale migrations
        $stale = $this->migrator->getStale();

        if (count($stale) === 0) {
            $this->io->note('No stale migrations found');

            return self::SUCCESS;
        }

        // Show migrations about to be ran
        if ($this->config->getBool('bakery.confirm_sensitive_command', true) || $this->io->isVerbose()) {
            $this->io->section('Stale migrations');
            $this->io->listing($stale);
        }

        // Confirm action if required (for example in production mode).
        if ($this->config->getBool('bakery.confirm_sensitive_command', true) && !$force) {
            if (!$this->io->confirm('Continue and remove stale migrations ?', false)) {
                return self::SUCCESS;
            }
        }

        // Remove stale migration from repo
        $repository = $this->migrator->getRepository();

        //Delete the stale migration classes from the database.
        foreach ($stale as $migration) {
            $this->io->writeln("> Removing `$migration`...");
            $repository->remove($migration);
        }

        $this->io->success('Stale migrations removed from repository');

        return self::SUCCESS;
    }
}
