<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit\Twig;

use DI\Container;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;
use Twig\Extension\ExtensionInterface;
use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\TwigExtensionRecipe;
use UserFrosting\Sprinkle\Core\Twig\SprinkleTwigRepository;
use UserFrosting\Sprinkle\SprinkleManager;
use UserFrosting\Sprinkle\SprinkleRecipe;
use UserFrosting\Support\Exception\BadClassNameException;
use UserFrosting\Support\Exception\BadInstanceOfException;

/**
 * Twig Repository Tests
 */
class SprinkleTwigRepositoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testGetAll(): void
    {
        $mockExtension1 = Mockery::mock(ExtensionInterface::class);
        $mockExtension2 = Mockery::mock(ExtensionInterface::class);

        $ci = Mockery::mock(Container::class)
            ->shouldReceive('get')->with($mockExtension1::class)->once()->andReturn($mockExtension1)
            ->shouldReceive('get')->with($mockExtension2::class)->once()->andReturn($mockExtension2)
            ->getMock();

        /** @var Mockery\MockInterface&TwigExtensionRecipe */
        $sprinkle1 = Mockery::mock(TwigExtensionRecipe::class)
            ->shouldReceive('getTwigExtensions')->andReturn([
                $mockExtension1::class,
                $mockExtension2::class,
            ])->getMock();

        /** @var Mockery\MockInterface&SprinkleRecipe */
        $sprinkle2 = Mockery::mock(SprinkleRecipe::class)
            ->shouldReceive('getTwigExtensions')->andReturn([$mockExtension1::class])
            ->getMock();

        /** @var Mockery\MockInterface&SprinkleManager */
        $manager = Mockery::mock(SprinkleManager::class)
            ->shouldReceive('getSprinkles')->andReturn([
                $sprinkle1,
                $sprinkle2,
            ])->getMock();

        $repository = new SprinkleTwigRepository($manager, $ci);

        $extensions = $repository->all();

        $this->assertCount(2, $extensions);
        $this->assertContainsOnlyInstancesOf(ExtensionInterface::class, $extensions);
    }

    public function testGetAllWithCommandNotFound(): void
    {
        /** @var Mockery\MockInterface&TwigExtensionRecipe */
        $sprinkle = Mockery::mock(TwigExtensionRecipe::class)
            ->shouldReceive('getTwigExtensions')->andReturn(['/Not/Extension'])
            ->getMock();

        /** @var Mockery\MockInterface&SprinkleManager */
        $sprinkleManager = Mockery::mock(SprinkleManager::class)
            ->shouldReceive('getSprinkles')->andReturn([$sprinkle])
            ->getMock();

        /** @var Mockery\MockInterface&ContainerInterface */
        $ci = Mockery::mock(ContainerInterface::class);

        $repository = new SprinkleTwigRepository($sprinkleManager, $ci);

        $this->expectException(BadClassNameException::class);
        $this->expectExceptionMessage('Extension class `/Not/Extension` not found.');
        $repository->all();
    }

    public function testGetAllWithCommandWrongInterface(): void
    {
        $extension = Mockery::mock(stdClass::class);

        /** @var Mockery\MockInterface&TwigExtensionRecipe */
        $sprinkle = Mockery::mock(TwigExtensionRecipe::class)
            ->shouldReceive('getTwigExtensions')->andReturn([$extension::class])
            ->getMock();

        /** @var Mockery\MockInterface&SprinkleManager */
        $sprinkleManager = Mockery::mock(SprinkleManager::class)
            ->shouldReceive('getSprinkles')->andReturn([$sprinkle])
            ->getMock();

        /** @var Mockery\MockInterface&ContainerInterface */
        $ci = Mockery::mock(ContainerInterface::class)
            ->shouldReceive('get')->with($extension::class)->andReturn($extension)
            ->getMock();

        $repository = new SprinkleTwigRepository($sprinkleManager, $ci);

        $this->expectException(BadInstanceOfException::class);
        $this->expectExceptionMessage('Extension class `' . $extension::class . "` doesn't implement " . ExtensionInterface::class . '.');
        $repository->all();
    }
}
