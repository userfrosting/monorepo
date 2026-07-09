<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\Database\Models;

use Carbon\Carbon;
use Illuminate\Cache\Repository as Cache;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Tests\AccountTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

/**
 * UserTest Class. Tests the User Model.
 */
class UserTest extends AccountTestCase
{
    use MockeryPHPUnitIntegration;
    use RefreshDatabase;

    /**
     * Setup the database schema.
     */
    public function setUp(): void
    {
        parent::setUp();

        // Setup test database
        $this->refreshDatabase();
    }

    public function testUser(): void
    {
        // Assert Initial DB state
        $this->assertSame(0, User::count());

        $user = new User([
            'user_name'  => 'testing',
            'email'      => 'test@test.test',
            'first_name' => 'Test',
            'last_name'  => 'Ing',
            'password'   => 'secret',
        ]);
        $user->save();
        $this->assertInstanceOf(UserInterface::class, $user); // @phpstan-ignore-line

        // Assert new state
        $this->assertSame(1, User::count());

        // Get User and assert it's properties
        /** @var User */
        $fetchedUser = User::find($user->id);
        $this->assertSame('testing', $fetchedUser->user_name);
        $this->assertSame('test@test.test', $fetchedUser->email);
        $this->assertSame('Test', $fetchedUser->first_name);
        $this->assertSame('Ing', $fetchedUser->last_name);
        $this->assertSame('Test Ing', $fetchedUser->full_name);
        $this->assertIsString($fetchedUser->password); // @phpstan-ignore-line
        $this->assertNotSame('secret', $fetchedUser->password); // Password is hash, therefore *not* same
        $this->assertIsString($fetchedUser->locale); // @phpstan-ignore-line
        $this->assertIsBool($fetchedUser->flag_verified); // @phpstan-ignore-line
        $this->assertIsBool($fetchedUser->flag_enabled); // @phpstan-ignore-line

        // Assert comparePassword
        $this->assertTrue($fetchedUser->comparePassword('secret'));
        $this->assertFalse($fetchedUser->comparePassword('password'));

        // Delete
        $fetchedUser->delete();

        // Assert new state
        $this->assertSame(0, User::count());

        // Assert soft delete
        $this->assertSame(1, User::withTrashed()->count());
    }

    public function testUserIsMaster(): void
    {
        /** @var User */
        $masterUser = User::factory()->create();

        /** @var User */
        $normalUser = User::factory()->create();

        $config = Mockery::mock(Config::class)
            ->shouldReceive('get')->with('reserved_user_ids.master')->times(2)->andReturn($masterUser->id)
            ->getMock();
        $this->ci->set(Config::class, $config);

        $this->assertTrue($masterUser->isMaster());
        $this->assertFalse($normalUser->isMaster());
    }

    public function testIsPasswordExpired(): void
    {
        /** @var User */
        $user = User::factory()->create();

        // Assert default state
        $this->assertFalse($user->isPasswordExpired());
    }

    public function testIsPasswordExpiredForEmptyPassword(): void
    {
        /** @var User */
        $user = User::factory()->create(
            [
                'password' => '',
            ]
        );

        // Assert default state
        $this->assertTrue($user->isPasswordExpired());
    }

    public function testIsPasswordExpiredForExpiredPassword(): void
    {
        // Get current config and set password expiration to 90 days
        /** @var Config */
        $config = $this->ci->get(Config::class);
        $config->set('user.password.expiration.timeout', 90);

        /** @var User */
        $user = User::factory()->create(
            [
                'password_last_set' => Carbon::now()->subDays(91),
            ]
        );

        // Assert default state
        $this->assertTrue($user->isPasswordExpired());
    }

    public function testIsPasswordExpiredForNotExpiredPassword(): void
    {
        // Get current config and set password expiration to 90 days
        /** @var Config */
        $config = $this->ci->get(Config::class);
        $config->set('user.password.expiration.timeout', 90);

        /** @var User */
        $user = User::factory()->create(
            [
                'password_last_set' => Carbon::now()->subDays(89),
            ]
        );

        // Assert default state
        $this->assertFalse($user->isPasswordExpired());
    }

    public function testIsPasswordExpiredForNeverSetPassword(): void
    {
        /** @var User */
        $user = User::factory()->create(
            [
                'password_last_set' => null,
            ]
        );

        // Assert default state
        $this->assertTrue($user->isPasswordExpired());
    }

    public function testUserAvatar(): void
    {
        /** @var User */
        $user = User::factory()->make();

        $this->assertIsString($user->avatar); // @phpstan-ignore-line
        $this->assertStringContainsString('gravatar', $user->avatar);
    }

    /**
     * @see https://github.com/userfrosting/sprinkle-account/pull/15
     */
    public function testUserAvatarForEmptyEmail(): void
    {
        /** @var User */
        $user = new User();
        $data = $user->toArray();

        $this->assertArrayNotHasKey('email', $data);
        $this->assertSame('https://www.gravatar.com/avatar/d41d8cd98f00b204e9800998ecf8427e?d=mm', $data['avatar']);
    }

    /**
     * Test user hard deletion.
     */
    public function testUserForceDelete(): void
    {
        /** @var User */
        $user = User::factory()->create();

        $this->assertSame(1, User::withTrashed()->count());
        $this->assertTrue($user->forceDelete());
        $this->assertSame(0, User::withTrashed()->count());
    }

    public function testUserCache(): void
    {
        /** @var User */
        $user = User::factory()->create();

        $cache = $user->getCache();
        $this->assertInstanceOf(Cache::class, $cache); // @phpstan-ignore-line
    }

    public function testFindCache(): void
    {
        /** @var User */
        $user = User::factory()->create();

        /** @var Cache */
        $cache = $this->ci->get(Cache::class);

        /** @var Config */
        $config = $this->ci->get(Config::class);

        // Config key
        $key = $config->get('cache.user.key') . $user->id;

        // Assert initial cache
        $this->assertFalse($cache->has($key));

        // Get fetched
        $fetched = User::findCached($user->id);
        $this->assertSame($user->id, $fetched?->id);

        // Assert cache directly
        $this->assertTrue($cache->has($key));
        $this->assertInstanceOf(UserInterface::class, $cache->get($key));
        $this->assertSame($user->id, $cache->get($key)->id);

        // Forget and assert cache
        $fetched->forgetCache();
        $this->assertFalse($cache->has($key));
    }

    public function testFindCacheWithNullUser(): void
    {
        /** @var Cache */
        $cache = $this->ci->get(Cache::class);

        /** @var Config */
        $config = $this->ci->get(Config::class);

        // Config key (with fake user id)
        $key = $config->get('cache.user.key') . '1234';

        // Assert initial cache
        $this->assertFalse($cache->has($key));

        // Get findCached
        $fetched = User::findCached(1234);
        $this->assertNull($fetched);

        // N.B.: Laravel cache won't store anything if the value is null.
        $this->assertFalse($cache->has($key));
    }
}
