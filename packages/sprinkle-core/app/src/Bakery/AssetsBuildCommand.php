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

use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use UserFrosting\Bakery\WithSymfonyStyle;
use UserFrosting\Sprinkle\Core\Bakery\Event\AssetsBuildCommandEvent;

/**
 * Alias for common used assets building commands, for integration into `bake` command.
 */
final class AssetsBuildCommand extends Command
{
    use WithSymfonyStyle;

    /**
     * @param \UserFrosting\Event\EventDispatcher $eventDispatcher
     */
    public function __construct(
        protected EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $list = implode(', ', $this->aggregateCommands());

        $this->setName('assets:build')
             ->setDescription('Build the assets using npm and Webpack Encore or Vite')
             ->addOption('production', 'p', InputOption::VALUE_NONE, 'Create a production build')
             ->addOption('watch', 'w', InputOption::VALUE_NONE, 'Watch for changes and recompile automatically (Webpack only)')
             ->setHelp("This command combine the following commands : <comment>{$list}</comment>. For more info, see <comment>https://learn.userfrosting.com/asset-management</comment>.");
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var \Symfony\Component\Console\Application */
        $application = $this->getApplication();
        foreach ($this->aggregateCommands() as $commandName) {
            $command = $application->find($commandName);
            $result = $command->run($input, $output);

            // If the previous command fails, stop the process
            if ($result === self::FAILURE) {
                return $result;
            }
        }

        return self::SUCCESS;
    }

    /**
     * Aggregate commands to run using AssetsBuildCommandEvent.
     *
     * @return string[]
     */
    protected function aggregateCommands(): array
    {
        $event = new AssetsBuildCommandEvent();
        $event = $this->eventDispatcher->dispatch($event);

        return $event->getCommands();
    }
}
