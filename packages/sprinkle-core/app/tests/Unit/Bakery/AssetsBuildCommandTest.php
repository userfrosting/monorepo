<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit\Bakery;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use UserFrosting\Bakery\WithSymfonyStyle;
use UserFrosting\Event\EventDispatcher;
use UserFrosting\Sprinkle\Core\Bakery\AssetsBuildCommand;
use UserFrosting\Sprinkle\Core\Bakery\Event\AssetsBuildCommandEvent;
use UserFrosting\Testing\ContainerStub;

/**
 * N.B.: This test doesn't actually call the predefined sub-commands. It only
 * tests the listener can overwrite them, and the stub command are called.
 */
class AssetsBuildCommandTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testBaseCommand(): void
    {
        // Setup services mock. Command will be set by AssetsBuildCommandEvent
        /** @var Mockery\MockInterface&ListenerProviderInterface */
        $listener = Mockery::mock(ListenerProviderInterface::class)
            ->shouldReceive('getListenersForEvent')->andReturn([new AssetsBuildCommandListenerStub()])
            ->getMock();
        $eventDispatcher = new EventDispatcher($listener);
        $ci = ContainerStub::create();
        $ci->set(EventDispatcherInterface::class, $eventDispatcher);

        /** @var AssetsBuildCommand */
        $command = $ci->get(AssetsBuildCommand::class);

        // Run command
        $app = new Application();
        $app->add($command);
        $app->add(new AssetsStubCommand());
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => 'assets:build']);

        // Assert some output
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertSame('SUCCESS', $commandTester->getDisplay());
    }

    public function testOneCommandFails(): void
    {
        // Setup services mock. Command will be set by AssetsBuildCommandEvent
        /** @var Mockery\MockInterface&ListenerProviderInterface */
        $listener = Mockery::mock(ListenerProviderInterface::class)
            ->shouldReceive('getListenersForEvent')->andReturn([new AssetsBuildCommandListenerStubFail()])
            ->getMock();
        $eventDispatcher = new EventDispatcher($listener);
        $ci = ContainerStub::create();
        $ci->set(EventDispatcherInterface::class, $eventDispatcher);

        /** @var AssetsBuildCommand */
        $command = $ci->get(AssetsBuildCommand::class);

        // Run command
        $app = new Application();
        $app->add($command);
        $app->add(new AssetsStubFailCommand());
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => 'assets:build']);

        // Assert some output
        $this->assertSame(1, $commandTester->getStatusCode());
        $this->assertSame('FAILURE', $commandTester->getDisplay());
    }

    public function testArgumentPassthrough(): void
    {
        // Setup services mock. Command will be set by AssetsBuildCommandEvent
        /** @var Mockery\MockInterface&ListenerProviderInterface */
        $listener = Mockery::mock(ListenerProviderInterface::class)
            ->shouldReceive('getListenersForEvent')->andReturn([new AssetsBuildCommandListenerStubParam()])
            ->getMock();
        $eventDispatcher = new EventDispatcher($listener);
        $ci = ContainerStub::create();
        $ci->set(EventDispatcherInterface::class, $eventDispatcher);

        /** @var AssetsBuildCommand */
        $command = $ci->get(AssetsAcceptParamStubCommand::class);

        // Run command
        $app = new Application();
        $app->add($command);
        $app->add(new AssetsStubCommand());
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => 'assets:build']);

        // Assert some output
        $this->assertSame(1, $commandTester->getStatusCode());

        // Start again, with the param
        $commandTester->execute(['command' => 'assets:build', '--watch' => true, '--production' => true]);
        $this->assertSame(0, $commandTester->getStatusCode());
    }
}

class AssetsBuildCommandListenerStub
{
    public function __invoke(AssetsBuildCommandEvent $event): void
    {
        $event->setCommands(['stub']);
    }
}

class AssetsBuildCommandListenerStubFail
{
    public function __invoke(AssetsBuildCommandEvent $event): void
    {
        $event->setCommands(['fail']);
    }
}

class AssetsBuildCommandListenerStubParam
{
    public function __invoke(AssetsBuildCommandEvent $event): void
    {
        $event->setCommands(['param']);
    }
}

class AssetsStubCommand extends Command
{
    use WithSymfonyStyle;

    protected function configure(): void
    {
        $this->setName('stub');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->write('SUCCESS');

        return self::SUCCESS;
    }
}

class AssetsAcceptParamStubCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('param')
             ->addOption('production', 'p', InputOption::VALUE_NONE, 'Create a production build')
             ->addOption('watch', 'w', InputOption::VALUE_NONE, 'Watch for changes and recompile automatically');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $production = (bool) $input->getOption('production');
        $watch = (bool) $input->getOption('watch');

        if ($production && $watch) {
            return self::SUCCESS;
        } else {
            return self::FAILURE;
        }
    }
}

class AssetsStubFailCommand extends Command
{
    use WithSymfonyStyle;

    protected function configure(): void
    {
        $this->setName('fail');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->write('FAILURE');

        return self::FAILURE;
    }
}
