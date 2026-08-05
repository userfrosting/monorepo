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

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use UserFrosting\Sprinkle\Core\Bakery\SetupEnvCommand;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase;
use UserFrosting\Support\DotenvEditor\DotenvEditor;
use UserFrosting\Testing\BakeryTester;
use UserFrosting\UniformResourceLocator\ResourceLocator;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;
use UserFrosting\UniformResourceLocator\ResourceStream;

/**
 * Test for SetupEnvCommand (setup:env)
 */
class SetupEnvCommandTest extends CoreTestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        // Use test location
        $envStream = new ResourceStream('sprinkles', path: 'env', shared: true);
        $locator = new ResourceLocator(__DIR__ . '/data');
        $locator->addStream($envStream);
        $this->getContainer()->set(ResourceLocatorInterface::class, $locator);
    }

    public function testEnvNotFound(): void
    {
        // Force locator return with Mockery
        $locator = Mockery::mock(ResourceLocatorInterface::class);
        $locator->shouldReceive('findResource')->andReturn(null);
        $this->getContainer()->set(ResourceLocatorInterface::class, $locator);

        // Run command and assert it fails
        /** @var SetupEnvCommand */
        $command = $this->getService(SetupEnvCommand::class);
        $result = BakeryTester::runCommand($command);
        $this->assertSame(1, $result->getStatusCode());
        $this->assertStringContainsString('Could not find .env file', $result->getDisplay());
    }

    public function testCommand(): void
    {
        // Run command and assert result
        /** @var SetupEnvCommand */
        $command = $this->getService(SetupEnvCommand::class);
        $result = BakeryTester::runCommand($command, userInput: [
            '2',
        ]);

        // Assertions
        $this->assertSame(0, $result->getStatusCode());
        $this->assertStringContainsString('Environment mode successfully changed to `debug`', $result->getDisplay());

        // Assert env and config is correctly changed
        $dotenvEditor = new DotenvEditor();
        $dotenvEditor->load(__DIR__ . '/data/env/.env');
        $this->assertSame('debug', $dotenvEditor->getValue('UF_MODE'));
    }

    public function testCommandWithOptionsAndVerbose(): void
    {
        // Run command and assert result
        /** @var SetupEnvCommand */
        $command = $this->getService(SetupEnvCommand::class);
        $result = BakeryTester::runCommand($command, verbosity: OutputInterface::VERBOSITY_VERBOSE, input: [
            '--mode'   => 'foo',
        ]);

        // Assertions
        $this->assertSame(0, $result->getStatusCode());
        $this->assertStringContainsString('Environment mode will be saved in', $result->getDisplay());
        $this->assertStringContainsString('Environment mode successfully changed to `foo`', $result->getDisplay());
    }

    public function testCommandForOther(): void
    {
        // Run command and assert result
        /** @var SetupEnvCommand */
        $command = $this->getService(SetupEnvCommand::class);
        $result = BakeryTester::runCommand($command, userInput: [
            '3',
            'bar',
        ]);

        // Assertions
        $this->assertSame(0, $result->getStatusCode());
        $this->assertStringContainsString('Environment mode successfully changed to `bar`', $result->getDisplay());
    }
}
