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
use UserFrosting\Sprinkle\Core\Bakery\Event\BakeCommandEvent;

/**
 * Bake command.
 * Umbrella command used to run multiple sub-commands at once.
 */
final class BakeCommand extends Command
{
    use WithSymfonyStyle;

    /**
     * @var string[] Commands to run
     */
    protected array $commands = [
        'setup:db',
        'setup:mail',
        'debug',
        'migrate',
        'assets:build',
        'clear-cache',
    ];

    /**
     * @var string The UserFrosting ASCII art.
     */
    public string $title = "
 _   _              ______             _   _                ____ 
| | | |             |  ___|           | | (_)              / ___|
| | | |___  ___ _ __| |_ _ __ ___  ___| |_ _ _ __   __ _  / /___ 
| | | / __|/ _ \ '__|  _| '__/ _ \/ __| __| | '_ \ / _` | | ___ \
| |_| \__ \  __/ |  | | | | | (_) \__ \ |_| | | | | (_| | | \_/ |
 \___/|___/\___|_|  \_| |_|  \___/|___/\__|_|_| |_|\__, | \_____/
                                                    __/ |        
                                                   |___/         ";
                                                   

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

        $this->setName('bake')
             ->setDescription('UserFrosting installation command')
             ->setHelp('This command combine the following commands : ' . $list);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->writeln("<info>{$this->title}</info>");

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
     * Aggregate commands to run using BakeCommandEvent.
     *
     * @return string[]
     */
    protected function aggregateCommands(): array
    {
        $event = new BakeCommandEvent($this->commands);
        $event = $this->eventDispatcher->dispatch($event);

        return $event->getCommands();
    }
}
