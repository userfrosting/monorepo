<?php

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\Controller;

use Illuminate\Database\Capsule\Manager as Capsule;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PDOException;
use Psr\EventDispatcher\EventDispatcherInterface;
use UserFrosting\Event\EventDispatcher;
use UserFrosting\Sprinkle\Account\Bakery\CreateAdminUser;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Event\UserCreatedEvent;
use UserFrosting\Sprinkle\Account\Exceptions\UsernameNotUniqueException;
use UserFrosting\Sprinkle\Account\Log\UserActivityLogger;
use UserFrosting\Sprinkle\Account\Log\UserActivityLoggerInterface;
use UserFrosting\Sprinkle\Account\Tests\AccountTestCase;
use UserFrosting\Sprinkle\Account\Validators\UserValidation;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationRepositoryInterface;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;
use UserFrosting\Testing\BakeryTester;

class CreateAdminUserTest extends AccountTestCase
{
    use RefreshDatabase;
    use MockeryPHPUnitIntegration;

    /**
     * Setup test database for controller tests
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
    }

    /**
     * WARNING : This test doesn't work on Windows.
     * @see https://symfony.com/doc/current/components/console/helpers/questionhelper.html#testing-a-command-that-expects-input
     * "On Windows systems Symfony uses a special binary to implement hidden
     * questions. This means that those questions don't use the default Input
     * console object and therefore you can't test them on Windows."
     *
     * @group windows-skip
     */
    public function testCommandWithUserInput(): void
    {
        // Asset initial user count.
        $this->assertEquals(0, User::count());

        // Mock eventDispatcher to assert it's being called properly.
        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class)
            ->shouldReceive('dispatch')->with(Mockery::type(UserCreatedEvent::class))->once()->andReturnUsing(function ($arg1) {
                return $arg1;
            })
            ->getMock();
        $this->ci->set(EventDispatcherInterface::class, $eventDispatcher);

        // Mock userActivityLogger to assert it's being called properly.
        $userActivityLogger = Mockery::mock(UserActivityLogger::class)
            ->shouldReceive('info')->once()
            ->getMock();
        $this->ci->set(UserActivityLoggerInterface::class, $userActivityLogger);

        /** @var CreateAdminUser */
        $command = $this->ci->get(CreateAdminUser::class);
        $result = BakeryTester::runCommand($command, userInput: [
            'test', // username
            'password123', // password
            'password123', // confirmation
            'test@test.com', // email
            'Test', // First name
            'ing', // Last name
        ]);
        $this->assertSame(0, $result->getStatusCode());

        // Assert that the user was created.
        $this->assertSame(1, User::count());
    }

    public function testCommandWithInput(): void
    {
        // Asset initial user count.
        $this->assertEquals(0, User::count());

        /** @var CreateAdminUser */
        $command = $this->ci->get(CreateAdminUser::class);
        $result = BakeryTester::runCommand($command, input: [
            '--username'  => 'test',
            '--password'  => 'password123',
            '--email'     => 'test@test.com',
            '--firstName' => 'Test',
            '--lastName'  => 'ing',
        ]);
        $this->assertSame(0, $result->getStatusCode());

        // Assert that the user was created.
        $this->assertSame(1, User::count());
    }

    /**
     * WARNING : This test doesn't work on Windows.
     * @see https://symfony.com/doc/current/components/console/helpers/questionhelper.html#testing-a-command-that-expects-input
     * "On Windows systems Symfony uses a special binary to implement hidden
     * questions. This means that those questions don't use the default Input
     * console object and therefore you can't test them on Windows."
     *
     * @group windows-skip
     */
    public function testCommandWithUserInputRepeatedForEmpty(): void
    {
        // Asset initial user count.
        $this->assertEquals(0, User::count());

        /** @var CreateAdminUser */
        $command = $this->ci->get(CreateAdminUser::class);
        $result = BakeryTester::runCommand($command, userInput: [
            '', // username
            'test', // username repeated
            'password123', // password
            'password123', // confirmation
            'test@test.com', // email
            'Test', // First name
            'ing', // Last name
        ]);
        $this->assertSame(0, $result->getStatusCode());

        // Assert that the user was created.
        $this->assertSame(1, User::count());
    }

    public function testForFailedDbConnection(): void
    {
        // Mock Connection
        $connection = Mockery::mock(Capsule::class)
            ->shouldReceive('getDatabaseManager')->once()->andThrow(new PDOException())
            ->getMock();
        $this->ci->set(Capsule::class, $connection);

        /** @var CreateAdminUser */
        $command = $this->ci->get(CreateAdminUser::class);
        $result = BakeryTester::runCommand($command);
        $this->assertSame(1, $result->getStatusCode());
        $this->assertStringContainsString('Could not connect to the database', $result->getDisplay());
    }

    public function testForNonExistingRepository(): void
    {
        // Mock MigrationRepositoryInterface
        $repository = Mockery::mock(MigrationRepositoryInterface::class)
            ->shouldReceive('exists')->once()->andReturn(false)
            ->getMock();
        $this->ci->set(MigrationRepositoryInterface::class, $repository);

        /** @var CreateAdminUser */
        $command = $this->ci->get(CreateAdminUser::class);
        $result = BakeryTester::runCommand($command);
        $this->assertSame(1, $result->getStatusCode());
        $this->assertStringContainsString("Migrations doesn't appear to have been run!", $result->getDisplay());
    }

    public function testForMissingDependencies(): void
    {
        // Mock MigrationRepositoryInterface
        $repository = Mockery::mock(MigrationRepositoryInterface::class)
            ->shouldReceive('exists')->once()->andReturn(true)
            ->shouldReceive('has')->once()->andReturn(false)
            ->getMock();
        $this->ci->set(MigrationRepositoryInterface::class, $repository);

        /** @var CreateAdminUser */
        $command = $this->ci->get(CreateAdminUser::class);
        $result = BakeryTester::runCommand($command);
        $this->assertSame(1, $result->getStatusCode());
    }

    public function testForExistingUser(): void
    {
        User::factory()->create();
        $this->assertEquals(1, User::count());

        /** @var CreateAdminUser */
        $command = $this->ci->get(CreateAdminUser::class);
        $result = BakeryTester::runCommand($command);
        $this->assertSame(0, $result->getStatusCode());
        $this->assertStringContainsString("Table 'users' is not empty.", $result->getDisplay());
    }

    /**
     * WARNING : This test doesn't work on Windows.
     * @see https://symfony.com/doc/current/components/console/helpers/questionhelper.html#testing-a-command-that-expects-input
     * "On Windows systems Symfony uses a special binary to implement hidden
     * questions. This means that those questions don't use the default Input
     * console object and therefore you can't test them on Windows."
     *
     * @group windows-skip
     */
    public function testValidationRetriesAfterInvalidEmail(): void
    {
        /** @var CreateAdminUser */
        $command = $this->ci->get(CreateAdminUser::class);
        $result = BakeryTester::runCommand($command, userInput: [
            'test', // username
            'password123', // password
            'password123', // confirmation
            'test', // email (invalid)
            'Test', // First name
            'ing', // Last name
            'test', // username retry
            'password123', // password retry
            'password123', // confirmation retry
            'test@test.com', // email retry
            'Test', // First name retry
            'ing', // Last name retry
        ]);
        $this->assertSame(0, $result->getStatusCode());
        $this->assertStringContainsString('Invalid email address.', $result->getDisplay());
    }

    /**
     * WARNING : This test doesn't work on Windows.
     * @see https://symfony.com/doc/current/components/console/helpers/questionhelper.html#testing-a-command-that-expects-input
     * "On Windows systems Symfony uses a special binary to implement hidden
     * questions. This means that those questions don't use the default Input
     * console object and therefore you can't test them on Windows."
     *
     * @group windows-skip
     */
    public function testForFailedUserValidation(): void
    {
        // Mock UserValidation
        $validator = Mockery::mock(UserValidation::class)
            ->shouldReceive('validate')->once()->andThrow(new UsernameNotUniqueException())
            ->getMock();
        $this->ci->set(UserValidation::class, $validator);

        /** @var CreateAdminUser */
        $command = $this->ci->get(CreateAdminUser::class);
        $result = BakeryTester::runCommand($command, userInput: [
            'test', // username
            'password123', // password
            'password123', // confirmation
            'test@test.com', // email
            'Test', // First name
            'ing', // Last name
        ]);
        $this->assertSame(1, $result->getStatusCode());
        $this->assertStringContainsString('Invalid username', $result->getDisplay());
    }
}
