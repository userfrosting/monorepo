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
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserVerificationInterface;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Database\Models\UserVerification;
use UserFrosting\Sprinkle\Account\Tests\AccountTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

/**
 * UserVerificationTest Class. Tests the User Verification Model.
 */
class UserVerificationTest extends AccountTestCase
{
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

    public function testUserVerificationTable(): void
    {
        // Assert Initial DB state
        $this->assertSame(0, UserVerification::count());

        /** @var User */
        $user = User::factory()->create();

        $verification = new UserVerification([
            'code'         => '123456',
            'user_id'      => $user->id,
            'expires_at'   => Carbon::now()->addMinutes(5),
            'completed_at' => null,
        ]);
        $verification->save();
        $this->assertInstanceOf(UserVerificationInterface::class, $verification); // @phpstan-ignore-line

        // Assert new state
        $this->assertSame(1, UserVerification::count());

        // Get verification and assert its properties
        /** @var UserVerification */
        $fetchedVerification = UserVerification::find($verification->id);
        $this->assertSame($user->id, $fetchedVerification->user_id);
        $this->assertSame(hash('sha512', '123456'), $fetchedVerification->code);
        $this->assertNull($fetchedVerification->completed_at);
        $this->assertSame($user->id, $fetchedVerification->user?->id);

        // Delete
        $fetchedVerification->delete();

        // Assert new state
        $this->assertSame(0, UserVerification::count());
    }

    public function testValidateCode(): void
    {
        /** @var User */
        $user = User::factory()->create();

        $model = new UserVerification();
        $verification = $model->storeCode($user, '123456', 300);

        $this->assertTrue($model->validateCode($user, '123456'));

        /** @var UserVerification */
        $freshVerification = $verification->fresh();
        $this->assertNotNull($freshVerification->completed_at);

        // Completed verification can't be reused
        $this->assertFalse($model->validateCode($user, '123456'));
        $this->assertFalse($model->validateCode($user, 'bad-code'));
    }

    public function testValidateCodeFailed(): void
    {
        /** @var User */
        $user = User::factory()->create();

        // Need to have at least one verification in the database for the user to test against
        $model = new UserVerification();
        $model->storeCode($user, '123456', 1);

        $this->assertFalse($model->validateCode($user, '654321')); // Wrong code
    }

    public function testClearExpired(): void
    {
        /** @var User */
        $user = User::factory()->create();

        $expired = new UserVerification([
            'code'         => '111111',
            'user_id'      => $user->id,
            'expires_at'   => Carbon::now()->subMinute(),
            'completed_at' => null,
        ]);
        $expired->save();

        $notExpired = new UserVerification([
            'code'         => '222222',
            'user_id'      => $user->id,
            'expires_at'   => Carbon::now()->addMinute(),
            'completed_at' => null,
        ]);
        $notExpired->save();

        $this->assertSame(2, UserVerification::count());

        (new UserVerification())->clearExpired();

        $this->assertSame(1, UserVerification::count());
        $this->assertTrue(UserVerification::query()->whereKey($notExpired->id)->exists()); // @phpstan-ignore-line
    }
}
