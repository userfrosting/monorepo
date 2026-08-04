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
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use UserFrosting\Sprinkle\Core\Database\MigrationInterface;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationLocatorInterface;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationRepositoryInterface;
use UserFrosting\Sprinkle\Core\Database\Migrator\Migrator;
use UserFrosting\Sprinkle\Core\Exceptions\MigrationRollbackException;

/**
 * Sub test for Migrator.
 * Tests rollback related methods.
 */
class MigratorRollbackTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * Test base functionality
     */
    public function testGetMigrationsForRollback(): void
    {
        /** @var Mockery\MockInterface&MigrationRepositoryInterface */
        $installed = Mockery::mock(MigrationRepositoryInterface::class)
            ->shouldReceive('list')->twice()->andReturn([
                StubAnalyserRollbackMigrationD::class,
            ])
            ->shouldReceive('has')->with(StubAnalyserRollbackMigrationD::class)->once()->andReturn(true)
            ->getMock();

        /** @var Mockery\MockInterface&MigrationLocatorInterface */
        $available = Mockery::mock(MigrationLocatorInterface::class)
            ->shouldReceive('list')->once()->andReturn([
                StubAnalyserRollbackMigrationD::class,
            ])
            ->getMock();

        /** @var Mockery\MockInterface&Capsule */
        $database = Mockery::mock(Capsule::class)
            ->shouldReceive('getConnection')
            ->with(null)
            ->andReturn(Mockery::mock(Connection::class))
            ->getMock();

        $analyser = new Migrator($installed, $available, $database);

        $result = $analyser->getMigrationsForRollback(1);
        $this->assertSame([StubAnalyserRollbackMigrationD::class], $result);
    }

    /**
     * Test base functionality (reset version).
     */
    public function testGetMigrationsForReset(): void
    {
        /** @var Mockery\MockInterface&MigrationRepositoryInterface */
        $installed = Mockery::mock(MigrationRepositoryInterface::class)
            ->shouldReceive('list')->twice()->andReturn([
                StubAnalyserRollbackMigrationD::class,
            ])
            ->shouldReceive('has')->with(StubAnalyserRollbackMigrationD::class)->once()->andReturn(true)
            ->getMock();

        /** @var Mockery\MockInterface&MigrationLocatorInterface */
        $available = Mockery::mock(MigrationLocatorInterface::class)
            ->shouldReceive('list')->once()->andReturn([
                StubAnalyserRollbackMigrationD::class,
            ])
            ->getMock();

        /** @var Mockery\MockInterface&Capsule */
        $database = Mockery::mock(Capsule::class)
            ->shouldReceive('getConnection')
            ->with(null)
            ->andReturn(Mockery::mock(Connection::class))
            ->getMock();

        $analyser = new Migrator($installed, $available, $database);

        $result = $analyser->getMigrationsForReset();
        $this->assertSame([StubAnalyserRollbackMigrationD::class], $result);
    }

    /**
     * @depends testGetMigrationsForRollback
     * @depends testGetMigrationsForReset
     */
    public function testValidateRollbackMigration(): void
    {
        // Set mock & analyser
        /** @var Mockery\MockInterface&MigrationRepositoryInterface */
        $installed = Mockery::mock(MigrationRepositoryInterface::class)
            ->shouldReceive('list')->times(4)->andReturn([
                StubAnalyserRollbackMigrationA::class,
                StubAnalyserRollbackMigrationB::class,
                StubAnalyserRollbackMigrationC::class,
                StubAnalyserRollbackMigrationD::class,
            ])
            ->shouldReceive('has')->with(StubAnalyserRollbackMigrationD::class)->twice()->andReturn(true)
            ->getMock();

        /** @var Mockery\MockInterface&MigrationLocatorInterface */
        $available = Mockery::mock(MigrationLocatorInterface::class)
            ->shouldReceive('list')->twice()->andReturn([
                StubAnalyserRollbackMigrationA::class,
                StubAnalyserRollbackMigrationB::class,
                StubAnalyserRollbackMigrationC::class,
                StubAnalyserRollbackMigrationD::class,
            ])
            ->shouldReceive('has')->with(StubAnalyserRollbackMigrationC::class)->twice()->andReturn(true)
            ->getMock();

        /** @var Mockery\MockInterface&Capsule */
        $database = Mockery::mock(Capsule::class)
            ->shouldReceive('getConnection')
            ->with(null)
            ->andReturn(Mockery::mock(Connection::class))
            ->getMock();

        $analyser = new Migrator($installed, $available, $database);

        // Run command
        $analyser->validateRollbackMigration(StubAnalyserRollbackMigrationD::class);
        $this->assertTrue($analyser->canRollbackMigration(StubAnalyserRollbackMigrationD::class));
    }

    /**
     * @depends testGetMigrationsForRollback
     * @depends testGetMigrationsForReset
     */
    public function testValidateRollbackMigrationForNotInstalledException(): void
    {
        // Set mock & analyser
        /** @var Mockery\MockInterface&MigrationRepositoryInterface */
        $installed = Mockery::mock(MigrationRepositoryInterface::class)
            ->shouldReceive('has')->with(StubAnalyserRollbackMigrationD::class)->twice()->andReturn(false)
            ->getMock();

        /** @var Mockery\MockInterface&MigrationLocatorInterface */
        $available = Mockery::mock(MigrationLocatorInterface::class);

        /** @var Mockery\MockInterface&Capsule */
        $database = Mockery::mock(Capsule::class)
            ->shouldReceive('getConnection')
            ->with(null)
            ->andReturn(Mockery::mock(Connection::class))
            ->getMock();

        $analyser = new Migrator($installed, $available, $database);

        // Start by testing false
        $this->assertFalse($analyser->canRollbackMigration(StubAnalyserRollbackMigrationD::class));

        // Set exception expectation
        $this->expectException(MigrationRollbackException::class);
        $this->expectExceptionMessage('Migration is not installed : ' . StubAnalyserRollbackMigrationD::class);

        // Run command
        $analyser->validateRollbackMigration(StubAnalyserRollbackMigrationD::class);
    }

    /**
     * @depends testGetMigrationsForRollback
     * @depends testGetMigrationsForReset
     */
    public function testValidateRollbackMigrationForStaleException(): void
    {
        // Set mock & analyser
        /** @var Mockery\MockInterface&MigrationRepositoryInterface */
        $installed = Mockery::mock(MigrationRepositoryInterface::class)
            ->shouldReceive('list')->twice()->andReturn([
                StubAnalyserRollbackMigrationA::class,
                StubAnalyserRollbackMigrationB::class,
                StubAnalyserRollbackMigrationC::class,
                StubAnalyserRollbackMigrationD::class,
            ])
            ->shouldReceive('has')->with(StubAnalyserRollbackMigrationD::class)->twice()->andReturn(true)
            ->getMock();

        /** @var Mockery\MockInterface&MigrationLocatorInterface */
        $available = Mockery::mock(MigrationLocatorInterface::class)
            ->shouldReceive('list')->twice()->andReturn([
                StubAnalyserRollbackMigrationC::class,
                StubAnalyserRollbackMigrationD::class,
            ])
            ->getMock();

        /** @var Mockery\MockInterface&Capsule */
        $database = Mockery::mock(Capsule::class)
            ->shouldReceive('getConnection')
            ->with(null)
            ->andReturn(Mockery::mock(Connection::class))
            ->getMock();

        $analyser = new Migrator($installed, $available, $database);

        // Start by testing false
        $this->assertFalse($analyser->canRollbackMigration(StubAnalyserRollbackMigrationD::class));

        // Set exception expectation
        $this->expectException(MigrationRollbackException::class);
        $this->expectExceptionMessage('Stale migration detected : ' . StubAnalyserRollbackMigrationA::class . ', ' . StubAnalyserRollbackMigrationB::class);

        // Run command
        $analyser->validateRollbackMigration(StubAnalyserRollbackMigrationD::class);
    }

    /**
     * @depends testValidateRollbackMigration
     */
    public function testValidateRollbackMigrationForDependenciesNotMet(): void
    {
        // Set mock & analyser
        /** @var Mockery\MockInterface&MigrationRepositoryInterface */
        $installed = Mockery::mock(MigrationRepositoryInterface::class)
            ->shouldReceive('list')->times(4)->andReturn([
                StubAnalyserRollbackMigrationA::class,
                StubAnalyserRollbackMigrationB::class,
                StubAnalyserRollbackMigrationC::class,
                StubAnalyserRollbackMigrationD::class,
            ])
            ->shouldReceive('has')->with(StubAnalyserRollbackMigrationC::class)->twice()->andReturn(true)
            ->getMock();

        /** @var Mockery\MockInterface&MigrationLocatorInterface */
        $available = Mockery::mock(MigrationLocatorInterface::class)
            ->shouldReceive('list')->andReturn([
                StubAnalyserRollbackMigrationA::class,
                StubAnalyserRollbackMigrationB::class,
                StubAnalyserRollbackMigrationC::class,
                StubAnalyserRollbackMigrationD::class,
            ])
            ->shouldReceive('has')->with(StubAnalyserRollbackMigrationC::class)->twice()->andReturn(true)
            ->getMock();

        /** @var Mockery\MockInterface&Capsule */
        $database = Mockery::mock(Capsule::class)
            ->shouldReceive('getConnection')
            ->with(null)
            ->andReturn(Mockery::mock(Connection::class))
            ->getMock();

        $analyser = new Migrator($installed, $available, $database);

        // Run command
        $this->assertFalse($analyser->canRollbackMigration(StubAnalyserRollbackMigrationC::class));

        // Set exception expectation
        $this->expectException(MigrationRollbackException::class);
        $this->expectExceptionMessage(StubAnalyserRollbackMigrationC::class . ' cannot be rolled back since ' . StubAnalyserRollbackMigrationB::class . ' depends on it.');

        // Run command
        $analyser->validateRollbackMigration(StubAnalyserRollbackMigrationC::class);
    }

    /**
     * @depends testValidateRollbackMigrationForDependenciesNotMet
     *
     * N.B.: That that it fails because "B" depends on "C" which is still not installed or available.
     * This is a very edge case, as "B" shouldn't be installed if "C" is not available.
     * "A" is used as intermediate, to simulate error in second level dependencies.
     */
    public function testValidateRollbackMigrationForDependenciesDoesntExist(): void
    {
        // Set mock & analyser
        /** @var Mockery\MockInterface&MigrationRepositoryInterface */
        $installed = Mockery::mock(MigrationRepositoryInterface::class)
            ->shouldReceive('list')->times(4)->andReturn([
                StubAnalyserRollbackMigrationA::class,
                StubAnalyserRollbackMigrationB::class,
            ])
            ->shouldReceive('has')->with(StubAnalyserRollbackMigrationA::class)->twice()->andReturn(true)
            ->getMock();

        /** @var Mockery\MockInterface&MigrationLocatorInterface */
        $available = Mockery::mock(MigrationLocatorInterface::class)
            ->shouldReceive('list')->andReturn([
                StubAnalyserRollbackMigrationA::class,
                StubAnalyserRollbackMigrationB::class,
            ])
            ->shouldReceive('has')->with(StubAnalyserRollbackMigrationC::class)->twice()->andReturn(false)
            ->getMock();

        /** @var Mockery\MockInterface&Capsule */
        $database = Mockery::mock(Capsule::class)
            ->shouldReceive('getConnection')
            ->with(null)
            ->andReturn(Mockery::mock(Connection::class))
            ->getMock();

        $analyser = new Migrator($installed, $available, $database);

        // Run command
        $this->assertFalse($analyser->canRollbackMigration(StubAnalyserRollbackMigrationA::class));

        // Set exception expectation
        $this->expectException(MigrationRollbackException::class);
        $this->expectExceptionMessage(StubAnalyserRollbackMigrationB::class . ' depends on ' . StubAnalyserRollbackMigrationC::class . ", but it's not available.");

        // Run command
        $analyser->validateRollbackMigration(StubAnalyserRollbackMigrationA::class);
    }

    /**
     * @depends testValidateRollbackMigrationForDependenciesNotMet
     *
     * N.B.: Same as previous test, but without "A" as the intermediate.
     * Should also fails, as "C" is still not installed or there.
     */
    public function testValidateRollbackMigrationForDependenciesDoesntExistWithDirectMigration(): void
    {
        // Set mock & analyser
        /** @var Mockery\MockInterface&MigrationRepositoryInterface */
        $installed = Mockery::mock(MigrationRepositoryInterface::class)
            ->shouldReceive('list')->times(4)->andReturn([
                StubAnalyserRollbackMigrationB::class,
            ])
            ->shouldReceive('has')->with(StubAnalyserRollbackMigrationB::class)->twice()->andReturn(true)
            ->getMock();

        /** @var Mockery\MockInterface&MigrationLocatorInterface */
        $available = Mockery::mock(MigrationLocatorInterface::class)
            ->shouldReceive('list')->twice()->andReturn([
                StubAnalyserRollbackMigrationB::class,
            ])
            ->shouldReceive('has')->with(StubAnalyserRollbackMigrationC::class)->twice()->andReturn(false)
            ->getMock();

        /** @var Mockery\MockInterface&Capsule */
        $database = Mockery::mock(Capsule::class)
            ->shouldReceive('getConnection')
            ->with(null)
            ->andReturn(Mockery::mock(Connection::class))
            ->getMock();

        $analyser = new Migrator($installed, $available, $database);

        // Run command
        $this->assertFalse($analyser->canRollbackMigration(StubAnalyserRollbackMigrationB::class));

        // Set exception expectation
        $this->expectException(MigrationRollbackException::class);
        $this->expectExceptionMessage(StubAnalyserRollbackMigrationB::class . ' depends on ' . StubAnalyserRollbackMigrationC::class . ", but it's not available.");

        // Run command
        $analyser->validateRollbackMigration(StubAnalyserRollbackMigrationB::class);
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
