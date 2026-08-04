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
use UserFrosting\Sprinkle\Core\Bakery\BakeCommand;
use UserFrosting\Sprinkle\Core\Bakery\Event\SetupCommandEvent;
use UserFrosting\Sprinkle\Core\Bakery\SetupCommand;
use UserFrosting\Testing\ContainerStub;

class SetupCommandTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testBaseCommand(): void
    {
        // Setup services mock. Command will be set by SetupCommandEvent
        /** @var Mockery\MockInterface&ListenerProviderInterface */
        $listener = Mockery::mock(ListenerProviderInterface::class)
            ->shouldReceive('getListenersForEvent')->andReturn([new SetupCommandListenerStub()])
            ->getMock();
        $eventDispatcher = new EventDispatcher($listener);
        $ci = ContainerStub::create();
        $ci->set(EventDispatcherInterface::class, $eventDispatcher);

        /** @var BakeCommand */
        $command = $ci->get(SetupCommand::class);

        // Run command
        $app = new Application();
        $app->add($command);
        $app->add(new StubSetupCommand());
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => 'setup']);

        // Assert some output
        $this->assertSame(0, $commandTester->getStatusCode());
    }
}

class SetupCommandListenerStub
{
    public function __invoke(SetupCommandEvent $event): void
    {
        $event->setCommands(['stub']);
    }
}

class StubSetupCommand extends Command
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
