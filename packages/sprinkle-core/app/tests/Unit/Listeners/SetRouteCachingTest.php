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

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Slim\App;
use Slim\Interfaces\RouteCollectorInterface;
use UserFrosting\Config\Config;
use UserFrosting\Event\AppInitiatedEvent;
use UserFrosting\Sprinkle\Core\Listeners\SetRouteCaching;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

class SetRouteCachingTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testSetRouteCaching(): void
    {
        /** @var Config */
        $config = Mockery::mock(Config::class)
            ->shouldReceive('getBool')->with('cache.route', false)->once()->andReturn(true)
            ->shouldReceive('getString')->with('cache.routerFile')->once()->andReturn('route.cache')
            ->getMock();

        /** @var Mockery\MockInterface&ResourceLocatorInterface */
        $locator = Mockery::mock(ResourceLocatorInterface::class)
            ->shouldReceive('findResource')->with('cache://route.cache', true, true)->once()->andReturn('/foo/route.cache')
            ->getMock();

        /** @var Mockery\MockInterface&RouteCollectorInterface */
        $collector = Mockery::mock(RouteCollectorInterface::class)
            ->shouldReceive('setCacheFile')->with('/foo/route.cache')->once()
            ->getMock();

        /** @var Mockery\MockInterface&App<\DI\Container> */
        $app = Mockery::mock(App::class)
            ->shouldReceive('getRouteCollector')->once()->andReturn($collector)
            ->getMock();

        $event = new AppInitiatedEvent();

        $listener = new SetRouteCaching($config, $locator, $app);
        $listener($event);
    }
}
