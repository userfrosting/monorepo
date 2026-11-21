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
use Symfony\Component\Console\Output\OutputInterface;
use UserFrosting\Bakery\WithSymfonyStyle;
use UserFrosting\Sprinkle\Core\Bakery\Event\CleanCommandEvent;

/**
 * Umbrella command for cache and database cleanup.
 */
final class CleanCommand extends Command
{
    use WithSymfonyStyle;

    /**
     * Commands to run during cleanup.
     *
     * @var string[]
     */
    protected array $commands = [
        'clean:cache',
        'clean:database',
    ];

    /**
     * @param \UserFrosting\Event\EventDispatcher $eventDispatcher
     */
    public function __construct(
        protected EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $list = implode(', ', $this->aggregateCommands());

        $this->setName('clean')
             ->setDescription('Cleans application cache and prunes expired database records')
             ->setHelp('This command combines the following commands: ' . $list);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var \Symfony\Component\Console\Application */
        $application = $this->getApplication();

        foreach ($this->aggregateCommands() as $commandName) {
            $command = $application->find($commandName);
            $result = $command->run($input, $output);
            if ($result !== self::SUCCESS) {
                return $result;
            }
        }

        return self::SUCCESS;
    }

    /**
     * Aggregate commands to run using CleanCommandEvent.
     *
     * @return string[]
     */
    protected function aggregateCommands(): array
    {
        $event = new CleanCommandEvent($this->commands);
        $event = $this->eventDispatcher->dispatch($event);

        return $event->getCommands();
    }
}
