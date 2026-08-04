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

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Schema\Grammars\Grammar;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use UserFrosting\Sprinkle\Core\Database\MigrationInterface;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationLocatorInterface;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationRepositoryInterface;
use UserFrosting\Sprinkle\Core\Database\Migrator\Migrator;
use UserFrosting\Sprinkle\Core\Exceptions\MigrationDependencyNotMetException;

/**
 * Tests for the Migrator Class
 *
 * Theses tests make sure the Migrator works correctly, without validating
 * against a simulated database. Those tests are performed by `DatabaseMigratorIntegrationTest`
 */
class MigratorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    // Shared mock objects
    protected MigrationRepositoryInterface $repository;
    protected MigrationLocatorInterface $locator;
    protected Connection $connection;
    protected Capsule $database;

    /**
     * Setup base mock and migrator instance.
     */
    public function setUp(): void
    {
        // Boot parent TestCase
        parent::setUp();

        // Create mock objects
        /** @var Mockery\MockInterface&Capsule */
        $database = Mockery::mock(Capsule::class)
            ->shouldReceive('getConnection')
            ->with(null)
            ->andReturn(Mockery::mock(Connection::class))
            ->getMock();
        $this->database = $database;
        $this->repository = Mockery::mock(MigrationRepositoryInterface::class);
        $this->locator = Mockery::mock(MigrationLocatorInterface::class);
    }

    protected function getMigrator(): Migrator
    {
        return new Migrator($this->repository, $this->locator, $this->database);
    }

    public function testConstructor(): Migrator
    {
        $migrator = $this->getMigrator();

        // @phpstan-ignore-next-line
        $this->assertInstanceOf(Migrator::class, $migrator);

        return $migrator;
    }

    /**
     * @depends testConstructor
     *
     * @param Migrator $migrator
     */
    public function testRepositoryMethods(Migrator $migrator): void
    {
        // Assert get repo from the main one
        // @phpstan-ignore-next-line
        $this->assertInstanceOf(MigrationRepositoryInterface::class, $migrator->getRepository());

        // Get mock
        /** @var Mockery\MockInterface&MigrationRepositoryInterface */
        $repository = Mockery::mock(MigrationRepositoryInterface::class)
            ->shouldReceive('exists')->twice()->andReturn(true, false)
            ->getMock();

        // Set mock and test change
        $this->assertNotSame($repository, $migrator->getRepository());
        $migrator->setRepository($repository);
        $this->assertSame($repository, $migrator->getRepository());

        // Assert `repositoryExists` mock
        $this->assertTrue($migrator->repositoryExists());
        $this->assertFalse($migrator->repositoryExists());
    }

    /**
     * @depends testConstructor
     *
     * @param Migrator $migrator
     */
    public function testLocatorMethods(Migrator $migrator): void
    {
        // Assert get locator from the main one
        // @phpstan-ignore-next-line
        $this->assertInstanceOf(MigrationLocatorInterface::class, $migrator->getLocator());

        // Get new mock
        $locator = Mockery::mock(MigrationLocatorInterface::class);

        // Set new mock and test change
        $this->assertNotSame($locator, $migrator->getLocator());
        $migrator->setLocator($locator);
        $this->assertSame($locator, $migrator->getLocator());
    }

    /**
     * @depends testConstructor
     *
     * @param Migrator $migrator
     */
    public function testMigrate(Migrator $migrator): void
    {
        // Mock a migration for locator
        $migration1 = Mockery::mock(MigrationInterface::class)
            ->shouldReceive('up')->once()->andReturn(null)
            ->getMock();
        $migration2 = Mockery::mock(MigrationInterface::class)
            ->shouldReceive('up')->once()->andReturn(null)
            ->getMock();

        // Create new repository mock for batch call and log
        $repository = Mockery::mock(MigrationRepositoryInterface::class)
            ->shouldReceive('getNextBatchNumber')->once()->andReturn(2)
            ->shouldReceive('log')->with($migration1::class, 2)->once()->andReturn(true)
            ->shouldReceive('log')->with($migration2::class, 2)->once()->andReturn(true)
            ->getMock();

        // Create new locator with our mock migration
        $locator = Mockery::mock(MigrationLocatorInterface::class)
            ->shouldReceive('get')->with($migration1::class)->once()->andReturn($migration1)
            ->shouldReceive('get')->with($migration2::class)->once()->andReturn($migration2)
            ->getMock();

        // We mock connection and Grammar, to simulate `supportsSchemaTransactions`
        $grammar = Mockery::mock(Grammar::class)
            ->shouldReceive('supportsSchemaTransactions')->times(2)->andReturn(false)
            ->getMock();
        $connection = Mockery::mock(Connection::class)
            ->shouldReceive('getSchemaGrammar')->times(2)->andReturn($grammar)
            ->getMock();
        $manager = Mockery::mock(DatabaseManager::class)
            ->shouldReceive('getDefaultConnection')->andReturn(null)
            ->getMock();
        $database = Mockery::mock(Capsule::class)
            ->shouldReceive('getConnection')->with(null)->andReturn($connection)
            ->shouldReceive('getDatabaseManager')->andReturn($manager)
            ->getMock();

        // Create partial mock of migrator, so we can spoof "getPending"
        $migrator = Mockery::mock(Migrator::class, [$repository, $locator, $database])->makePartial();
        $migrator->shouldReceive('getPending')->once()->andReturn([$migration1::class, $migration2::class]);

        // Migrate (Step = false)
        $result = $migrator->migrate();

        // Assert results
        $this->assertSame([$migration1::class, $migration2::class], $result);
    }

    /**
     * N.B.: Transaction is only tested for behavior.
     *
     * @depends testConstructor
     * @depends testMigrate
     *
     * @param Migrator $migrator
     */
    public function testMigrateWithStepsAndTransaction(Migrator $migrator): void
    {
        // Mock a migration for locator
        $migration1 = Mockery::mock(MigrationInterface::class);
        $migration2 = Mockery::mock(MigrationInterface::class);

        // Create new repository mock for batch call and log
        $repository = Mockery::mock(MigrationRepositoryInterface::class)
            ->shouldReceive('getNextBatchNumber')->once()->andReturn(2)
            ->shouldReceive('log')->with($migration1::class, 2)->once()->andReturn(true)
            ->shouldReceive('log')->with($migration2::class, 3)->once()->andReturn(true) // 3, as steps is true
            ->getMock();

        // Create new locator with our mock migration
        $locator = Mockery::mock(MigrationLocatorInterface::class)
            ->shouldReceive('get')->with($migration1::class)->once()->andReturn($migration1)
            ->shouldReceive('get')->with($migration2::class)->once()->andReturn($migration2)
            ->getMock();

        // We mock connection and Grammar, to simulate `supportsSchemaTransactions`
        $grammar = Mockery::mock(Grammar::class)
            ->shouldReceive('supportsSchemaTransactions')->times(2)->andReturn(true)
            ->getMock();
        $connection = Mockery::mock(Connection::class)
            ->shouldReceive('getSchemaGrammar')->times(2)->andReturn($grammar)
            ->shouldReceive('transaction')->times(2)
            ->getMock();
        $manager = Mockery::mock(DatabaseManager::class)
            ->shouldReceive('getDefaultConnection')->andReturn(null)
            ->getMock();
        $database = Mockery::mock(Capsule::class)
            ->shouldReceive('getConnection')->with(null)->andReturn($connection)
            ->shouldReceive('getDatabaseManager')->andReturn($manager)
            ->getMock();

        // Create partial mock of migrator, so we can spoof "getPending"
        $migrator = Mockery::mock(Migrator::class, [$repository, $locator, $database])->makePartial();
        $migrator->shouldReceive('getPending')->once()->andReturn([$migration1::class, $migration2::class]);

        // Migrate (Step = true)
        $result = $migrator->migrate(step: true);

        // Assert results
        $this->assertSame([$migration1::class, $migration2::class], $result);
    }

    public function testMigrateWithNoPending(): void
    {
        $migrator = Mockery::mock(Migrator::class)->makePartial();
        $migrator->shouldReceive('getPending')->once()->andReturn([]);

        $result = $migrator->migrate();
        $this->assertSame([], $result);
    }

    public function testMigrateWithPendingException(): void
    {
        $migrator = Mockery::mock(Migrator::class)->makePartial();
        $migrator->shouldReceive('getPending')->once()->andThrow(new MigrationDependencyNotMetException());

        $this->expectException(MigrationDependencyNotMetException::class);
        $migrator->migrate();
    }

    public function testPretendToMigrate(): void
    {
        // Return from Connection pretend.
        // The actual data here is not 100% true, see Integration test for it.
        $queries = [['query' => 'foo']];

        // Mock a migration for locator
        $migration = Mockery::mock(MigrationInterface::class);

        // Create new locator with our mock migration
        $locator = Mockery::mock(MigrationLocatorInterface::class)
            ->shouldReceive('get')->with($migration::class)->once()->andReturn($migration)
            ->getMock();

        // Up is not called, as the callable passed to pretend is not mocked here
        // See Integration test for a more complete test.
        $connection = Mockery::mock(Connection::class)
            ->shouldReceive('pretend')->once()->andReturn($queries)
            ->getMock();
        $manager = Mockery::mock(DatabaseManager::class)
            ->shouldReceive('getDefaultConnection')->andReturn(null)
            ->getMock();
        $database = Mockery::mock(Capsule::class)
            ->shouldReceive('getConnection')->with(null)->andReturn($connection)
            ->shouldReceive('getDatabaseManager')->andReturn($manager)
            ->getMock();

        // Create partial mock of migrator, so we can spoof "getPending"
        $migrator = Mockery::mock(Migrator::class, [$this->repository, $locator, $database])->makePartial();
        $migrator->shouldReceive('getPending')->once()->andReturn([$migration::class]);

        // Pretend to migrate
        $result = $migrator->pretendToMigrate();

        // Assert results
        $this->assertSame([
            $migration::class => $queries
        ], $result);
    }

    public function testPretendToMigrateWithNoPending(): void
    {
        $migrator = Mockery::mock(Migrator::class)->makePartial();
        $migrator->shouldReceive('getPending')->once()->andReturn([]);

        $result = $migrator->pretendToMigrate();
        $this->assertSame([], $result);
    }

    public function testPretendToMigrateWithPendingException(): void
    {
        $migrator = Mockery::mock(Migrator::class)->makePartial();
        $migrator->shouldReceive('getPending')->once()->andThrow(new MigrationDependencyNotMetException());

        $this->expectException(MigrationDependencyNotMetException::class);
        $migrator->pretendToMigrate();
    }

    /**
     * @depends testConstructor
     *
     * @param Migrator $migrator
     */
    public function testRollback(Migrator $migrator): void
    {
        // Mock a migration for locator
        $migration1 = Mockery::mock(MigrationInterface::class)
            ->shouldReceive('down')->once()->andReturn(null)
            ->getMock();
        $migration2 = Mockery::mock(MigrationInterface::class)
            ->shouldReceive('down')->once()->andReturn(null)
            ->getMock();

        // Create new repository mock for batch call and log
        $repository = Mockery::mock(MigrationRepositoryInterface::class)
            ->shouldReceive('remove')->with($migration1::class)->once()->andReturn(true)
            ->shouldReceive('remove')->with($migration2::class)->once()->andReturn(true)
            ->getMock();

        // Create new locator with our mock migration
        $locator = Mockery::mock(MigrationLocatorInterface::class)
            ->shouldReceive('get')->with($migration1::class)->once()->andReturn($migration1)
            ->shouldReceive('get')->with($migration2::class)->once()->andReturn($migration2)
            ->getMock();

        // We mock connection and Grammar, to simulate `supportsSchemaTransactions`
        $grammar = Mockery::mock(Grammar::class)
            ->shouldReceive('supportsSchemaTransactions')->times(2)->andReturn(false)
            ->getMock();
        $connection = Mockery::mock(Connection::class)
            ->shouldReceive('getSchemaGrammar')->times(2)->andReturn($grammar)
            ->getMock();
        $manager = Mockery::mock(DatabaseManager::class)
            ->shouldReceive('getDefaultConnection')->andReturn(null)
            ->getMock();
        $database = Mockery::mock(Capsule::class)
            ->shouldReceive('getConnection')->with(null)->andReturn($connection)
            ->shouldReceive('getDatabaseManager')->andReturn($manager)
            ->getMock();

        // Create partial mock of migrator, so we can spoof "getPending"
        $migrator = Mockery::mock(Migrator::class, [$repository, $locator, $database])->makePartial();
        $migrator->shouldReceive('getMigrationsForRollback')->with(1)->once()->andReturn([$migration1::class, $migration2::class]);

        // Rollback (steps = 1; default)
        $result = $migrator->rollback();

        // Assert results
        $this->assertSame([$migration1::class, $migration2::class], $result);
    }

    /**
     * N.B.: Transaction is only tested for behavior.
     *
     * @depends testConstructor
     * @depends testMigrate
     *
     * @param Migrator $migrator
     */
    public function testRollbackWithStepsAndTransaction(Migrator $migrator): void
    {
        // Mock a migration for locator
        $migration1 = Mockery::mock(MigrationInterface::class);
        $migration2 = Mockery::mock(MigrationInterface::class);

        // Create new repository mock for batch call and log
        $repository = Mockery::mock(MigrationRepositoryInterface::class)
            ->shouldReceive('remove')->with($migration1::class)->once()->andReturn(true)
            ->shouldReceive('remove')->with($migration2::class)->once()->andReturn(true)
            ->getMock();

        // Create new locator with our mock migration
        $locator = Mockery::mock(MigrationLocatorInterface::class)
            ->shouldReceive('get')->with($migration1::class)->once()->andReturn($migration1)
            ->shouldReceive('get')->with($migration2::class)->once()->andReturn($migration2)
            ->getMock();

        // We mock connection and Grammar, to simulate `supportsSchemaTransactions`
        $grammar = Mockery::mock(Grammar::class)
            ->shouldReceive('supportsSchemaTransactions')->times(2)->andReturn(true)
            ->getMock();
        $connection = Mockery::mock(Connection::class)
            ->shouldReceive('getSchemaGrammar')->times(2)->andReturn($grammar)
            ->shouldReceive('transaction')->times(2)
            ->getMock();
        $manager = Mockery::mock(DatabaseManager::class)
            ->shouldReceive('getDefaultConnection')->andReturn(null)
            ->getMock();
        $database = Mockery::mock(Capsule::class)
            ->shouldReceive('getConnection')->with(null)->andReturn($connection)
            ->shouldReceive('getDatabaseManager')->andReturn($manager)
            ->getMock();

        // Create partial mock of migrator, so we can spoof "getPending"
        $migrator = Mockery::mock(Migrator::class, [$repository, $locator, $database])->makePartial();
        $migrator->shouldReceive('getMigrationsForRollback')->with(2)->once()->andReturn([$migration1::class, $migration2::class]);

        // Migrate (Step = true)
        $result = $migrator->rollback(2);

        // Assert results
        $this->assertSame([$migration1::class, $migration2::class], $result);
    }

    public function testRollbackWithNoPending(): void
    {
        $migrator = Mockery::mock(Migrator::class)->makePartial();
        $migrator->shouldReceive('getMigrationsForRollback')->once()->andReturn([]);

        $result = $migrator->rollback();
        $this->assertSame([], $result);
    }

    public function testRollbackWithPendingException(): void
    {
        $migrator = Mockery::mock(Migrator::class)->makePartial();
        $migrator->shouldReceive('getMigrationsForRollback')->once()->andThrow(new MigrationDependencyNotMetException());

        $this->expectException(MigrationDependencyNotMetException::class);
        $migrator->rollback();
    }

    public function testPretendToRollback(): void
    {
        // Return from Connection pretend.
        // The actual data here is not 100% true, see Integration test for it.
        $queries = [['query' => 'foo']];

        // Mock a migration for locator
        $migration = Mockery::mock(MigrationInterface::class);

        // Create new locator with our mock migration
        $locator = Mockery::mock(MigrationLocatorInterface::class)
            ->shouldReceive('get')->with($migration::class)->once()->andReturn($migration)
            ->getMock();

        // Up is not called, as the callable passed to pretend is not mocked here
        // See Integration test for a more complete test.
        $connection = Mockery::mock(Connection::class)
            ->shouldReceive('pretend')->once()->andReturn($queries)
            ->getMock();
        $manager = Mockery::mock(DatabaseManager::class)
            ->shouldReceive('getDefaultConnection')->andReturn(null)
            ->getMock();
        $database = Mockery::mock(Capsule::class)
            ->shouldReceive('getConnection')->with(null)->andReturn($connection)
            ->shouldReceive('getDatabaseManager')->andReturn($manager)
            ->getMock();

        // Create partial mock of migrator, so we can spoof "getPending"
        $migrator = Mockery::mock(Migrator::class, [$this->repository, $locator, $database])->makePartial();
        $migrator->shouldReceive('getMigrationsForRollback')->once()->andReturn([$migration::class]);

        // Pretend to migrate
        $result = $migrator->pretendToRollback();

        // Assert results
        // @phpstan-ignore-next-line
        $this->assertSame([
            $migration::class => $queries
        ], $result);
    }

    public function testPretendToRollbackWithNoPending(): void
    {
        $migrator = Mockery::mock(Migrator::class)->makePartial();
        $migrator->shouldReceive('getMigrationsForRollback')->once()->andReturn([]);

        $result = $migrator->pretendToRollback();
        $this->assertSame([], $result);
    }

    public function testPretendToRollbackWithPendingException(): void
    {
        $migrator = Mockery::mock(Migrator::class)->makePartial();
        $migrator->shouldReceive('getMigrationsForRollback')->once()->andThrow(new MigrationDependencyNotMetException());

        $this->expectException(MigrationDependencyNotMetException::class);
        $migrator->pretendToRollback();
    }

    /**
     * @depends testConstructor
     *
     * @param Migrator $migrator
     */
    public function testReset(Migrator $migrator): void
    {
        // Mock a migration for locator
        $migration1 = Mockery::mock(MigrationInterface::class)
            ->shouldReceive('down')->once()->andReturn(null)
            ->getMock();
        $migration2 = Mockery::mock(MigrationInterface::class)
            ->shouldReceive('down')->once()->andReturn(null)
            ->getMock();

        // Create new repository mock for batch call and log
        $repository = Mockery::mock(MigrationRepositoryInterface::class)
            ->shouldReceive('remove')->with($migration1::class)->once()->andReturn(true)
            ->shouldReceive('remove')->with($migration2::class)->once()->andReturn(true)
            ->getMock();

        // Create new locator with our mock migration
        $locator = Mockery::mock(MigrationLocatorInterface::class)
            ->shouldReceive('get')->with($migration1::class)->once()->andReturn($migration1)
            ->shouldReceive('get')->with($migration2::class)->once()->andReturn($migration2)
            ->getMock();

        // We mock connection and Grammar, to simulate `supportsSchemaTransactions`
        $grammar = Mockery::mock(Grammar::class)
            ->shouldReceive('supportsSchemaTransactions')->times(2)->andReturn(false)
            ->getMock();
        $connection = Mockery::mock(Connection::class)
            ->shouldReceive('getSchemaGrammar')->times(2)->andReturn($grammar)
            ->getMock();
        $manager = Mockery::mock(DatabaseManager::class)
            ->shouldReceive('getDefaultConnection')->andReturn(null)
            ->getMock();
        $database = Mockery::mock(Capsule::class)
            ->shouldReceive('getConnection')->with(null)->andReturn($connection)
            ->shouldReceive('getDatabaseManager')->andReturn($manager)
            ->getMock();

        // Create partial mock of migrator, so we can spoof "getPending"
        $migrator = Mockery::mock(Migrator::class, [$repository, $locator, $database])->makePartial();
        $migrator->shouldReceive('getMigrationsForReset')->once()->andReturn([$migration1::class, $migration2::class]);

        // Reset
        $result = $migrator->reset();

        // Assert results
        $this->assertSame([$migration1::class, $migration2::class], $result);
    }

    public function testResetWithNoPending(): void
    {
        $migrator = Mockery::mock(Migrator::class)->makePartial();
        $migrator->shouldReceive('getMigrationsForReset')->once()->andReturn([]);

        $result = $migrator->reset();
        $this->assertSame([], $result);
    }

    public function testResetWithPendingException(): void
    {
        $migrator = Mockery::mock(Migrator::class)->makePartial();
        $migrator->shouldReceive('getMigrationsForReset')->once()->andThrow(new MigrationDependencyNotMetException());

        $this->expectException(MigrationDependencyNotMetException::class);
        $migrator->reset();
    }

    public function testPretendToReset(): void
    {
        // Return from Connection pretend.
        // The actual data here is not 100% true, see Integration test for it.
        $queries = [['query' => 'foo']];

        // Mock a migration for locator
        $migration = Mockery::mock(MigrationInterface::class);

        // Create new locator with our mock migration
        $locator = Mockery::mock(MigrationLocatorInterface::class)
            ->shouldReceive('get')->with($migration::class)->once()->andReturn($migration)
            ->getMock();

        // Up is not called, as the callable passed to pretend is not mocked here
        // See Integration test for a more complete test.
        $connection = Mockery::mock(Connection::class)
            ->shouldReceive('pretend')->once()->andReturn($queries)
            ->getMock();
        $manager = Mockery::mock(DatabaseManager::class)
            ->shouldReceive('getDefaultConnection')->andReturn(null)
            ->getMock();
        $database = Mockery::mock(Capsule::class)
            ->shouldReceive('getConnection')->with(null)->andReturn($connection)
            ->shouldReceive('getDatabaseManager')->andReturn($manager)
            ->getMock();

        // Create partial mock of migrator, so we can spoof "getPending"
        $migrator = Mockery::mock(Migrator::class, [$this->repository, $locator, $database])->makePartial();
        $migrator->shouldReceive('getMigrationsForReset')->once()->andReturn([$migration::class]);

        // Pretend to migrate
        $result = $migrator->pretendToReset();

        // Assert results
        // @phpstan-ignore-next-line
        $this->assertSame([
            $migration::class => $queries
        ], $result);
    }

    public function testPretendToResetWithNoPending(): void
    {
        $migrator = Mockery::mock(Migrator::class)->makePartial();
        $migrator->shouldReceive('getMigrationsForReset')->once()->andReturn([]);

        $result = $migrator->pretendToReset();
        $this->assertSame([], $result);
    }

    public function testPretendToResetWithPendingException(): void
    {
        $migrator = Mockery::mock(Migrator::class)->makePartial();
        $migrator->shouldReceive('getMigrationsForReset')->once()->andThrow(new MigrationDependencyNotMetException());

        $this->expectException(MigrationDependencyNotMetException::class);
        $migrator->pretendToReset();
    }
}
