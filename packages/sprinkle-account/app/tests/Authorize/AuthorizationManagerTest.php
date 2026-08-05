<?php

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\Authorize;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use UserFrosting\Config\Config;
use UserFrosting\Session\Session;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager;
use UserFrosting\Sprinkle\Account\Authorize\AuthorizationManagerInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Log\AuthLoggerInterface;
use UserFrosting\Sprinkle\Account\Tests\AccountTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

/**
 * Integration tests for the built-in Sprunje classes.
 */
class AuthorizationManagerTest extends AccountTestCase
{
    use MockeryPHPUnitIntegration;
    use RefreshDatabase;

    /** @var AuthLoggerInterface|\Mockery\MockInterface */
    protected AuthLoggerInterface $logger;

    /**
     * Setup the test database.
     */
    public function setUp(): void
    {
        parent::setUp();

        // Setup test database
        $this->refreshDatabase();

        // We'll test using the `debug.auth` on and a mock AuthLoggerInterface, to not
        // get our dirty test into the real log
        /** @var Config */
        $config = $this->getService(Config::class);
        $config->set('debug.auth', true);
        $config->set('reserved_user_ids.master', 1);

        $this->logger = Mockery::mock(AuthLoggerInterface::class);
        $this->getContainer()->set(AuthLoggerInterface::class, $this->logger);
    }

    public function testCheckAccessWithNullUser(): void
    {
        /** @var AuthorizationManager */
        $manager = $this->getService(AuthorizationManagerInterface::class);
        $this->logger->shouldReceive('debug')->once()->with('Authorization check requested at: ', Mockery::any());
        $this->logger->shouldReceive('debug')->once()->with('No user defined. Access denied.', []);
        $this->assertFalse($manager->checkAccess(null, 'foo'));
    }

    public function testCheckAccessWithNormalUser(): void
    {
        /** @var User */
        $user = User::factory([
            'id' => 11,
        ])->make();

        // Setup AuthLoggerInterface expectations
        $this->logger->shouldReceive('debug')->once()->with('No permissions found. Access denied.', []);
        $this->logger->shouldReceive('debug')->times(2);

        /** @var AuthorizationManager */
        $manager = $this->getService(AuthorizationManagerInterface::class);
        $this->assertFalse($manager->checkAccess($user, 'blah'));
    }

    public function testAuthenticatorAlias(): void
    {
        /** @var User */
        $user = User::factory([
            'id' => 11,
        ])->create();

        // Setup AuthLoggerInterface expectations
        $this->logger->shouldReceive('debug')->once()->with('No permissions found. Access denied.', []);
        $this->logger->shouldReceive('debug')->times(2);

        /** @var Authenticator */
        $authenticator = $this->getService(Authenticator::class);

        /** @var Session */
        $session = $this->getService(Session::class);

        // Start session
        $session->start();

        // Login the test user
        $authenticator->login($user, false);

        $this->assertFalse($authenticator->checkAccess('blah'));

        // Must logout to avoid test issue
        $authenticator->logout(true);
    }

    public function testCheckAccessWithMasterUser(): void
    {
        /** @var User */
        $user = User::factory([
            'id' => 1,
        ])->make();

        // Setup AuthLoggerInterface expectations
        $this->logger->shouldReceive('debug')->once()->with('User is the master (root) user. Access granted.', []);
        $this->logger->shouldReceive('debug')->times(2);

        /** @var AuthorizationManager */
        $manager = $this->getService(AuthorizationManagerInterface::class);
        $this->assertTrue($manager->checkAccess($user, 'foo'));
    }

    public function testCheckAccessWithNormalUserWithPermission(): void
    {
        /** @var Permission */
        $permission = Permission::factory([
            'slug'       => 'foo',
            'conditions' => 'always()',
        ])->create();

        /** @var Role */
        $role = Role::factory()->create();

        /** @var User */
        $user = User::factory([
            'id' => 11,
        ])->create();

        // Set relationships
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        // Setup AuthLoggerInterface expectations
        $this->logger->shouldReceive('debug')->once()->with("Evaluating callback 'always'...");
        $this->logger->shouldReceive('debug')->once()->with("User passed conditions 'always()'. Access granted.", []);
        $this->logger->shouldReceive('debug')->times(6);

        /** @var AuthorizationManager */
        $manager = $this->getService(AuthorizationManagerInterface::class);
        $this->assertTrue($manager->checkAccess($user, 'foo'));
    }

    public function testCheckAccessWithNormalUserWithFailedPermission(): void
    {
        /** @var Permission */
        $permission = Permission::factory([
            'slug'       => 'foo',
            'conditions' => 'never()',
        ])->create();

        /** @var Role */
        $role = Role::factory()->create();

        /** @var User */
        $user = User::factory([
            'id' => 11,
        ])->create();

        // Set relationships
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        // Setup AuthLoggerInterface expectations
        $this->logger->shouldReceive('debug')->once()->with('User failed to pass any of the matched permissions. Access denied.', []);
        $this->logger->shouldReceive('debug')->times(7);

        /** @var AuthorizationManager */
        $manager = $this->getService(AuthorizationManagerInterface::class);
        $this->assertFalse($manager->checkAccess($user, 'foo'));
    }
}
