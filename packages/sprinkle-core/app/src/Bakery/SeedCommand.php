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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use UserFrosting\Bakery\WithSymfonyStyle;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Core\Seeder\SeedRepositoryInterface;

/**
 * seed Bakery Command
 * Runs SeedInterface.
 */
class SeedCommand extends Command
{
    use WithSymfonyStyle;

    #[Inject]
    protected SeedRepositoryInterface $seeds;

    #[Inject]
    protected Config $config;

    #[Inject]
    protected Capsule $db;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('seed')
             ->setDescription('Seed the database with records')
             ->setHelp('This command runs a seed to populate the app with default, random and/or test data.')
             ->addArgument('class', InputArgument::IS_ARRAY, 'The class name of the seeder. Separate multiple seeder with a space.')
             ->addOption('database', 'd', InputOption::VALUE_REQUIRED, 'The database connection to use.')
             ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force the operation to run without confirmation.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return $this->seed(
            $input->getArgument('class'),
            $input->getOption('force'),
            $input->getOption('database'),
        );
    }

    /**
     * Perform seed operation.
     *
     * @param class-string[] $classes
     * @param bool           $force
     * @param string|null    $database
     *
     * @return int
     */
    protected function seed(array $classes = [], bool $force = false, ?string $database = ''): int
    {
        $this->io->title('Seeder');

        // Set connection to the selected database
        if (is_string($database) && $database !== '') {
            $this->io->info("Running {$this->getName()} with `$database` database connection");
            $this->db->getDatabaseManager()->setDefaultConnection($database);
        }

        // If class is empty, ask to choose one.
        if (count($classes) === 0) {
            $list = $this->seeds->list();

            // Abort if no registered seeds
            if (count($list) === 0) {
                $this->io->warning('No available seeds founds');

                return self::SUCCESS;
            }

            // Ask user to select seeds
            $classes = $this->selectSeeds($list);
        }

        // Validate each classes
        foreach ($classes as $className) {
            if (!$this->seeds->has($className)) {
                $this->io->error('Class is not a valid seed : ' . $className);

                return self::FAILURE;
            }
        }

        // Display what's about to be run
        if ($this->config->getBool('bakery.confirm_sensitive_command', true) || $this->io->isVerbose()) {
            $this->io->section('Seed(s) to apply');
            $this->io->listing($classes);
        }

        // Confirm action if required (for example in production mode).
        if ($this->config->getBool('bakery.confirm_sensitive_command', true) && !$force) {
            if (!$this->io->confirm('Do you really wish to continue ?', false)) {
                return self::SUCCESS;
            }
        }

        // Run seeds
        foreach ($classes as $seed) {
            try {
                $this->seeds->get($seed)->run();
            } catch (\Exception $e) {
                $this->io->error($e->getMessage());

                return self::FAILURE;
            }
        }

        // Success
        $this->io->success('Seed successful !');

        return self::SUCCESS;
    }

    /**
     * Ask user to select the seeds to run.
     *
     * @param class-string[] $list Available seeds
     *
     * @return class-string[] Selected seeds
     */
    protected function selectSeeds(array $list): array
    {
        $question = new ChoiceQuestion('Select seed(s) to run. Multiple seeds can be selected using comma separated values', $list);
        $question->setMultiselect(true);

        return $this->io->askQuestion($question);
    }
}
