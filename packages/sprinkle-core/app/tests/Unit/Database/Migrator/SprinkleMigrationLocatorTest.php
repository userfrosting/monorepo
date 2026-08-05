<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit\Database\Migrator;

use DI\Container;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;
use UserFrosting\Sprinkle\Core\Database\Migration;
use UserFrosting\Sprinkle\Core\Database\MigrationInterface;
use UserFrosting\Sprinkle\Core\Database\Migrator\SprinkleMigrationLocator;
use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\MigrationRecipe;
use UserFrosting\Sprinkle\SprinkleManager;
use UserFrosting\Sprinkle\SprinkleRecipe;
use UserFrosting\Support\Exception\BadClassNameException;
use UserFrosting\Support\Exception\BadInstanceOfException;

/**
 * Migration Locator Tests
 */
class SprinkleMigrationLocatorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testGetAll(): void
    {
        $mockMigration1 = Mockery::mock(Migration::class);
        $mockMigration2 = Mockery::mock(Migration::class);

        $ci = Mockery::mock(Container::class)
            ->shouldReceive('get')->with($mockMigration1::class)->andReturn($mockMigration1)
            ->shouldReceive('get')->with($mockMigration2::class)->andReturn($mockMigration2)
            ->getMock();

        /** @var Mockery\MockInterface&MigrationRecipe */
        $sprinkle1 = Mockery::mock(MigrationRecipe::class)
            ->shouldReceive('getMigrations')->andReturn([
                $mockMigration1::class,
                $mockMigration2::class,
            ])->getMock();

        /** @var Mockery\MockInterface&SprinkleRecipe */
        $sprinkle2 = Mockery::mock(SprinkleRecipe::class)
            ->shouldReceive('getMigrations')->andReturn([$mockMigration1::class])
            ->getMock();

        /** @var Mockery\MockInterface&SprinkleManager */
        $manager = Mockery::mock(SprinkleManager::class)
            ->shouldReceive('getSprinkles')->andReturn([
                $sprinkle1,
                $sprinkle2,
            ])->getMock();

        $locator = new SprinkleMigrationLocator($manager, $ci);

        $migrations = $locator->all();

        $this->assertCount(2, $migrations);
        $this->assertContainsOnlyInstancesOf(MigrationInterface::class, $migrations);
    }

    public function testGetAllWithCommandNotFound(): void
    {
        /** @var Mockery\MockInterface&MigrationRecipe */
        $sprinkle = Mockery::mock(MigrationRecipe::class)
            ->shouldReceive('getMigrations')->andReturn(['/Not/Command'])
            ->getMock();

        /** @var Mockery\MockInterface&SprinkleManager */
        $sprinkleManager = Mockery::mock(SprinkleManager::class)
            ->shouldReceive('getSprinkles')->andReturn([$sprinkle])
            ->getMock();

        /** @var Mockery\MockInterface&ContainerInterface */
        $ci = Mockery::mock(ContainerInterface::class);

        $repository = new SprinkleMigrationLocator($sprinkleManager, $ci);

        $this->expectException(BadClassNameException::class);
        $this->expectExceptionMessage('Migration class `/Not/Command` not found.');
        $repository->all();
    }

    public function testGetAllWithCommandWrongInterface(): void
    {
        $migration = Mockery::mock(stdClass::class);

        /** @var Mockery\MockInterface&MigrationRecipe */
        $sprinkle = Mockery::mock(MigrationRecipe::class)
            ->shouldReceive('getMigrations')->andReturn([$migration::class])
            ->getMock();

        /** @var Mockery\MockInterface&SprinkleManager */
        $sprinkleManager = Mockery::mock(SprinkleManager::class)
            ->shouldReceive('getSprinkles')->andReturn([$sprinkle])
            ->getMock();

        /** @var Mockery\MockInterface&ContainerInterface */
        $ci = Mockery::mock(ContainerInterface::class)
            ->shouldReceive('get')->with($migration::class)->andReturn($migration)
            ->getMock();

        $repository = new SprinkleMigrationLocator($sprinkleManager, $ci);

        $this->expectException(BadInstanceOfException::class);
        $this->expectExceptionMessage('Migration class `' . $migration::class . "` doesn't implement " . MigrationInterface::class . '.');
        $repository->all();
    }
}
