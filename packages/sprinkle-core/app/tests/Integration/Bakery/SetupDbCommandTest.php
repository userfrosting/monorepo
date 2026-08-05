<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Bakery;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder;
use Illuminate\Database\Schema\SQLiteBuilder;
use Illuminate\Database\SQLiteConnection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PDOException;
use phpmock\mockery\PHPMockery;
use ReflectionClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Core\Bakery\Helper\DbParamTester;
use UserFrosting\Sprinkle\Core\Bakery\SetupDbCommand;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase;
use UserFrosting\Support\DotenvEditor\DotenvEditor;
use UserFrosting\Testing\BakeryTester;
use UserFrosting\UniformResourceLocator\ResourceLocator;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;
use UserFrosting\UniformResourceLocator\ResourceStream;

/**
 * Test for SetupDbCommand (setup:db)
 */
class SetupDbCommandTest extends CoreTestCase
{
    use MockeryPHPUnitIntegration;

    protected string $dbFile = __DIR__ . '/data/database/database.sql';

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        // Use test location
        $dbStream = new ResourceStream('database', shared: true);
        $envStream = new ResourceStream('sprinkles', path: 'env', shared: true);
        $locator = new ResourceLocator(__DIR__ . '/data');
        $locator->addStream($dbStream);
        $locator->addStream($envStream);
        $this->getContainer()->set(ResourceLocatorInterface::class, $locator);

        // Delete existing env file
        @unlink($this->dbFile);
    }

    public function testEnvNotFound(): void
    {
        // Force locator return with Mockery
        $locator = Mockery::mock(ResourceLocatorInterface::class);
        $locator->shouldReceive('getResource')->andReturn(null); // For databaseDrivers method
        $locator->shouldReceive('findResource')->andReturn(null);
        $this->getContainer()->set(ResourceLocatorInterface::class, $locator);

        // Run command and assert it fails
        /** @var SetupDbCommand */
        $command = $this->getService(SetupDbCommand::class);
        $result = BakeryTester::runCommand($command);
        $this->assertSame(1, $result->getStatusCode());
        $this->assertStringContainsString('Could not find .env file', $result->getDisplay());
    }

    public function testCommandWithNoInput(): void
    {
        // Run command and assert result
        /** @var SetupDbCommand */
        $command = $this->getService(SetupDbCommand::class);
        $result = BakeryTester::runCommand($command, input: ['--force' => true]);

        // Assertions
        $this->assertSame(1, $result->getStatusCode());
        $this->assertStringContainsString('Aborted.', $result->getDisplay());
    }

    public function testCommandWithExistingDb(): void
    {
        // Mock Capsule to manipulate the result
        $tester = Mockery::mock(DbParamTester::class)
            ->shouldReceive('test')->once()->andReturn(true)
            ->getMock();
        $this->getContainer()->set(DbParamTester::class, $tester);

        // Run command and assert result
        /** @var SetupDbCommand */
        $command = $this->getService(SetupDbCommand::class);
        $result = BakeryTester::runCommand($command);

        // Assertions
        $this->assertSame(0, $result->getStatusCode());
        $this->assertStringContainsString('Database already setup.', $result->getDisplay());
    }

    /**
     * Warning : This test needs to be run BEFORE `testCommand`
     * @see https://github.com/php-mock/php-mock-mockery#restrictions
     */
    public function testCommandWithFailedTouch(): void
    {
        // Make sure database file doesn't exist yet
        $this->assertFileDoesNotExist($this->dbFile);

        // Mock capsule to manipulate the result
        // This simulate the entered config IS valid
        $tester = Mockery::mock(DbParamTester::class)
            ->shouldReceive('test')->times(1)->andThrow(new PDOException())
            ->getMock();
        $this->getContainer()->set(DbParamTester::class, $tester);

        // Mock touch function to fail
        $reflection_class = new ReflectionClass(SetupDbCommand::class);
        $namespace = $reflection_class->getNamespaceName();
        PHPMockery::mock($namespace, 'touch')->andReturn(false);

        // Run command and assert result
        /** @var SetupDbCommand */
        $command = $this->getService(SetupDbCommand::class);
        $result = BakeryTester::runCommand($command, userInput: [
            '3',
            $this->dbFile,
        ]);

        // Assertions
        $this->assertSame(1, $result->getStatusCode());
        $this->assertStringContainsString('Unable to create database file', $result->getDisplay());
    }

    public function testCommand(): void
    {
        /** @var Capsule */
        $capsule = $this->getService(Capsule::class);
        $connection = $capsule->getConnection();
        $this->assertNotSame($this->dbFile, $connection->getDatabaseName());

        // Make sure database file doesn't exist yet
        $this->assertFileDoesNotExist($this->dbFile);

        // Mock capsule to manipulate the result
        // This simulate the entered config IS valid
        $tester = Mockery::mock(DbParamTester::class)
            ->shouldReceive('test')->times(1)->andThrow(new PDOException())
            ->shouldReceive('test')->times(1)->andReturn(true)
            ->getMock();
        $this->getContainer()->set(DbParamTester::class, $tester);

        // Run command and assert result
        /** @var SetupDbCommand */
        $command = $this->getService(SetupDbCommand::class);
        $result = BakeryTester::runCommand($command, userInput: [
            '3',
            $this->dbFile,
        ]);

        // Assertions
        $this->assertSame(0, $result->getStatusCode());
        $this->assertStringContainsString('Database connection successful', $result->getDisplay());
        $this->assertStringContainsString('Database config successfully saved', $result->getDisplay());

        // Assert env and config is correctly changed
        $dotenvEditor = new DotenvEditor();
        $dotenvEditor->load(__DIR__ . '/data/env/.env');
        $this->assertSame('sqlite', $dotenvEditor->getValue('DB_CONNECTION'));
        $this->assertSame($this->dbFile, $dotenvEditor->getValue('DB_NAME'));

        // Assert config is ok, env was update by the command
        /** @var Config */
        $config = $this->getService(Config::class);
        $this->assertSame('sqlite', $config->get('db.default'));
        $this->assertSame($this->dbFile, $config->get('db.connections.sqlite.database'));

        /** @var Capsule */
        $capsule = $this->getService(Capsule::class);
        $connection = $capsule->getConnection();
        $this->assertSame('sqlite', $connection->getDriverName());
        $this->assertSame($this->dbFile, $connection->getDatabaseName());

        // Assert services were updated
        $this->assertInstanceOf(SQLiteConnection::class, $this->getService(Connection::class));
        $this->assertInstanceOf(SQLiteBuilder::class, $this->getService(Builder::class));

        // Delete database file
        unlink($this->dbFile);
        $this->assertFileDoesNotExist($this->dbFile);
    }

    /**
     * Simulate when the command define the same database driver, but with new
     * config. This make sure the existing connection is properly purged.
     *
     * @depends testCommand
     */
    public function testCommandForPurge(): void
    {
        // Make a second file
        $dbFile2 = __DIR__ . '/data/database/foo.sql';

        // Make sure database file doesn't exist yet
        $this->assertFileDoesNotExist($this->dbFile);
        $this->assertFileDoesNotExist($dbFile2);

        // Mock capsule to manipulate the result
        // This simulate the entered config IS valid
        $tester = Mockery::mock(DbParamTester::class)
            ->shouldReceive('test')->times(1)->andThrow(new PDOException())
            ->shouldReceive('test')->times(2)->andReturn(true)
            ->getMock();
        $this->getContainer()->set(DbParamTester::class, $tester);

        // Run command and assert result
        /** @var SetupDbCommand */
        $command = $this->getService(SetupDbCommand::class);
        $result = BakeryTester::runCommand($command, userInput: [
            '3',
            $this->dbFile,
        ]);
        $result2 = BakeryTester::runCommand($command, input: ['--force' => true], userInput: [
            '3',
            $dbFile2,
        ]);

        // Assertions, make sure command is correctly executed
        $this->assertSame(0, $result->getStatusCode());
        $this->assertSame(0, $result2->getStatusCode());
        $this->assertStringContainsString('Database config successfully saved', $result2->getDisplay());

        // Assert config is ok, env was update by the command
        /** @var Config */
        $config = $this->getService(Config::class);
        $this->assertSame($dbFile2, $config->get('db.connections.sqlite.database'));

        /** @var Capsule */
        $capsule = $this->getService(Capsule::class);
        $this->assertSame($dbFile2, $capsule->getConnection()->getDatabaseName());

        // Delete database file
        unlink($this->dbFile);
        unlink($dbFile2);
        $this->assertFileDoesNotExist($this->dbFile);
        $this->assertFileDoesNotExist($dbFile2);
    }

    public function testCommandWithOptionsAndVerbose(): void
    {
        // Mock capsule to manipulate the result
        // This simulate the entered config is valid
        $tester = Mockery::mock(DbParamTester::class)
            ->shouldReceive('test')->times(1)->andReturn(true)
            ->getMock();
        $this->getContainer()->set(DbParamTester::class, $tester);

        // Run command and assert result
        /** @var SetupDbCommand */
        $command = $this->getService(SetupDbCommand::class);
        $result = BakeryTester::runCommand($command, verbosity: OutputInterface::VERBOSITY_VERBOSE, input: [
            '--force'       => true,
            '--db_driver'   => 'mysql',
            '--db_name'     => 'database_name',
            '--db_host'     => 'localhost',
            '--db_port'     => '3306',
            '--db_user'     => 'database_user',
            '--db_password' => 'database_password',
        ]);

        // Assertions
        $this->assertSame(0, $result->getStatusCode());
        $this->assertStringContainsString('Save path for database credentials', $result->getDisplay());
        $this->assertStringContainsString('Database connection successful', $result->getDisplay());
        $this->assertStringContainsString('Database config successfully saved', $result->getDisplay());
    }

    public function testCommandForBadNewConfig(): void
    {
        // Mock capsule to manipulate the result
        // This simulate the entered config is valid
        $tester = Mockery::mock(DbParamTester::class)
            ->shouldReceive('test')->times(1)->andThrow(new PDOException())
            ->getMock();
        $this->getContainer()->set(DbParamTester::class, $tester);

        // Run command and assert result
        /** @var SetupDbCommand */
        $command = $this->getService(SetupDbCommand::class);
        $result = BakeryTester::runCommand($command, input: [
            '--force'       => true,
            '--db_driver'   => 'mysql',
            '--db_name'     => 'database_name',
            '--db_host'     => 'localhost',
            '--db_port'     => '3306',
            '--db_user'     => 'database_user',
            '--db_password' => 'database_password',
        ]);

        // Assertions
        $this->assertSame(1, $result->getStatusCode());
        $this->assertStringContainsString('Could not connect to the database', $result->getDisplay());
    }

    public function testCommandWithUserInput(): void
    {
        // Mock capsule to manipulate the result
        // This simulate the entered config is valid
        $tester = Mockery::mock(DbParamTester::class)
            ->shouldReceive('test')->times(1)->andReturn(true)
            ->getMock();
        $this->getContainer()->set(DbParamTester::class, $tester);

        // Run command and assert result
        /** @var SetupDbCommand */
        $command = $this->getService(SetupDbCommand::class);
        $result = BakeryTester::runCommand($command, input: ['--force' => true], userInput: [
            '0',
            'localhost',
            3306,
            'userfrosting',
            'userfrosting',
            'password',
        ]);

        // Assertions
        $this->assertSame(0, $result->getStatusCode());
        $this->assertStringContainsString('Database connection successful', $result->getDisplay());
        $this->assertStringContainsString('Database config successfully saved', $result->getDisplay());
    }

    public function testCommandForBadDriver(): void
    {
        // Run command and assert result
        /** @var SetupDbCommand */
        $command = $this->getService(SetupDbCommand::class);
        $result = BakeryTester::runCommand($command, input: [
            '--force'       => true,
            '--db_driver'   => 'foo',
        ]);

        // Assertions
        $this->assertSame(1, $result->getStatusCode());
        $this->assertStringContainsString('Invalid database driver: foo', $result->getDisplay());
    }
}
