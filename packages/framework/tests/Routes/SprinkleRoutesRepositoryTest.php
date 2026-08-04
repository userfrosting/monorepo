<?php

/*
 * UserFrosting Framework (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/framework
 * @copyright Copyright (c) 2013-2024 Alexander Weissman, Louis Charette, Jordan Mele
 * @license   https://github.com/userfrosting/framework/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests\Bakery;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;
use UserFrosting\Routes\RouteDefinitionInterface;
use UserFrosting\Routes\SprinkleRoutesRepository;
use UserFrosting\Sprinkle\SprinkleManager;
use UserFrosting\Sprinkle\SprinkleRecipe;
use UserFrosting\Support\Exception\BadClassNameException;
use UserFrosting\Support\Exception\BadInstanceOfException;

class SprinkleRoutesRepositoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testGetAll(): void
    {
        $route = Mockery::mock(RouteDefinitionInterface::class);

        /** @var Mockery\MockInterface&SprinkleRecipe */
        $sprinkle = Mockery::mock(SprinkleRecipe::class)
            ->shouldReceive('getRoutes')->andReturn([$route::class])
            ->getMock();

        /** @var Mockery\MockInterface&SprinkleManager */
        $sprinkleManager = Mockery::mock(SprinkleManager::class)
            ->shouldReceive('getSprinkles')->andReturn([$sprinkle])
            ->getMock();

        /** @var Mockery\MockInterface&ContainerInterface */
        $ci = Mockery::mock(ContainerInterface::class)
            ->shouldReceive('get')->with($route::class)->andReturn($route)
            ->getMock();

        $repository = new SprinkleRoutesRepository($sprinkleManager, $ci);
        $classes = $repository->all();

        $this->assertCount(1, $classes);
        $this->assertSame($route, $classes[0]);
    }

    public function testGetAllWithCommandNotFound(): void
    {
        /** @var Mockery\MockInterface&SprinkleRecipe */
        $sprinkle = Mockery::mock(SprinkleRecipe::class)
            ->shouldReceive('getRoutes')->andReturn(['/Not/RouteDefinitionInterface'])
            ->getMock();

        /** @var Mockery\MockInterface&SprinkleManager */
        $sprinkleManager = Mockery::mock(SprinkleManager::class)
            ->shouldReceive('getSprinkles')->andReturn([$sprinkle])
            ->getMock();

        /** @var Mockery\MockInterface&ContainerInterface */
        $ci = Mockery::mock(ContainerInterface::class);

        $repository = new SprinkleRoutesRepository($sprinkleManager, $ci);

        $this->expectException(BadClassNameException::class);
        $this->expectExceptionMessage('Routes definition class `/Not/RouteDefinitionInterface` not found.');
        $repository->all();
    }

    public function testGetAllWithCommandWrongInterface(): void
    {
        $route = Mockery::mock(stdClass::class);

        /** @var Mockery\MockInterface&SprinkleRecipe */
        $sprinkle = Mockery::mock(SprinkleRecipe::class)
            ->shouldReceive('getRoutes')->andReturn([$route::class])
            ->getMock();

        /** @var Mockery\MockInterface&SprinkleManager */
        $sprinkleManager = Mockery::mock(SprinkleManager::class)
            ->shouldReceive('getSprinkles')->andReturn([$sprinkle])
            ->getMock();

        /** @var Mockery\MockInterface&ContainerInterface */
        $ci = Mockery::mock(ContainerInterface::class)
            ->shouldReceive('get')->with($route::class)->andReturn($route)
            ->getMock();

        $repository = new SprinkleRoutesRepository($sprinkleManager, $ci);

        $this->expectException(BadInstanceOfException::class);
        $this->expectExceptionMessage('Routes definition class `' . $route::class . "` doesn't implement " . RouteDefinitionInterface::class . '.');
        $repository->all();
    }
}
