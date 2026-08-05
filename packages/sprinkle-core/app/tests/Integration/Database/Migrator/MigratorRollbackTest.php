<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Database\Migrator;

use UserFrosting\Sprinkle\Core\Core;
use UserFrosting\Sprinkle\Core\Database\MigrationInterface;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationRepositoryInterface;
use UserFrosting\Sprinkle\Core\Database\Migrator\Migrator;
use UserFrosting\Sprinkle\Core\Exceptions\MigrationRollbackException;
use UserFrosting\Testing\TestCase;

/**
 * Sub test for Migrator.
 * Tests rollback related methods.
 */
class MigratorRollbackTest extends TestCase
{
    protected string $mainSprinkle = TestRollbackMigrationSprinkle::class;

    protected MigrationRepositoryInterface $repository;

    public function setUp(): void
    {
        parent::setUp();

        // Add installed
        /** @var MigrationRepositoryInterface */
        $repository = $this->getService(MigrationRepositoryInterface::class);
        $repository->log(StubAnalyserRollbackMigrationA::class, 1);
        $repository->log(StubAnalyserRollbackMigrationC::class, 2);
        $repository->log(StubAnalyserRollbackMigrationB::class, 2);
        $repository->log(StubAnalyserRollbackMigrationD::class, 3);

        $this->repository = $repository;
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->repository->delete();
    }

    public function testCanRollbackMigration(): void
    {
        $migrator = $this->getService(Migrator::class);

        // "D" and "A" are clear for rollback
        $this->assertTrue($migrator->canRollbackMigration(StubAnalyserRollbackMigrationD::class));
        $this->assertTrue($migrator->canRollbackMigration(StubAnalyserRollbackMigrationA::class));

        // B can be removed, as it depend on "C", but none depend on it
        $this->assertTrue($migrator->canRollbackMigration(StubAnalyserRollbackMigrationB::class));

        // But "C" can't be removed, as "B" depends on it and it's still installed
        $this->assertFalse($migrator->canRollbackMigration(StubAnalyserRollbackMigrationC::class));

        // "E" is not installed, so can't rollback
        $this->assertFalse($migrator->canRollbackMigration(StubAnalyserRollbackMigrationE::class));
    }

    public function testGetMigrationsForRollbackForLast(): void
    {
        $migrator = $this->getService(Migrator::class);

        $resultA = $migrator->getMigrationsForRollback();
        $resultB = $migrator->getMigrationsForRollback(1);

        $this->assertSame($resultA, $resultB);
        $this->assertSame([StubAnalyserRollbackMigrationD::class], $resultA);
        $this->assertSame([StubAnalyserRollbackMigrationD::class], $resultB);
    }

    public function testGetMigrationsForRollbackForStep2(): void
    {
        $migrator = $this->getService(Migrator::class);

        $result = $migrator->getMigrationsForRollback(2);

        $this->assertSame([
            StubAnalyserRollbackMigrationD::class,
            StubAnalyserRollbackMigrationB::class,
            StubAnalyserRollbackMigrationC::class,
        ], $result);
    }

    public function testGetMigrationsForReset(): void
    {
        $migrator = $this->getService(Migrator::class);

        $result = $migrator->getMigrationsForReset();

        $this->assertSame([
            StubAnalyserRollbackMigrationD::class,
            StubAnalyserRollbackMigrationB::class,
            StubAnalyserRollbackMigrationC::class,
            StubAnalyserRollbackMigrationA::class,
        ], $result);
    }

    public function testGetMigrationsForRollbackForTooManyStep(): void
    {
        $migrator = $this->getService(Migrator::class);

        // Will do same as reset in this case
        $result = $migrator->getMigrationsForRollback(99);

        $this->assertSame([
            StubAnalyserRollbackMigrationD::class,
            StubAnalyserRollbackMigrationB::class,
            StubAnalyserRollbackMigrationC::class,
            StubAnalyserRollbackMigrationA::class,
        ], $result);
    }

    public function testGetMigrationsForRollbackForStaleError(): void
    {
        $migrator = $this->getService(Migrator::class);

        // Add "E" as installed. It's gonna be stale
        $this->repository->log(StubAnalyserRollbackMigrationE::class, 1);

        // Get analyser back to propagate changes
        $migrator = $this->getService(Migrator::class);

        // Expect exception because of stale migration.
        $this->expectException(MigrationRollbackException::class);
        $migrator->getMigrationsForRollback(1);
    }
}

class TestRollbackMigrationSprinkle extends Core
{
    /**
     * Replace core migration with our dumb ones.
     */
    public function getMigrations(): array
    {
        return [
            StubAnalyserRollbackMigrationA::class,
            StubAnalyserRollbackMigrationB::class,
            StubAnalyserRollbackMigrationC::class,
            StubAnalyserRollbackMigrationD::class,
        ];
    }
}

class StubAnalyserRollbackMigrationA implements MigrationInterface
{
    public function up(): void
    {
    }

    public function down(): void
    {
    }
}

class StubAnalyserRollbackMigrationB extends StubAnalyserRollbackMigrationA
{
    /** @var class-string[] */
    public static $dependencies = [
        StubAnalyserRollbackMigrationC::class,
    ];
}

class StubAnalyserRollbackMigrationC extends StubAnalyserRollbackMigrationA
{
}

class StubAnalyserRollbackMigrationD extends StubAnalyserRollbackMigrationA
{
}

class StubAnalyserRollbackMigrationE extends StubAnalyserRollbackMigrationA
{
}
