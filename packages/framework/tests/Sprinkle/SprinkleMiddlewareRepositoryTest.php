<?php

/*
 * UserFrosting Framework (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/framework
 * @copyright Copyright (c) 2013-2024 Alexander Weissman, Louis Charette, Jordan Mele
 * @license   https://github.com/userfrosting/framework/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests\Sprinkle;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use stdClass;
use UserFrosting\Sprinkle\SprinkleManager;
use UserFrosting\Sprinkle\SprinkleMiddlewareRepository;
use UserFrosting\Sprinkle\SprinkleRecipe;
use UserFrosting\Support\Exception\BadClassNameException;
use UserFrosting\Support\Exception\BadInstanceOfException;
use UserFrosting\Tests\TestSprinkle\TestSprinkle;

class SprinkleMiddlewareRepositoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testGetAll(): void
    {
        $middleware = Mockery::mock(MiddlewareInterface::class);

        /** @var Mockery\MockInterface&SprinkleRecipe */
        $sprinkle = Mockery::mock(TestSprinkle::class)
            ->shouldReceive('getMiddlewares')->once()->andReturn([$middleware::class])
            ->getMock();

        /** @var Mockery\MockInterface&SprinkleManager */
        $sprinkleManager = Mockery::mock(SprinkleManager::class)
            ->shouldReceive('getSprinkles')->once()->andReturn([$sprinkle])
            ->getMock();

        /** @var Mockery\MockInterface&ContainerInterface */
        $ci = Mockery::mock(ContainerInterface::class)
            ->shouldReceive('get')->with($middleware::class)->once()->andReturn($middleware)
            ->getMock();

        $repository = new SprinkleMiddlewareRepository($sprinkleManager, $ci);
        $classes = $repository->all();

        $this->assertCount(1, $classes);
        $this->assertSame($middleware, $classes[0]);
    }

    public function testGetAllNoInterface(): void
    {
        /** @var Mockery\MockInterface&SprinkleRecipe */
        $sprinkle = Mockery::mock(SprinkleRecipe::class)
            ->shouldNotReceive('getMiddlewares')
            ->getMock();

        /** @var Mockery\MockInterface&SprinkleManager */
        $sprinkleManager = Mockery::mock(SprinkleManager::class)
            ->shouldReceive('getSprinkles')->once()->andReturn([$sprinkle])
            ->getMock();

        /** @var Mockery\MockInterface&ContainerInterface */
        $ci = Mockery::mock(ContainerInterface::class)
            ->shouldNotReceive('get')
            ->getMock();

        $repository = new SprinkleMiddlewareRepository($sprinkleManager, $ci);
        $classes = $repository->all();

        $this->assertCount(0, $classes);
    }

    public function testGetAllWithCommandNotFound(): void
    {
        /** @var Mockery\MockInterface&SprinkleRecipe */
        $sprinkle = Mockery::mock(TestSprinkle::class)
            ->shouldReceive('getMiddlewares')->once()->andReturn(['/Not/MiddlewareInterface'])
            ->getMock();

        /** @var Mockery\MockInterface&SprinkleManager */
        $sprinkleManager = Mockery::mock(SprinkleManager::class)
            ->shouldReceive('getSprinkles')->once()->andReturn([$sprinkle])
            ->getMock();

        /** @var Mockery\MockInterface&ContainerInterface */
        $ci = Mockery::mock(ContainerInterface::class);

        $repository = new SprinkleMiddlewareRepository($sprinkleManager, $ci);

        $this->expectException(BadClassNameException::class);
        $this->expectExceptionMessage('Middleware class `/Not/MiddlewareInterface` not found.');
        $repository->all();
    }

    public function testGetAllWithCommandWrongInterface(): void
    {
        $middleware = Mockery::mock(stdClass::class);

        /** @var Mockery\MockInterface&SprinkleRecipe */
        $sprinkle = Mockery::mock(TestSprinkle::class)
            ->shouldReceive('getMiddlewares')->once()->andReturn([$middleware::class])
            ->getMock();

        /** @var Mockery\MockInterface&SprinkleManager */
        $sprinkleManager = Mockery::mock(SprinkleManager::class)
            ->shouldReceive('getSprinkles')->once()->andReturn([$sprinkle])
            ->getMock();

        /** @var Mockery\MockInterface&ContainerInterface */
        $ci = Mockery::mock(ContainerInterface::class)
            ->shouldReceive('get')->with($middleware::class)->once()->andReturn($middleware)
            ->getMock();

        $repository = new SprinkleMiddlewareRepository($sprinkleManager, $ci);

        $this->expectException(BadInstanceOfException::class);
        $this->expectExceptionMessage('Middleware class `' . $middleware::class . "` doesn't implement " . MiddlewareInterface::class . '.');
        $repository->all();
    }
}
