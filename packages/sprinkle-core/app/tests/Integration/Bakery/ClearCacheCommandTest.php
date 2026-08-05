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

use Illuminate\Cache\Repository as Cache;
use Illuminate\Filesystem\Filesystem;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use UserFrosting\Sprinkle\Core\Bakery\ClearCacheCommand;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase;
use UserFrosting\Sprinkle\Core\Twig\CacheHelper;
use UserFrosting\Testing\BakeryTester;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

/**
 * Test ClearCacheCommand
 */
class ClearCacheCommandTest extends CoreTestCase
{
    use MockeryPHPUnitIntegration;

    private string $cachePath = __DIR__ . '/data/cache/route.cache';

    public function testCommand(): void
    {
        // Setup mocks
        $cache = Mockery::mock(Cache::class)
            ->shouldReceive('flush')->once()
            ->getMock();
        $this->getContainer()->set(Cache::class, $cache);

        $cacheHelper = Mockery::mock(CacheHelper::class)
            ->shouldReceive('clearCache')->once()->andReturn(true)
            ->getMock();
        $this->getContainer()->set(CacheHelper::class, $cacheHelper);

        // Route caching mock
        $locator = Mockery::mock(ResourceLocatorInterface::class)
            ->shouldReceive('findResource')->with('cache://routes.cache', true)->once()->andReturn($this->cachePath)
            ->getMock();
        $this->getContainer()->set(ResourceLocatorInterface::class, $locator);

        // Set file to be deleted
        touch($this->cachePath);
        $this->assertFileExists($this->cachePath);

        /** @var ClearCacheCommand */
        $command = $this->getService(ClearCacheCommand::class);
        $result = BakeryTester::runCommand($command);

        // Assert some output
        $this->assertSame(0, $result->getStatusCode());
        $this->assertStringContainsString('Cache cleared', $result->getDisplay());
        $this->assertFileDoesNotExist($this->cachePath);
    }

    public function testCommandForFailure(): void
    {
        // Setup mocks
        $cache = Mockery::mock(Cache::class)
            ->shouldReceive('flush')->once()
            ->getMock();
        $this->getContainer()->set(Cache::class, $cache);

        $cacheHelper = Mockery::mock(CacheHelper::class)
            ->shouldReceive('clearCache')->once()->andReturn(false)
            ->getMock();
        $this->getContainer()->set(CacheHelper::class, $cacheHelper);

        /** @var ClearCacheCommand */
        $command = $this->getService(ClearCacheCommand::class);
        $result = BakeryTester::runCommand($command);

        // Assert some output
        $this->assertSame(1, $result->getStatusCode());
        $this->assertStringContainsString('Failed to clear Twig cached data', $result->getDisplay());
    }

    public function testCommandWithNoRouteFile(): void
    {
        // Setup mocks
        $cache = Mockery::mock(Cache::class)
            ->shouldReceive('flush')->once()
            ->getMock();
        $this->getContainer()->set(Cache::class, $cache);

        $cacheHelper = Mockery::mock(CacheHelper::class)
            ->shouldReceive('clearCache')->once()->andReturn(true)
            ->getMock();
        $this->getContainer()->set(CacheHelper::class, $cacheHelper);

        // Route caching mock
        $locator = Mockery::mock(ResourceLocatorInterface::class)
            ->shouldReceive('findResource')->with('cache://routes.cache', true)->once()->andReturn($this->cachePath)
            ->getMock();
        $this->getContainer()->set(ResourceLocatorInterface::class, $locator);

        // Filesystem return not found
        $filesystem = Mockery::mock(Filesystem::class)
            ->shouldReceive('exists')->with($this->cachePath)->once()->andReturn(false)
            ->getMock();
        $this->getContainer()->set(Filesystem::class, $filesystem);

        /** @var ClearCacheCommand */
        $command = $this->getService(ClearCacheCommand::class);
        $result = BakeryTester::runCommand($command);

        // Assert some output
        $this->assertSame(0, $result->getStatusCode());
        $this->assertStringContainsString('Cache cleared', $result->getDisplay());
    }

    public function testCommandWithRouteError(): void
    {
        // Setup mocks
        $cache = Mockery::mock(Cache::class)
            ->shouldReceive('flush')->once()
            ->getMock();
        $this->getContainer()->set(Cache::class, $cache);

        $cacheHelper = Mockery::mock(CacheHelper::class)
            ->shouldReceive('clearCache')->once()->andReturn(true)
            ->getMock();
        $this->getContainer()->set(CacheHelper::class, $cacheHelper);

        // Route caching mock
        $locator = Mockery::mock(ResourceLocatorInterface::class)
            ->shouldReceive('findResource')->with('cache://routes.cache', true)->andReturn($this->cachePath)
            ->getMock();
        $this->getContainer()->set(ResourceLocatorInterface::class, $locator);

        // Filesystem return found, but delete error
        $filesystem = Mockery::mock(Filesystem::class)
            ->shouldReceive('exists')->with($this->cachePath)->once()->andReturn(true)
            ->shouldReceive('delete')->with($this->cachePath)->once()->andReturn(false)
            ->getMock();
        $this->getContainer()->set(Filesystem::class, $filesystem);

        /** @var ClearCacheCommand */
        $command = $this->getService(ClearCacheCommand::class);
        $result = BakeryTester::runCommand($command);

        // Assert some output
        $this->assertSame(1, $result->getStatusCode());
        $this->assertStringContainsString('Failed to delete Router cache file', $result->getDisplay());
    }
}
