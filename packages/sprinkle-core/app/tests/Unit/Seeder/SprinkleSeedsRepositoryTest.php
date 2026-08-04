<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit\Seeder;

use DI\Container;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;
use UserFrosting\Sprinkle\Core\Seeder\SeedInterface;
use UserFrosting\Sprinkle\Core\Seeder\SprinkleSeedsRepository;
use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\SeedRecipe;
use UserFrosting\Sprinkle\SprinkleManager;
use UserFrosting\Sprinkle\SprinkleRecipe;
use UserFrosting\Support\Exception\BadClassNameException;
use UserFrosting\Support\Exception\BadInstanceOfException;

/**
 * SprinkleSeedsRepository Test
 */
class SprinkleSeedsRepositoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testGetAll(): void
    {
        $mockSeed1 = Mockery::mock(SeedInterface::class);
        $mockSeed2 = Mockery::mock(SeedInterface::class);

        $ci = Mockery::mock(Container::class)
            ->shouldReceive('get')->with($mockSeed1::class)->andReturn($mockSeed1)
            ->shouldReceive('get')->with($mockSeed2::class)->andReturn($mockSeed2)
            ->getMock();

        /** @var Mockery\MockInterface&SeedRecipe */
        $sprinkle1 = Mockery::mock(SeedRecipe::class)
            ->shouldReceive('getSeeds')->andReturn([
                $mockSeed1::class,
                $mockSeed2::class,
            ])->getMock();

        /** @var Mockery\MockInterface&SprinkleRecipe */
        $sprinkle2 = Mockery::mock(SprinkleRecipe::class)
            ->shouldReceive('getSeeds')->andReturn([$mockSeed1::class])
            ->getMock();

        /** @var Mockery\MockInterface&SprinkleManager */
        $manager = Mockery::mock(SprinkleManager::class)
            ->shouldReceive('getSprinkles')->andReturn([
                $sprinkle1,
                $sprinkle2,
            ])->getMock();

        $repository = new SprinkleSeedsRepository($manager, $ci);

        $seeds = $repository->all();

        $this->assertCount(2, $seeds);
        $this->assertContainsOnlyInstancesOf(SeedInterface::class, $seeds);
    }

    public function testGetAllWithCommandNotFound(): void
    {
        /** @var Mockery\MockInterface&SeedRecipe */
        $sprinkle = Mockery::mock(SeedRecipe::class)
            ->shouldReceive('getSeeds')->andReturn(['/Not/Command'])
            ->getMock();

        /** @var Mockery\MockInterface&SprinkleManager */
        $sprinkleManager = Mockery::mock(SprinkleManager::class)
            ->shouldReceive('getSprinkles')->andReturn([$sprinkle])
            ->getMock();

        /** @var Mockery\MockInterface&ContainerInterface */
        $ci = Mockery::mock(ContainerInterface::class);

        $repository = new SprinkleSeedsRepository($sprinkleManager, $ci);

        $this->expectException(BadClassNameException::class);
        $this->expectExceptionMessage('Seed class `/Not/Command` not found.');
        $repository->all();
    }

    public function testGetAllWithCommandWrongInterface(): void
    {
        $seed = Mockery::mock(stdClass::class);

        /** @var Mockery\MockInterface&SeedRecipe */
        $sprinkle = Mockery::mock(SeedRecipe::class)
            ->shouldReceive('getSeeds')->andReturn([$seed::class])
            ->getMock();

        /** @var Mockery\MockInterface&SprinkleManager */
        $sprinkleManager = Mockery::mock(SprinkleManager::class)
            ->shouldReceive('getSprinkles')->andReturn([$sprinkle])
            ->getMock();

        /** @var Mockery\MockInterface&ContainerInterface */
        $ci = Mockery::mock(ContainerInterface::class)
            ->shouldReceive('get')->with($seed::class)->andReturn($seed)
            ->getMock();

        $repository = new SprinkleSeedsRepository($sprinkleManager, $ci);

        $this->expectException(BadInstanceOfException::class);
        $this->expectExceptionMessage('Seed class `' . $seed::class . "` doesn't implement " . SeedInterface::class . '.');
        $repository->all();
    }
}
