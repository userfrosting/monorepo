<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit\ServicesProvider;

use DI\Container;
use Illuminate\Cache\Repository as Cache;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use UserFrosting\Cache\MemcachedStore;
use UserFrosting\Cache\RedisStore;
use UserFrosting\Cache\TaggableFileStore;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Core\Exceptions\BadConfigException;
use UserFrosting\Sprinkle\Core\ServicesProvider\CacheService;
use UserFrosting\Testing\ContainerStub;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

/**
 * Integration tests for `cache` service.
 * Check to see if service returns what it's supposed to return
 */
class CacheServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected Container $ci;

    public function setUp(): void
    {
        parent::setUp();

        // Create container with provider to test
        $provider = new CacheService();
        $this->ci = ContainerStub::create($provider->register());
    }

    /**
     * Test the right store is returned depending on Config
     *
     * @dataProvider driverDataProvider
     *
     * @param string       $name
     * @param class-string $class
     */
    public function testFileConfig(string $name, string $class): void
    {
        // Set mock Config service
        $config = Mockery::mock(Config::class)
            ->shouldReceive('get')->with('cache.driver')->once()->andReturn($name)
            ->getMock();
        $this->ci->set(Config::class, $config);

        // Set mock Store
        $store = Mockery::mock($class)
            ->shouldReceive('instance')->once()->andReturn(Mockery::mock(Cache::class))
            ->getMock();
        $this->ci->set($class, $store);
        $this->ci->get(Cache::class);
    }

    /**
     * @return array<string|class-string>[]
     */
    public static function driverDataProvider(): array
    {
        return [
            ['file', TaggableFileStore::class],
            ['memcached', MemcachedStore::class],
            ['redis', RedisStore::class],
        ];
    }

    public function testBadConfig(): void
    {
        // Set mock Config service
        $config = Mockery::mock(Config::class)
            ->shouldReceive('get')->with('cache.driver')->times(2)->andReturn('foo')
            ->getMock();
        $this->ci->set(Config::class, $config);

        // Get stream and assert the exception is thrown.
        $this->expectException(BadConfigException::class);
        $this->ci->get(Cache::class);
    }

    public function testTaggableFileStore(): void
    {
        // Set mock Locator
        $locator = Mockery::mock(ResourceLocatorInterface::class)
            ->shouldReceive('findResource')->withArgs(['cache://', true])->once()->andReturn('foo/')
            ->getMock();
        $this->ci->set(ResourceLocatorInterface::class, $locator);
        $this->ci->get(TaggableFileStore::class);
    }

    public function testTaggableFileStoreForNullPath(): void
    {
        // Set mock Locator
        $locator = Mockery::mock(ResourceLocatorInterface::class)
            ->shouldReceive('findResource')->withArgs(['cache://', true])->andReturn(null)
            ->getMock();
        $this->ci->set(ResourceLocatorInterface::class, $locator);

        $this->expectException(\Exception::class);
        $this->ci->get(TaggableFileStore::class);
    }

    public function testMemcachedStore(): void
    {
        // Set mock Config service
        $config = Mockery::mock(Config::class)
            ->shouldReceive('get')->with('cache.memcached')->once()->andReturn([])
            ->shouldReceive('get')->with('cache.prefix')->once()->andReturn('')
            ->getMock();
        $this->ci->set(Config::class, $config);
        $this->ci->get(MemcachedStore::class);
    }

    public function testRedisStore(): void
    {
        // Set mock Config service
        $config = Mockery::mock(Config::class)
            ->shouldReceive('get')->with('cache.redis')->once()->andReturn([])
            ->shouldReceive('get')->with('cache.prefix')->once()->andReturn('')
            ->getMock();
        $this->ci->set(Config::class, $config);
        $this->ci->get(RedisStore::class);
    }
}
