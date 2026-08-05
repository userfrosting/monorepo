<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Bakery;

use Illuminate\Database\Capsule\Manager as Capsule;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PDOException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use UserFrosting\Sprinkle\Core\Bakery\DebugCommand;
use UserFrosting\Sprinkle\Core\Bakery\DebugConfigCommand;
use UserFrosting\Sprinkle\Core\Bakery\DebugDbCommand;
use UserFrosting\Sprinkle\Core\Bakery\DebugEventsCommand;
use UserFrosting\Sprinkle\Core\Bakery\DebugLocatorCommand;
use UserFrosting\Sprinkle\Core\Bakery\DebugMailCommand;
use UserFrosting\Sprinkle\Core\Bakery\DebugTwigCommand;
use UserFrosting\Sprinkle\Core\Bakery\DebugVersionCommand;
use UserFrosting\Sprinkle\Core\Bakery\SprinkleListCommand;
use UserFrosting\Sprinkle\Core\Exceptions\VersionCompareException;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase;
use UserFrosting\Sprinkle\Core\Validators\PhpDeprecationValidator;
use UserFrosting\Sprinkle\Core\Validators\PhpVersionValidator;
use UserFrosting\Testing\BakeryTester;

class DebugCommandTest extends CoreTestCase
{
    use MockeryPHPUnitIntegration;

    public function testCommand(): void
    {
        $result = $this->getCommandTester();

        // Assert some output
        $this->assertSame(0, $result->getStatusCode());
        $this->assertStringContainsString('Testing database connection', $result->getDisplay());
    }

    public function testCommandForFailDatabase(): void
    {
        // Setup mocks
        $class = Mockery::mock(Capsule::class)
            ->shouldReceive('getDatabaseManager')->andThrow(PDOException::class)
            ->getMock();
        $this->getContainer()->set(Capsule::class, $class);

        $result = $this->getCommandTester();

        // Assert some output
        $this->assertSame(1, $result->getStatusCode());
        $this->assertStringContainsString('Could not connect to the database connection', $result->getDisplay());
    }

    public function testCommandForFailVersion(): void
    {
        // Setup mocks
        $class = Mockery::mock(PhpVersionValidator::class)
            ->shouldReceive('validate')->andThrow(VersionCompareException::class, 'Version is not supported')
            ->getMock();
        $this->getContainer()->set(PhpVersionValidator::class, $class);

        $result = $this->getCommandTester();

        // Assert some output
        $this->assertSame(1, $result->getStatusCode());
        $this->assertStringContainsString('Version is not supported', $result->getDisplay());
    }

    public function testCommandForWarningVersion(): void
    {
        // Setup mocks
        $class = Mockery::mock(PhpDeprecationValidator::class)
            ->shouldReceive('validate')->andThrow(VersionCompareException::class, 'Version deprecated')
            ->getMock();
        $this->getContainer()->set(PhpDeprecationValidator::class, $class);

        $result = $this->getCommandTester();

        // Assert some output
        $this->assertSame(0, $result->getStatusCode());
        $this->assertStringContainsString('Version deprecated', $result->getDisplay());
        $this->assertStringContainsString('Testing database connection', $result->getDisplay());
    }

    private function getCommandTester(): CommandTester
    {
        // Create app and add command to it
        // N.B.: We can't use the BakeryTester here because we need to manually
        //       add the sub-command to the app
        $app = new Application();

        // Add all required commands
        $commands = [
            DebugCommand::class,
            DebugVersionCommand::class,
            DebugVersionCommand::class,
            SprinkleListCommand::class,
            DebugConfigCommand::class,
            DebugDbCommand::class,
            DebugMailCommand::class,
            DebugLocatorCommand::class,
            DebugEventsCommand::class,
            DebugTwigCommand::class,
        ];
        foreach ($commands as $command) {
            $app->add($command = $this->getContainer()->get($command));
        }

        // Get command to test
        /** @var DebugCommand */
        $debugCommand = $this->getService(DebugCommand::class);

        // Create command tester & execute command
        $commandTester = new CommandTester($debugCommand);
        $commandTester->execute([
            'command' => $debugCommand->getName(),
        ], ['verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE]);

        return $commandTester;
    }
}
