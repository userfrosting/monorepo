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
use UserFrosting\Sprinkle\Core\Database\Migrator\Migrator;

/**
 * migrate:status Bakery Command
 * Show the list of installed and pending migration.
 */
class MigrateStatusCommand extends Command
{
    use WithSymfonyStyle;

    #[Inject]
    protected Migrator $migrator;

    #[Inject]
    protected Capsule $db;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('migrate:status')
             ->setDescription('Show the list of installed and pending migration.')
             ->setHelp('Show the list of installed and pending migration. This command also show if an installed migration is available in the Filesystem, so it can be run down by the rollback command')
             ->addOption('database', 'd', InputOption::VALUE_REQUIRED, 'The database connection to use.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Migration status');

        // Set connection to the selected database
        $database = $input->getOption('database');
        if ($database !== '') {
            $this->io->note("Running {$this->getName()} with `$database` database connection");
            $this->db->getDatabaseManager()->setDefaultConnection($database);
        }

        // Get installed, available and pending migrations.
        $installed = $this->migrator->getRepository()->all();
        $available = $this->migrator->getAvailable();
        $pending = $this->migrator->getPending();

        // Display ran migrations
        $this->io->section('Installed migrations');
        if (count($installed) > 0) {
            $headers = ['Migration', 'Available?', 'Batch'];
            $rows = $this->getRows($installed, $available);
            $this->io->table($headers, $rows);
        } else {
            $this->io->note('No installed migrations');
        }

        // Display pending migrations
        $this->io->section('Pending migrations');
        if (count($pending) > 0) {
            $this->io->listing($pending);
        } else {
            $this->io->note('No pending migrations');
        }

        return self::SUCCESS;
    }

    /**
     * Return an array of [migration, available] association.
     * A migration is available if it's in the available stack (class is in the Filesystem).
     *
     * @param array<array{migration: string, batch: int}> $installed   The ran migrations
     * @param string[]                                    $isAvailable The available migrations
     *
     * @return array<array{string, string, int}> An array of [migration, available] association
     */
    protected function getRows(array $installed, array $isAvailable): array
    {
        $row = array_map(function (array $migration) use ($isAvailable): array {
            if (in_array($migration['migration'], $isAvailable, true)) {
                $isAvailable = '<info>Yes</info>';
            } else {
                $isAvailable = '<fg=red>No</fg=red>';
            }

            return [$migration['migration'], $isAvailable, $migration['batch']];
        }, $installed);

        return $row;
    }
}
