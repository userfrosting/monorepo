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
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use UserFrosting\Config\Config;
use UserFrosting\Config\ConfigPathBuilder;
use UserFrosting\Sprinkle\Core\ServicesProvider\ConfigService;
use UserFrosting\Support\Repository\Loader\ArrayFileLoader;
use UserFrosting\Testing\ContainerStub;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

/**
 * Integration tests for `config` service.
 * Check to see if service returns what it's supposed to return
 */
class ConfigServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected Container $ci;

    public function setUp(): void
    {
        parent::setUp();

        // Create container with provider to test
        $provider = new ConfigService();
        $this->ci = ContainerStub::create($provider->register());
    }

    public function testConfig(): void
    {
        // Set mock Locator
        $loader = Mockery::mock(ArrayFileLoader::class)
            ->shouldReceive('load')->once()->andReturn([])
            ->getMock();
        $this->ci->set(ArrayFileLoader::class, $loader);
        $this->ci->get(Config::class);
    }

    // TODO : This must be properly tested...
    // public function testUFMode(): void
    // {
    //     // Set mock Locator
    //     $locator = Mockery::mock(ResourceLocatorInterface::class)
    //         ->shouldReceive('getBasePath')->andReturn('')
    //         ->getMock();
    //     $this->ci->set(ResourceLocatorInterface::class, $locator);

    //     $this->assertSame('foobar', $this->ci->get('UF_MODE'));
    // }

    public function testArrayFileLoader(): void
    {
        // Set mock Config Path Builder
        $builder = Mockery::mock(ConfigPathBuilder::class)
            ->shouldReceive('buildPaths')->once()->andReturn([])
            ->getMock();
        $this->ci->set(ConfigPathBuilder::class, $builder);

        // Set mock Locator, as it's required to get "UF_MODE".
        $locator = Mockery::mock(ResourceLocatorInterface::class)
            ->shouldReceive('getBasePath')->andReturn('')
            ->getMock();
        $this->ci->set(ResourceLocatorInterface::class, $locator);
        $this->ci->get(ArrayFileLoader::class);
    }
}
