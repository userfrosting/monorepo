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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use UserFrosting\Event\EventDispatcher;
use UserFrosting\Sprinkle\Core\Bakery\CleanCommand;
use UserFrosting\Sprinkle\Core\Bakery\Event\CleanCommandEvent;
use UserFrosting\Testing\ContainerStub;

class CleanCommandTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testBaseCommandRunsAggregatedSubcommand(): void
    {
        /** @var Mockery\MockInterface&ListenerProviderInterface */
        $listener = Mockery::mock(ListenerProviderInterface::class)
            ->shouldReceive('getListenersForEvent')->andReturn([new CleanCommandListenerStub()])
            ->getMock();
        $eventDispatcher = new EventDispatcher($listener);
        $ci = ContainerStub::create();
        $ci->set(EventDispatcherInterface::class, $eventDispatcher);

        /** @var CleanCommand */
        $command = $ci->get(CleanCommand::class);

        $app = new Application();
        $app->add($command);
        $app->add(new StubCleanCommand());
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => 'clean']);

        $this->assertSame(0, $commandTester->getStatusCode());
    }

    public function testSubcommandFailureStopsCleanup(): void
    {
        /** @var Mockery\MockInterface&ListenerProviderInterface */
        $listener = Mockery::mock(ListenerProviderInterface::class)
            ->shouldReceive('getListenersForEvent')->andReturn([new CleanCommandListenerStub()])
            ->getMock();
        $eventDispatcher = new EventDispatcher($listener);
        $ci = ContainerStub::create();
        $ci->set(EventDispatcherInterface::class, $eventDispatcher);

        /** @var CleanCommand */
        $command = $ci->get(CleanCommand::class);

        $app = new Application();
        $app->add($command);
        $app->add(new StubFailCleanCommand());
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => 'clean']);

        $this->assertSame(1, $commandTester->getStatusCode());
    }
}

class CleanCommandListenerStub
{
    public function __invoke(CleanCommandEvent $event): void
    {
        $event->setCommands(['stub']);
    }
}

class StubCleanCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('stub');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return self::SUCCESS;
    }
}

class StubFailCleanCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('stub');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return self::FAILURE;
    }
}
