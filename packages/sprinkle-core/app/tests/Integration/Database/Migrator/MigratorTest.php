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

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use UserFrosting\Sprinkle\Core\Core;
use UserFrosting\Sprinkle\Core\Database\Migration;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationLocatorInterface;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationRepositoryInterface;
use UserFrosting\Sprinkle\Core\Database\Migrator\Migrator;
use UserFrosting\Testing\TestCase;

/**
 * Migrator Tests
 *
 * N.B.: This can can't use the refresh database trait, since the trait uses
 * part of the code we are testing.
 */
class MigratorTest extends TestCase
{
    protected string $mainSprinkle = TestMigrateSprinkle::class;

    public function setUp(): void
    {
        parent::setUp();

        // Manually reset the repository and database between test
        $this->resetDatabase();
    }

    public function tearDown(): void
    {
        // Manually reset the repository and database between test
        $this->resetDatabase();

        parent::tearDown();
    }

    protected function resetDatabase(): void
    {
        $repo = $this->ci->get(MigrationRepositoryInterface::class);
        $repo->delete();
        $repo->create();

        $builder = $this->ci->get(Builder::class);
        $builder->dropIfExists('test');
    }

    public function testGetters(): void
    {
        /** @var Migrator */
        $migrator = $this->ci->get(Migrator::class);

        // Test Constructor
        $this->assertInstanceOf(Migrator::class, $migrator); // @phpstan-ignore-line
        $this->assertInstanceOf(MigrationRepositoryInterface::class, $migrator->getRepository()); // @phpstan-ignore-line
        $this->assertInstanceOf(MigrationLocatorInterface::class, $migrator->getLocator()); // @phpstan-ignore-line
    }

    public function testPretendToMigrate(): void
    {
        $migrator = $this->ci->get(Migrator::class);

        // Initial state, table doesn't exist.
        // N.B.: Requires to get schema from connection, as otherwise it might
        // not work (different :memory: instance)
        $schema = $this->ci->get(Builder::class);
        $this->assertFalse($schema->hasTable('test'));

        // Pretend to migrate
        $result = $migrator->pretendToMigrate();

        // Assert results
        // N.B.: Don't assert exact string here, because it could change depending
        //       of DB, we only assert structure for now.
        $this->assertIsArray($result); // @phpstan-ignore-line
        $this->assertCount(1, $result);
        $this->assertIsString($result[StubMigrationA::class][0]['query']);

        // Table still doesn't exist.
        $this->assertFalse($schema->hasTable('test'));
    }

    public function testMigrate(): void
    {
        $migrator = $this->ci->get(Migrator::class);

        // Initial state, table doesn't exist.
        $schema = $this->ci->get(Builder::class);
        $this->assertFalse($schema->hasTable('test'));

        // Migrate
        $result = $migrator->migrate();

        // Assert results
        $this->assertSame([StubMigrationA::class], $result);

        // Assert table has been created
        $this->assertTrue($schema->hasTable('test'));
    }

    public function testMigrateWithNoOutstanding(): void
    {
        $migrator = $this->ci->get(Migrator::class);
        $this->assertNotSame([], $migrator->migrate());
        $this->assertSame([], $migrator->migrate());
    }

    public function testPretendToRollback(): void
    {
        $migrator = $this->ci->get(Migrator::class);

        // Initial state, table exist.
        $migrator->migrate();
        $schema = $this->ci->get(Builder::class);
        $this->assertTrue($schema->hasTable('test'));

        // Pretend to rollback
        $result = $migrator->pretendToRollback();

        // Assert results
        // N.B.: Don't assert exact string here, because it could change depending
        //       of DB, we only assert structure for now.
        $this->assertIsArray($result); // @phpstan-ignore-line
        $this->assertCount(1, $result);
        $this->assertIsString($result[StubMigrationA::class][0]['query']); // @phpstan-ignore-line

        // Table stills exist.
        $this->assertTrue($schema->hasTable('test'));
    }

    public function testRollback(): void
    {
        $migrator = $this->ci->get(Migrator::class);

        // Initial state, table exist.
        $migrator->migrate();
        $schema = $this->ci->get(Builder::class);
        $this->assertTrue($schema->hasTable('test'));

        // Rollback
        $result = $migrator->rollback();

        // Assert results
        $this->assertSame([StubMigrationA::class], $result);

        // Assert table has been removed
        $this->assertFalse($schema->hasTable('test'));
    }

    public function testRollbackWithNoOutstanding(): void
    {
        $migrator = $this->ci->get(Migrator::class);
        $migrator->migrate();
        $this->assertNotSame([], $migrator->rollback());
        $this->assertSame([], $migrator->rollback());
    }

    public function testReset(): void
    {
        $migrator = $this->ci->get(Migrator::class);

        // Test it's empty
        $result = $migrator->pretendToReset();
        $this->assertSame([], $result);

        // Install migration
        $result = $migrator->migrate();
        $this->assertSame([StubMigrationA::class], $result);
        $schema = $this->ci->get(Builder::class);
        $this->assertTrue($schema->hasTable('test'));

        // Test pretend to reset
        $result = $migrator->pretendToReset();
        $this->assertIsArray($result); // @phpstan-ignore-line
        $this->assertCount(1, $result);
        $this->assertIsString($result[StubMigrationA::class][0]['query']); // @phpstan-ignore-line

        // Test reset
        $result = $migrator->reset();
        $this->assertSame([StubMigrationA::class], $result);
        $schema = $this->ci->get(Builder::class);
        $this->assertFalse($schema->hasTable('test'));
    }

    /**
     * Legacy support for old migration class name.
     * UF V4 stored the migrations with a leading slash, which was removed in
     * UF V5, since we now use `::Class` to get the class name, instead of an
     * hardcoded string.
     * @see : https://github.com/userfrosting/UserFrosting/blob/adb574f378fb0af1c5eaa3be71458869431e7410/app/sprinkles/core/src/Database/Migrator/MigrationLocator.php#L86
     */
    public function testMigrateLegacy(): void
    {
        $migrator = $this->ci->get(Migrator::class);

        $repository = $migrator->getRepository();
        $repository->log('\\' . StubMigrationA::class);

        // Migrate
        $pending = $migrator->getPending();

        // Assert results
        $this->assertSame([], $pending);
    }
}

class TestMigrateSprinkle extends Core
{
    /**
     * Replace core migration with our dumb ones.
     */
    public function getMigrations(): array
    {
        return [
            StubMigrationA::class,
        ];
    }
}

class StubMigrationA extends Migration
{
    public function up(): void
    {
        $this->schema->create('test', function (Blueprint $table) {
            $table->id();
            $table->string('foo');
        });
    }

    public function down(): void
    {
        $this->schema->drop('test');
    }
}
