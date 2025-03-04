<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\Controller;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Account\Account;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Event\UserRedirectedAfterLoginEvent;
use UserFrosting\Sprinkle\Account\Tests\AccountTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;
use UserFrosting\Sprinkle\Core\Throttle\Throttler;

/**
 * Tests RegisterAction
 */
class LoginActionTest extends AccountTestCase
{
    use RefreshDatabase;
    use MockeryPHPUnitIntegration;

    protected string $mainSprinkle = LoginActionSprinkle::class;

    /**
     * Setup test database for controller tests
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
    }

    public function testLogin(): void
    {
        /** @var User */
        $user = User::factory([
            'password' => 'test'
        ])->create();
        $user->refresh();

        /** @var Role */
        $role = Role::factory()->create();
        $user->roles()->attach($role);
        $permission = new Permission([
            'slug'       => 'test_permission',
            'name'       => 'Test Permission',
            'conditions' => 'always()',
        ]);
        $permission->save();
        $role->permissions()->attach($permission);
        $role->save();

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/account/login', [
            'user_name' => $user->user_name,
            'password'  => 'test',
        ]);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse($user->toArray(), $response, 'user');
        $this->assertJsonResponse(['test_permission' => 'always()'], $response, 'permissions');
        $this->assertJsonResponse('Welcome back, ' . $user->full_name . '!', $response, 'message');
        $this->assertJsonResponse('/home', $response, 'redirect');
        $this->assertResponseStatus(200, $response);

        // Assert Event Redirect
        $this->assertSame('/home', $response->getHeaderLine('UF-Redirect'));

        // We have to logout the user to avoid problem
        /** @var Authenticator */
        $authenticator = $this->ci->get(Authenticator::class);
        $authenticator->logout();
    }

    public function testLoginWithEmail(): void
    {
        /** @var User */
        $user = User::factory([
            'password' => 'test'
        ])->create();
        $user->refresh();

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/account/login', [
            'user_name' => $user->email,
            'password'  => 'test',
        ]);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse($user->toArray(), $response, 'user');
        $this->assertResponseStatus(200, $response);

        // We have to logout the user to avoid problem
        /** @var Authenticator */
        $authenticator = $this->ci->get(Authenticator::class);
        $authenticator->logout();
    }

    /**
     * N.B.: This should be covered by GuestGuard
     */
    public function testLoginWithLoggedInUser(): void
    {
        // Fake user is logged in
        // Mock Authenticator
        $authenticator = Mockery::mock(Authenticator::class)
            ->shouldReceive('check')->once()->andReturn(true)
            ->getMock();
        $this->ci->set(Authenticator::class, $authenticator);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/account/login', [
            'user_name' => 'foo',
            'password'  => 'test',
        ]);
        $response = $this->handleRequest($request);

        // Assert response status
        $this->assertResponseStatus(400, $response);
    }

    public function testLoginWithFailedValidation(): void
    {
        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/account/login', [
            'user_name' => 'foo',
        ]);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse([
            'title'       => 'Validation error',
            'description' => 'Please specify a value for <strong>Password</strong>.',
            'status'      => 400,
        ], $response);
        $this->assertResponseStatus(400, $response);
    }

    public function testLoginWithThrottler(): void
    {
        // Create fake throttler
        /** @var Throttler */
        $throttler = Mockery::mock(Throttler::class)
            ->shouldReceive('getDelay')->once()->with('sign_in_attempt', ['user_identifier' => 'foo'])->andReturn(90)
            ->getMock();
        $this->ci->set(Throttler::class, $throttler);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/account/login', [
            'user_name'  => 'foo',
            'password'   => 'bar',
            'rememberme' => false,
        ]);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse('Rate Limit Exceeded', $response, 'title');
        $this->assertResponseStatus(429, $response);
    }

    public function testLoginThrottlerCountsFailedLoginsAndFailedCredentials(): void
    {
        // Create fake throttler
        /** @var Throttler */
        $throttler = Mockery::mock(Throttler::class)
            ->shouldReceive('getDelay')->once()->with('sign_in_attempt', ['user_identifier' => 'foo'])->andReturn(0)
            ->shouldReceive('logEvent')->once()->with('sign_in_attempt', ['user_identifier' => 'foo'])
            ->getMock();
        $this->ci->set(Throttler::class, $throttler);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/account/login', [
            'user_name'  => 'foo',
            'password'   => 'bar',
            'rememberme' => false,
        ]);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse([
            'title'       => 'Invalid Credentials',
            'description' => 'User not found or password is invalid.',
            'status'      => 403,
        ], $response);
        $this->assertResponseStatus(403, $response);
    }

    public function testLoginThrottlerDoesNotCountSuccessfulLogins(): void
    {
        /** @var User */
        $user = User::factory([
            'password' => 'test'
        ])->create();
        $user->refresh();

        // Create fake throttler
        /** @var Throttler */
        $throttler = Mockery::mock(Throttler::class)
            ->shouldReceive('getDelay')->once()->with('sign_in_attempt', ['user_identifier' => $user->email])->andReturn(0)
            ->shouldNotReceive('logEvent')
            ->getMock();
        $this->ci->set(Throttler::class, $throttler);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/account/login', [
            'user_name' => $user->email,
            'password'  => 'test',
        ]);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse($user->toArray(), $response, 'user');
        $this->assertResponseStatus(200, $response);

        // We have to logout the user to avoid problem
        /** @var Authenticator */
        $authenticator = $this->ci->get(Authenticator::class);
        $authenticator->logout();
    }

    public function testLoginWithDisableEmail(): void
    {
        // Force config
        /** @var Config */
        $config = $this->ci->get(Config::class);
        $config->set('site.login.enable_email', false);

        // Create fake throttler
        /** @var Throttler */
        $throttler = Mockery::mock(Throttler::class)
            ->shouldReceive('getDelay')->once()->with('sign_in_attempt', ['user_identifier' => 'foo@bar.com'])->andReturn(0)
            ->shouldReceive('logEvent')->once()->with('sign_in_attempt', ['user_identifier' => 'foo@bar.com'])
            ->getMock();
        $this->ci->set(Throttler::class, $throttler);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/account/login', [
            'user_name'  => 'foo@bar.com',
            'password'   => 'bar',
        ]);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse([
            'title'       => 'Invalid Credentials',
            'description' => 'User not found or password is invalid.',
            'status'      => 403,
        ], $response);
        $this->assertResponseStatus(403, $response);
    }
}

class LoginActionSprinkle extends Account
{
    /**
     * {@inheritDoc}
     */
    public function getEventListeners(): array
    {
        return [
            UserRedirectedAfterLoginEvent::class => [
                UserRedirectedAfterLoginListener::class,
                UserRedirectedAfterLoginListener2::class,
            ],
        ];
    }
}

class UserRedirectedAfterLoginListener
{
    public function __invoke(UserRedirectedAfterLoginEvent $event): void
    {
        $event->setRedirect('/home');
        $event->stop();
    }
}

class UserRedirectedAfterLoginListener2
{
    public function __invoke(UserRedirectedAfterLoginEvent $event): void
    {
        $event->setRedirect('/index'); // Won't be used, as "/home" stop propagation
    }
}
