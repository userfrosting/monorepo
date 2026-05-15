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
use Symfony\Component\Console\Tester\CommandTester;
use UserFrosting\Sprinkle\Core\Bakery\DebugVersionCommand;
use UserFrosting\Sprinkle\Core\Validators\NodeVersionValidator;
use UserFrosting\Sprinkle\Core\Validators\NpmVersionValidator;
use UserFrosting\Sprinkle\Core\Validators\PhpDeprecationValidator;
use UserFrosting\Sprinkle\Core\Validators\PhpVersionValidator;
use UserFrosting\Sprinkle\SprinkleManager;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

class DebugVersionCommandTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testCommandWarnsForUnreadableEnv(): void
    {
        if (posix_geteuid() === 0) {
            $this->markTestSkipped('Cannot test file permissions as root.');
        }

        $tmpDir = sys_get_temp_dir() . '/uf_test_env_' . uniqid();
        mkdir($tmpDir);
        $envFile = $tmpDir . '/.env';
        file_put_contents($envFile, "UF_MODE=production\n");
        chmod($envFile, 0000);

        try {
            $command = $this->buildCommand($tmpDir);
            $tester = new CommandTester($command);
            $tester->execute([]);

            $this->assertSame(0, $tester->getStatusCode());
            $this->assertStringContainsString('cannot be read', $tester->getDisplay());
        } finally {
            chmod($envFile, 0644);
            unlink($envFile);
            rmdir($tmpDir);
        }
    }

    public function testCommandNoWarningForReadableEnv(): void
    {
        $tmpDir = sys_get_temp_dir() . '/uf_test_env_' . uniqid();
        mkdir($tmpDir);
        $envFile = $tmpDir . '/.env';
        file_put_contents($envFile, "UF_MODE=production\n");

        try {
            $command = $this->buildCommand($tmpDir);
            $tester = new CommandTester($command);
            $tester->execute([]);

            $this->assertSame(0, $tester->getStatusCode());
            $this->assertStringNotContainsString('cannot be read', $tester->getDisplay());
        } finally {
            unlink($envFile);
            rmdir($tmpDir);
        }
    }

    public function testCommandNoWarningWhenEnvMissing(): void
    {
        $command = $this->buildCommand('/nonexistent/path');
        $tester = new CommandTester($command);
        $tester->execute([]);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringNotContainsString('cannot be read', $tester->getDisplay());
    }

    /**
     * Build the command with mocked dependencies.
     */
    private function buildCommand(string $basePath): DebugVersionCommand
    {
        $dispatcher = Mockery::mock(EventDispatcherInterface::class);

        $sprinkleManager = Mockery::mock(SprinkleManager::class);
        $sprinkleManager->shouldReceive('getMainSprinkle->getName')->andReturn('TestSprinkle');

        $phpVersion = Mockery::mock(PhpVersionValidator::class);
        $phpVersion->shouldReceive('validate');
        $phpVersion->shouldReceive('getInstalled')->andReturn('8.2.0');

        $phpDeprecation = Mockery::mock(PhpDeprecationValidator::class);
        $phpDeprecation->shouldReceive('validate');

        $nodeVersion = Mockery::mock(NodeVersionValidator::class);
        $nodeVersion->shouldReceive('validate');
        $nodeVersion->shouldReceive('getInstalled')->andReturn('18.0.0');

        $npmVersion = Mockery::mock(NpmVersionValidator::class);
        $npmVersion->shouldReceive('validate');
        $npmVersion->shouldReceive('getInstalled')->andReturn('9.0.0');

        $locator = Mockery::mock(ResourceLocatorInterface::class);
        $locator->shouldReceive('getBasePath')->andReturn($basePath);

        return new DebugVersionCommand(
            $dispatcher,
            $sprinkleManager,
            $phpVersion,
            $phpDeprecation,
            $nodeVersion,
            $npmVersion,
            $locator,
        );
    }
}
