<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\Authenticate;

use Birke\Rememberme\Authenticator as RememberMe;
use Birke\Rememberme\Cookie\PHPCookie;
use Birke\Rememberme\LoginResult;
use Birke\Rememberme\Storage\StorageInterface;
use Illuminate\Cache\Repository as Cache;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PDOException;
use UserFrosting\Config\Config;
use UserFrosting\Session\Session;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Exceptions\AccountDisabledException;
use UserFrosting\Sprinkle\Account\Exceptions\AccountNotFoundException;
use UserFrosting\Sprinkle\Account\Exceptions\AccountNotVerifiedException;
use UserFrosting\Sprinkle\Account\Exceptions\AuthCompromisedException;
use UserFrosting\Sprinkle\Account\Exceptions\AuthExpiredException;
use UserFrosting\Sprinkle\Account\Exceptions\InvalidCredentialsException;
use UserFrosting\Sprinkle\Account\Exceptions\PasswordExpiredException;
use UserFrosting\Sprinkle\Account\Tests\AccountTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

/**
 * Integration tests for the Authenticator.
 */
class AuthenticatorTest extends AccountTestCase
{
    use RefreshDatabase;
    use MockeryPHPUnitIntegration;

    /**
     * Setup the test database.
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
    }

    public function testAuthenticate(): void
    {
        /** @var User */
        $user = User::factory()->create();

        /** @var Authenticator */
        $authenticator = $this->ci->get(Authenticator::class);

        // Valid credentials.
        // N.B.: "password" is hardcoded in factory.
        $authUser = $authenticator->authenticate('user_name', $user->user_name, 'password');
        $this->assertSame($user->id, $authUser->id);
    }

    public function testAuthenticateWithBadPassword(): void
    {
        /** @var User */
        $user = User::factory()->create();

        /** @var Authenticator */
        $authenticator = $this->ci->get(Authenticator::class);

        $this->expectException(InvalidCredentialsException::class);
        $authenticator->authenticate('id', $user->id, 'secret');
    }

    public function testAuthenticateWithNullUser(): void
    {
        /** @var Authenticator */
        $authenticator = $this->ci->get(Authenticator::class);

        $this->expectException(AccountNotFoundException::class);
        $authenticator->authenticate('id', 123, 'password');
    }

    public function testAuthenticateWithUserNoPassword(): void
    {
        /** @var User */
        $user = User::factory()->state([
            'password' => '',
        ])->create();

        /** @var Authenticator */
        $authenticator = $this->ci->get(Authenticator::class);

        $this->expectException(PasswordExpiredException::class);
        $authenticator->authenticate('id', $user->id, '');
    }

    public function testAuthenticateWithDisableUser(): void
    {
        /** @var User */
        $user = User::factory()->state([
            'flag_enabled' => false,
        ])->create();

        /** @var Authenticator */
        $authenticator = $this->ci->get(Authenticator::class);

        $this->expectException(AccountDisabledException::class);
        $authenticator->authenticate('id', $user->id, 'password');
    }

    public function testAuthenticateWithUserNotVerified(): void
    {
        /** @var User */
        $user = User::factory()->state([
            'flag_verified' => false,
        ])->create();

        // Make sure email verification is required
        $config = $this->ci->get(Config::class);
        $config->set('site.registration.require_email_verification', true);
        $this->assertTrue($config->get('site.registration.require_email_verification'));

        // Get authenticator, set expectation and run test
        $authenticator = $this->ci->get(Authenticator::class);
        $this->expectException(AccountNotVerifiedException::class);
        $authenticator->authenticate('id', $user->id, 'password');
    }

    public function testCheckGuestWithDefault(): void
    {
        /** @var Authenticator */
        $authenticator = $this->ci->get(Authenticator::class);

        /** @var Session */
        $session = $this->ci->get(Session::class);

        // Start session
        $session->start();

        // Do assertions
        $this->assertFalse($authenticator->check());
        $this->assertTrue($authenticator->guest());

        /** @var Session */
        $session = $this->ci->get(Session::class);
    }

    public function testLogin(): void
    {
        /** @var User */
        $user = User::factory()->create();

        /** @var Authenticator */
        $authenticator = $this->ci->get(Authenticator::class);

        /** @var Config */
        $config = $this->ci->get(Config::class);

        /** @var Session */
        $session = $this->ci->get(Session::class);

        // Start session
        $session->start();

        // Test session to avoid false positive
        $key = $config->get('session.keys.current_user_id');
        $this->assertNull($session[$key]);

        // Login the test user
        $authenticator->login($user, false);

        // Test session to see if user was logged in
        $this->assertNotNull($session[$key]);
        $this->assertSame($user->id, $session[$key]);

        // Test check/guest
        $this->assertTrue($authenticator->check());
        $this->assertFalse($authenticator->guest());

        // Must logout to avoid test issue
        $authenticator->logout(true);

        // We'll test the logout system works too while we're at it (and depend on it)
        $key = $config->get('session.keys.current_user_id');
        $this->assertNull($session[$key]);
        $this->assertNotSame($user->id, $session[$key]);

        // Retest check/guest
        $this->assertFalse($authenticator->check());
        $this->assertTrue($authenticator->guest());
    }

    public function testAttempt(): void
    {
        /** @var User */
        $user = User::factory()->create();

        /** @var Authenticator */
        $authenticator = $this->ci->get(Authenticator::class);

        /** @var Session */
        $session = $this->ci->get(Session::class);

        // Start session
        $session->start();

        // Attempt to login user
        // N.B.: "password" is hardcoded in factory.
        $authUser = $authenticator->attempt('user_name', $user->user_name, 'password');
        $this->assertSame($user->id, $authUser->id);

        // Must logout to avoid test issue
        $authenticator->logout(true);
        $session->destroy();
    }

    public function testLoginWithRememberMe(): void
    {
        /** @var User */
        $testUser = User::factory()->create();

        /** @var Authenticator */
        $authenticator = $this->ci->get(Authenticator::class);

        /** @var Config */
        $config = $this->ci->get(Config::class);
        $config->set('remember_me.domain', 'foo.bar');

        /** @var Session */
        $session = $this->ci->get(Session::class);
        $key = strval($config->get('session.keys.current_user_id'));

        // Start session
        $session->start();

        // Test session to avoid false positive
        $this->assertNull($session[$key]);

        // Perform login
        $authenticator->login($testUser, true);

        // Test session to test that user was logged in
        $this->assertNotNull($session[$key]);
        $this->assertSame($testUser->id, $session[$key]);

        // We'll manually delete the session,
        $session->set($key, null);
        $this->assertNull($session[$key]);
        $this->assertNotSame($testUser->id, $session[$key]);

        // Now go through the loginRememberedUser process
        // First, we'll simulate a page refresh by creating a new authenticator
        // (So `$this->user` will be null)
        /** @var Authenticator */
        $authenticator = $this->ci->make(Authenticator::class);

        // Get user
        $user = $authenticator->user();

        // If loginRememberedUser doesn't work, `user` will be null.
        $this->assertNotNull($user);
        $this->assertEquals($user->id, $testUser->id);
        $this->assertEquals($user->id, $session[$key]);
        $this->assertTrue($authenticator->viaRemember());

        // Must logout to avoid test issue
        $authenticator->logout();
        $session->destroy();
    }

    public function testLoginWithRememberMeForNullCachedUser(): void
    {
        /** @var User */
        $testUser = User::factory()->create();

        /** @var Authenticator */
        $authenticator = $this->ci->get(Authenticator::class);

        /** @var Config */
        $config = $this->ci->get(Config::class);
        $config->set('remember_me.domain', 'foo.bar');

        /** @var Session */
        $session = $this->ci->get(Session::class);
        $key = strval($config->get('session.keys.current_user_id'));

        // Start session
        $session->start();

        // Test session to avoid false positive
        $this->assertNull($session[$key]);

        // Perform login
        $authenticator->login($testUser, true);

        // Test session to test that user was logged in
        $this->assertNotNull($session[$key]);
        $this->assertSame($testUser->id, $session[$key]);

        // We'll manually delete the session,
        $session->set($key, null);
        $this->assertNull($session[$key]);
        $this->assertNotSame($testUser->id, $session[$key]);

        // We also remove cached user AND delete the user
        /** @var Cache */
        $cache = $this->ci->get(Cache::class);
        $key = $config->get('cache.user.key') . $testUser->id;
        $cache->delete($key);
        $testUser->delete();

        // Now go through the loginRememberedUser process
        // First, we'll simulate a page refresh by creating a new authenticator
        // (So `$this->user` will be null)
        /** @var Authenticator */
        $authenticator = $this->ci->make(Authenticator::class);

        // Get user
        $user = $authenticator->user();

        // If loginRememberedUser doesn't work, `user` will be null.
        $this->assertNull($user);
        $this->assertNull($session[$key]);
        $this->assertTrue($authenticator->viaRemember());

        // Must logout to avoid test issue
        $session->destroy();
    }

    public function testLoginWithRememberMeForAuthCompromisedException(): void
    {
        // Mock RememberMe so we can force AuthCompromisedException
        $loginResult = Mockery::mock(LoginResult::class)
            ->shouldReceive('isSuccess')->once()->andReturn(false)
            ->shouldReceive('hasPossibleManipulation')->once()->andReturn(true)
            ->getMock();
        $storageInterface = $this->ci->get(StorageInterface::class);
        /** @var class-string */
        $class = RememberMe::class . '[login]';
        $rememberMe = Mockery::mock($class, [$storageInterface])
            ->shouldReceive('login')->once()->andReturn($loginResult)
            ->getMock();
        $this->ci->set(RememberMe::class, $rememberMe);

        /** @var User */
        $testUser = User::factory()->create();

        /** @var Authenticator */
        $authenticator = $this->ci->get(Authenticator::class);

        /** @var Config */
        $config = $this->ci->get(Config::class);

        /** @var Session */
        $session = $this->ci->get(Session::class);
        $key = strval($config->get('session.keys.current_user_id'));

        // Start session and login user
        $session->start();
        $authenticator->login($testUser, true);

        // We'll manually delete the session,
        $session->set($key, null);

        // Now go through the loginRememberedUser process
        // First, we'll simulate a page refresh by creating a new authenticator
        // (So `$this->user` will be null)
        /** @var Authenticator */
        $authenticator = $this->ci->make(Authenticator::class);

        // Get user
        $this->expectException(AuthCompromisedException::class);
        $authenticator->user();

        // Must destroy to avoid test issue
        $session->destroy();
    }

    public function testLoginSessionUser(): void
    {
        /** @var User */
        $testUser = User::factory()->create();

        /** @var Authenticator */
        $authenticator = $this->ci->get(Authenticator::class);

        /** @var Config */
        $config = $this->ci->get(Config::class);

        /** @var Session */
        $session = $this->ci->get(Session::class);
        $key = strval($config->get('session.keys.current_user_id'));

        // Start session
        $session->start();

        // Perform login
        $authenticator->login($testUser);

        // Now go through the `loginSessionUser` process
        // First, we'll simulate a page refresh by creating a new authenticator
        // (So `$this->user` will be null)
        /** @var Authenticator */
        $authenticator = $this->ci->make(Authenticator::class);

        // Get user
        $user = $authenticator->user();

        // If loginSessionUser doesn't work, `user` will be null.
        $this->assertNotNull($user);
        $this->assertEquals($user->id, $testUser->id);
        $this->assertEquals($user->id, $session[$key]);
        $this->assertFalse($authenticator->viaRemember());

        // Must logout to avoid test issue
        $authenticator->logout();
        $session->destroy();
    }

    public function testLoginSessionUserForSessionNotStarted(): void
    {
        /** @var User */
        $testUser = User::factory()->create();

        /** @var Authenticator */
        $authenticator = $this->ci->get(Authenticator::class);

        /** @var Session */
        $session = $this->ci->get(Session::class);

        // Start session
        $session->start();

        // Perform login
        $authenticator->login($testUser);

        // Now go through the `loginSessionUser` process
        // First, we'll simulate a page refresh by creating a new authenticator
        // (So `$this->user` will be null)
        /** @var Authenticator */
        $authenticator = $this->ci->make(Authenticator::class);

        // We now destroy session
        $session->destroy();

        // Get user
        $user = $authenticator->user();

        // loginSessionUser won't work since there's no session, `user` will be null.
        // We also make sure no exception is thrown.
        $this->assertNull($user);
        $this->assertFalse($authenticator->viaRemember());
    }

    public function testLoginSessionUserWithAuthExpired(): void
    {
        // Mock RememberMe to simulate a fake cookie.
        $storageInterface = $this->ci->get(StorageInterface::class);
        $cookie = Mockery::mock(PHPCookie::class)
            ->makePartial()
            ->shouldReceive('getValue')->andReturn('foo')
            ->getMock();
        /** @var class-string */
        $class = RememberMe::class . '[getCookie]';
        $rememberMe = Mockery::mock($class, [$storageInterface])
            ->shouldReceive('getCookie')->andReturn($cookie)
            ->getMock();
        $this->ci->set(RememberMe::class, $rememberMe);

        /** @var User */
        $testUser = User::factory()->create();

        /** @var Authenticator */
        $authenticator = $this->ci->get(Authenticator::class);

        /** @var Session */
        $session = $this->ci->get(Session::class);

        // Start session
        $session->start();

        // Perform login
        $authenticator->login($testUser);

        // Now go through the `loginSessionUser` process
        // First, we'll simulate a page refresh by creating a new authenticator
        // (So `$this->user` will be null)
        /** @var Authenticator */
        $authenticator = $this->ci->make(Authenticator::class);

        // Get user
        $this->expectException(AuthExpiredException::class);
        $authenticator->user();

        // Must logout to avoid test issue
        $session->destroy();
    }

    public function testLoginWithNoOneLoggedIn(): void
    {
        /** @var Authenticator */
        $authenticator = $this->ci->get(Authenticator::class);
        $this->assertTrue($authenticator->guest());
        $authenticator->logout();
        $this->assertTrue($authenticator->guest());
    }

    public function testLoginSessionUserForBadId(): void
    {
        /** @var User */
        $testUser = User::factory()->create();

        /** @var Authenticator */
        $authenticator = $this->ci->get(Authenticator::class);

        /** @var Config */
        $config = $this->ci->get(Config::class);

        /** @var Session */
        $session = $this->ci->get(Session::class);
        $key = strval($config->get('session.keys.current_user_id'));

        // Start session
        $session->start();

        // Perform login
        $authenticator->login($testUser, true);

        // We'll manually alter the session,
        $session->set($key, $testUser->id + 1);
        $this->assertNotSame($testUser->id, $session[$key]);

        // Now go through the `loginSessionUser` process
        // First, we'll simulate a page refresh by creating a new authenticator
        // (So `$this->user` will be null)
        /** @var Authenticator */
        $authenticator = $this->ci->make(Authenticator::class);

        // Get user
        $this->expectException(AccountNotFoundException::class);
        $authenticator->user();

        // Must destroy session to avoid test issue
        $session->destroy();
    }

    public function testPDOException(): void
    {
        /** @var UserInterface */
        $userModel = Mockery::mock(UserInterface::class)
            ->shouldReceive('findCached')->andThrow(new PDOException())
            ->getMock();

        /** @var Authenticator */
        $authenticator = $this->ci->get(Authenticator::class);
        $authenticator->setUserModel($userModel);

        /** @var Config */
        $config = $this->ci->get(Config::class);

        /** @var Session */
        $session = $this->ci->get(Session::class);
        $key = strval($config->get('session.keys.current_user_id'));

        // Start session
        $session->start();

        // Set a session so we go through loginSessionUser
        $session->set($key, 1);

        // PDOException won't be thrown, as it's cached. User will be null.
        $user = $authenticator->user();
        $this->assertNull($user);
        $session->destroy();
    }
}
