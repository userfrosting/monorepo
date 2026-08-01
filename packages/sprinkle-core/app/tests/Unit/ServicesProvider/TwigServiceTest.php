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

use ArrayIterator;
use DI\Container;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Slim\App;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Interfaces\RouteParserInterface;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Core\ServicesProvider\TwigService;
use UserFrosting\Sprinkle\Core\Twig\TwigRepositoryInterface;
use UserFrosting\Testing\ContainerStub;
use UserFrosting\UniformResourceLocator\ResourceInterface;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

/**
 * Unit tests for `Twig` service.
 * Check to see if service returns what it's supposed to return
 */
class TwigServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected Container $ci;

    public function setUp(): void
    {
        parent::setUp();

        // Create container with provider to test
        $provider = new TwigService();
        $this->ci = ContainerStub::create($provider->register());
    }

    public function testMiddleware(): void
    {
        // Set App Mock
        $RouteParserInterface = Mockery::mock(RouteParserInterface::class);
        $RouteCollectorInterface = Mockery::mock(RouteCollectorInterface::class)
            ->shouldReceive('getRouteParser')->once()->andReturn($RouteParserInterface)
            ->getMock();
        $app = Mockery::mock(App::class)
            ->shouldReceive('getRouteCollector')->once()->andReturn($RouteCollectorInterface)
            ->shouldReceive('getBasePath')->once()->andReturn('')
            ->getMock();
        $this->ci->set(App::class, $app);

        $twig = Mockery::mock(Twig::class);
        $this->ci->set(Twig::class, $twig);

        $this->ci->get(TwigMiddleware::class);
    }

    public function testServiceWithCacheAndDebug(): void
    {
        // Set Config Mock
        $config = Mockery::mock(Config::class)
                ->shouldReceive('get')->with('cache.twig')->once()->andReturn(true)
                ->shouldReceive('get')->with('debug.twig')->once()->andReturn(true)
                ->getMock();
        $this->ci->set(Config::class, $config);

        // Set Locator Mock
        $resource = Mockery::mock(ResourceInterface::class)
            ->shouldReceive('getAbsolutePath')->once()->andReturn('')
            ->getMock();
        $locator = Mockery::mock(ResourceLocatorInterface::class)
                ->shouldReceive('getResources')->once()->andReturn([])
                ->shouldReceive('getResource')->with('cache://twig', true)->once()->andReturn($resource)
                ->getMock();
        $this->ci->set(ResourceLocatorInterface::class, $locator);

        /** @var TwigRepositoryInterface */
        $repository = Mockery::mock(TwigRepositoryInterface::class)
            ->shouldReceive('getIterator')->once()->andReturn(new ArrayIterator([]))
            ->getMock();
        $this->ci->set(TwigRepositoryInterface::class, $repository);

        $this->ci->get(Twig::class);
    }
}
