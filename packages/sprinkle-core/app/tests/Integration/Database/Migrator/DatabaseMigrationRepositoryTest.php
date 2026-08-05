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

use Illuminate\Database\Schema\Builder;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use UserFrosting\Sprinkle\Core\Database\Migrator\DatabaseMigrationRepository;
use UserFrosting\Sprinkle\Core\Database\Models\MigrationTable;
use UserFrosting\Sprinkle\Core\Exceptions\MigrationNotFoundException;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase as TestCase;

/**
 * DatabaseMigrationRepository Test
 *
 * N.B.: This can can't use the refresh database trait, since the trait uses
 * part of the code we are testing.
 */
class DatabaseMigrationRepositoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testRepositoryCreation(): void
    {
        // Repository should not exists before migration is instantiated
        /** @var Builder */
        $builder = $this->getService(Builder::class);
        $builder->dropIfExists('migrationTest');

        // Replace the default model with a custom one for testing
        $this->assertFalse($builder->hasTable('migrationTest'));
        $this->getContainer()->set(MigrationTable::class, new TestMigration());
        $repository = $this->getService(DatabaseMigrationRepository::class);

        // Create table
        $repository->create();

        // Table should exist
        $this->assertTrue($builder->hasTable('migrationTest'));
        $this->assertTrue($repository->exists());

        // Check table structure. Need to sort, because order of columns can be
        // different based on database driver
        $expected = ['id', 'migration', 'batch'];
        $actual = $builder->getColumnListing('migrationTest');
        sort($expected);
        sort($actual);
        $this->assertSame($expected, $actual);

        // Delete repository
        $repository->delete();
        $this->assertFalse($builder->hasTable('migrationTest'));
        $this->assertFalse($repository->exists());

        // Assert deleting when table doesn't exist doesn't throw an error
        $repository->delete();
    }

    public function testRepository(): void
    {
        $this->getContainer()->set(MigrationTable::class, new TestMigration());
        $repository = $this->getService(DatabaseMigrationRepository::class);

        // Init batch number
        $this->assertSame(0, $repository->getLastBatchNumber());
        $this->assertSame(1, $repository->getNextBatchNumber());

        // Get all migrations (empty)
        $this->assertSame([], $repository->list());

        // Insert 2 from the same batch, plus two other batch
        $repository->log('foo', 2); // @phpstan-ignore-line
        $repository->log('bar', 2); // @phpstan-ignore-line
        $repository->log('foobar', 3); // @phpstan-ignore-line
        $repository->log('barfoo'); // @phpstan-ignore-line

        // Get Lists
        $this->assertSame([
            'foo',
            'bar',
            'foobar',
            'barfoo',
        ], $repository->list());
        $this->assertSame(['foobar', 'barfoo'], $repository->list(2)); // @phpstan-ignore-line
        $this->assertSame(['barfoo', 'foobar'], $repository->list(2, false)); // @phpstan-ignore-line

        // New batch number
        $this->assertSame(4, $repository->getLastBatchNumber());
        $this->assertSame(5, $repository->getNextBatchNumber());

        // Test Last
        $this->assertSame(['barfoo'], $repository->last()); // @phpstan-ignore-line

        // Get single Migration
        $this->assertTrue($repository->has('foobar')); // @phpstan-ignore-line
        $migration = $repository->get('foobar'); // @phpstan-ignore-line
        $this->assertSame('foobar', $migration['migration']); // @phpstan-ignore-line
        $this->assertSame(3, (int) $migration['batch']);

        // Delete Migration
        $repository->remove('foobar'); // @phpstan-ignore-line
        // @phpstan-ignore-next-line
        $this->assertSame([
            'foo',
            'bar',
            'barfoo',
        ], $repository->list());

        // Delete repository for next test
        $repository->delete();
    }

    public function testMigrationNotFound(): void
    {
        $this->getContainer()->set(MigrationTable::class, new TestMigration());
        $repository = $this->getService(DatabaseMigrationRepository::class);

        $this->expectException(MigrationNotFoundException::class);
        $repository->get('foo');  // @phpstan-ignore-line

        // Delete repository for next test
        $repository->delete();
    }

    /**
     * Legacy support for old migration class name.
     * UF V4 stored the migrations with a leading slash, which was removed in
     * UF V5, since we now use `::Class` to get the class name, instead of an
     * hardcoded string.
     * @see : https://github.com/userfrosting/UserFrosting/blob/adb574f378fb0af1c5eaa3be71458869431e7410/app/sprinkles/core/src/Database/Migrator/MigrationLocator.php#L86
     */
    public function testLegacyMigrations(): void
    {
        // Replace the default model with a custom one for testing
        $this->getContainer()->set(MigrationTable::class, new TestMigration());
        $repository = $this->getService(DatabaseMigrationRepository::class);

        // Log the migration with the old class name ("\" at the beginning)
        $repository->log('\\' . MigrationClassStub::class);

        // Check if the migration exists, should be, for legacy support. Accept both standard
        $this->assertSame([MigrationClassStub::class], $repository->list());
        $this->assertTrue($repository->has(MigrationClassStub::class));
        $this->assertTrue($repository->has('\\' . MigrationClassStub::class));
        $this->assertSame(MigrationClassStub::class, $repository->all()[0]['migration']);

        // Test with new format
        $result = $repository->get(MigrationClassStub::class);
        $this->assertSame(MigrationClassStub::class, $result['migration']);

        // Test get with legacy format
        $result = $repository->get('\\' . MigrationClassStub::class);
        $this->assertSame(MigrationClassStub::class, $result['migration']);

        // Test last
        $this->assertSame([MigrationClassStub::class], $repository->last());

        // Delete
        $repository->remove(MigrationClassStub::class);
        $this->assertFalse($repository->has(MigrationClassStub::class));

        // Delete repository for next test
        $repository->delete();
    }
}

// N.B.: Stub doesn't need to be a real migration class, just needs to be a class that exists
class MigrationClassStub
{
}

class TestMigration extends MigrationTable
{
    protected $table = 'migrationTest';
}
