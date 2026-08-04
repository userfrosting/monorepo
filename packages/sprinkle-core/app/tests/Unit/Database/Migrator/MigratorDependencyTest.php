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
use UserFrosting\Sprinkle\Core\Exceptions\MigrationDependencyNotMetException;

/**
 * Sub test for Migrator.
 * Tests dependencies management related methods.
 */
class MigratorDependencyTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected function getMigrator(): Migrator
    {
        /** @var Mockery\MockInterface&MigrationRepositoryInterface */
        $installed = Mockery::mock(MigrationRepositoryInterface::class)
            ->shouldReceive('list')->andReturn([
                StubAnalyserMigrationA::class,
                StubAnalyserMigrationD::class, // @phpstan-ignore-line - D doesn't exist, which is the point
            ])
            ->getMock();

        /** @var Mockery\MockInterface&MigrationLocatorInterface */
        $available = Mockery::mock(MigrationLocatorInterface::class)
            ->shouldReceive('list')->andReturn([
                StubAnalyserMigrationA::class,
                StubAnalyserMigrationB::class,
                StubAnalyserMigrationC::class,
                StubAnalyserMigrationE::class,
            ])
            ->shouldReceive('has')->with(StubAnalyserMigrationA::class)->andReturn(true)
            ->shouldReceive('has')->with(StubAnalyserMigrationB::class)->andReturn(true)
            ->shouldReceive('has')->with(StubAnalyserMigrationC::class)->andReturn(true)
            ->shouldReceive('has')->with(StubAnalyserMigrationD::class)->andReturn(false) // @phpstan-ignore-line
            ->shouldReceive('has')->with(StubAnalyserMigrationE::class)->andReturn(true)
            ->shouldReceive('get')->with(StubAnalyserMigrationA::class)->andReturn(new StubAnalyserMigrationA())
            ->shouldReceive('get')->with(StubAnalyserMigrationB::class)->andReturn(new StubAnalyserMigrationB())
            ->shouldReceive('get')->with(StubAnalyserMigrationC::class)->andReturn(new StubAnalyserMigrationC())
            ->shouldReceive('get')->with(StubAnalyserMigrationE::class)->andReturn(new StubAnalyserMigrationE())
            ->getMock();

        /** @var Mockery\MockInterface&Capsule */
        $database = Mockery::mock(Capsule::class)
            ->shouldReceive('getConnection')
            ->with(null)
            ->andReturn(Mockery::mock(Connection::class))
            ->getMock();

        $analyser = new Migrator($installed, $available, $database);

        return $analyser;
    }

    public function testGetInstalled(): void
    {
        $analyser = $this->getMigrator();
        $this->assertSame([
            StubAnalyserMigrationA::class,
            StubAnalyserMigrationD::class, // @phpstan-ignore-line
        ], $analyser->getInstalled());
    }

    public function testGetAvailable(): void
    {
        $analyser = $this->getMigrator();
        $this->assertSame([
            StubAnalyserMigrationA::class,
            StubAnalyserMigrationB::class,
            StubAnalyserMigrationC::class,
            StubAnalyserMigrationE::class,
        ], $analyser->getAvailable());
    }

    public function testGetPending(): void
    {
        $analyser = $this->getMigrator();
        $this->assertSame([
            StubAnalyserMigrationC::class, // C is before B because B depend on C
            StubAnalyserMigrationB::class,
            StubAnalyserMigrationE::class,
        ], $analyser->getPending());
    }

    public function testGetStale(): void
    {
        $analyser = $this->getMigrator();
        $this->assertSame([
            StubAnalyserMigrationD::class, // @phpstan-ignore-line
        ], $analyser->getStale());
    }

    /**
     * @depends testGetPending
     */
    public function testGetPendingThirdStageDependency(): void
    {
        /** @var Mockery\MockInterface&MigrationRepositoryInterface */
        $installed = Mockery::mock(MigrationRepositoryInterface::class)
            ->shouldReceive('list')->andReturn([
                StubAnalyserMigrationA::class,
                StubAnalyserMigrationD::class, // @phpstan-ignore-line
            ])
            ->getMock();

        /** @var Mockery\MockInterface&MigrationLocatorInterface */
        $available = Mockery::mock(MigrationLocatorInterface::class)
            ->shouldReceive('list')->andReturn([
                StubAnalyserMigrationH::class, // Place H first
                StubAnalyserMigrationA::class,
                StubAnalyserMigrationB::class,
                StubAnalyserMigrationC::class,
                StubAnalyserMigrationE::class,
            ])
            ->shouldReceive('has')->with(StubAnalyserMigrationA::class)->andReturn(true)
            ->shouldReceive('has')->with(StubAnalyserMigrationB::class)->andReturn(true)
            ->shouldReceive('has')->with(StubAnalyserMigrationC::class)->andReturn(true)
            ->shouldReceive('has')->with(StubAnalyserMigrationD::class)->andReturn(false) // @phpstan-ignore-line
            ->shouldReceive('has')->with(StubAnalyserMigrationE::class)->andReturn(true)
            ->shouldReceive('get')->with(StubAnalyserMigrationA::class)->andReturn(new StubAnalyserMigrationA())
            ->shouldReceive('get')->with(StubAnalyserMigrationB::class)->andReturn(new StubAnalyserMigrationB())
            ->shouldReceive('get')->with(StubAnalyserMigrationC::class)->andReturn(new StubAnalyserMigrationC())
            ->shouldReceive('get')->with(StubAnalyserMigrationE::class)->andReturn(new StubAnalyserMigrationE())
            ->shouldReceive('get')->with(StubAnalyserMigrationH::class)->andReturn(new StubAnalyserMigrationH())
            ->getMock();

        /** @var Mockery\MockInterface&Capsule */
        $database = Mockery::mock(Capsule::class)
            ->shouldReceive('getConnection')
            ->with(null)
            ->andReturn(Mockery::mock(Connection::class))
            ->getMock();

        $analyser = new Migrator($installed, $available, $database);

        $this->assertSame([
            StubAnalyserMigrationC::class, // C is before B because B depend on C
            StubAnalyserMigrationB::class,
            StubAnalyserMigrationH::class, // H should be first, but it require B, that require C.
            StubAnalyserMigrationE::class,
        ], $analyser->getPending());
    }

    /**
     * @depends testGetPending
     */
    public function testGetPendingWithNonAvailable(): void
    {
        /** @var Mockery\MockInterface&MigrationRepositoryInterface */
        $installed = Mockery::mock(MigrationRepositoryInterface::class)
            ->shouldReceive('list')->andReturn([])
            ->getMock();

        /** @var Mockery\MockInterface&MigrationLocatorInterface */
        $available = Mockery::mock(MigrationLocatorInterface::class)
            ->shouldReceive('list')->andReturn([
                StubAnalyserMigrationG::class,
            ])
            ->shouldReceive('has')->with(StubAnalyserMigrationF::class)->andReturn(false) // @phpstan-ignore-line
            ->shouldReceive('get')->with(StubAnalyserMigrationG::class)->andReturn(new StubAnalyserMigrationG())
            ->getMock();

        /** @var Mockery\MockInterface&Capsule */
        $database = Mockery::mock(Capsule::class)
            ->shouldReceive('getConnection')
            ->with(null)
            ->andReturn(Mockery::mock(Connection::class))
            ->getMock();

        $analyser = new Migrator($installed, $available, $database);

        $this->expectException(MigrationDependencyNotMetException::class);
        $this->expectExceptionMessage(StubAnalyserMigrationG::class . ' depends on ' . StubAnalyserMigrationF::class . ", but it's not available."); // @phpstan-ignore-line

        $analyser->getPending();
    }
}

class StubAnalyserMigrationA implements MigrationInterface
{
    public function up(): void
    {
    }

    public function down(): void
    {
    }
}

class StubAnalyserMigrationB extends StubAnalyserMigrationA
{
    /** @var class-string[] */
    public static $dependencies = [
        StubAnalyserMigrationC::class,
    ];
}

class StubAnalyserMigrationC extends StubAnalyserMigrationA
{
}

class StubAnalyserMigrationE extends StubAnalyserMigrationA
{
    /** @var class-string[] */
    public static $dependencies = [
        // D doesn't exist on purpose, but it IS installed
        StubAnalyserMigrationD::class, // @phpstan-ignore-line
    ];
}

class StubAnalyserMigrationG extends StubAnalyserMigrationA
{
    /** @var class-string[] */
    public static $dependencies = [
        // F doesn't exist on purpose, and it's NOT installed
        StubAnalyserMigrationF::class, // @phpstan-ignore-line
    ];
}

class StubAnalyserMigrationH extends StubAnalyserMigrationA
{
    /** @var class-string[] */
    public static $dependencies = [
        StubAnalyserMigrationB::class,
    ];
}
