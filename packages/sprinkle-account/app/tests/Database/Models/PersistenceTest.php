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

use DateTime;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\PersistenceInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Persistence;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Tests\AccountTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

/**
 * PersistenceTest Class. Tests the Persistence Model.
 */
class PersistenceTest extends AccountTestCase
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

    public function testPersistence(): void
    {
        // Assert Initial DB state
        $this->assertSame(0, Persistence::count());

        $token = 'f98103e9217f099208569d295c1b276f1821348636c268c854bb2a086e0037cd'; // TOKEN
        $ptoken = 'a512c84ae422bfa80f18f86ff885a4c35769a784e5f63f34587fb18ae028d317'; // PTOKEN

        /** @var User */
        $user = User::factory()->create();

        $persistence = new Persistence([
            'token'            => $token,
            'persistent_token' => $ptoken,
        ]);
        $persistence->user()->associate($user);
        $persistence->save();
        $this->assertInstanceOf(PersistenceInterface::class, $persistence); // @phpstan-ignore-line

        // Assert new state
        $this->assertSame(1, Persistence::count());

        // Get Model and assert it's default properties
        /** @var Persistence */
        $fetched = Persistence::find($persistence->id);
        $this->assertSame($user->id, $fetched->user_id);
        $this->assertSame($token, $fetched->token);
        $this->assertSame($ptoken, $fetched->persistent_token);
        $this->assertNull($fetched->expires_at);

        // Assert User relations
        $this->assertSame($user->id, $fetched->user->id);

        // Delete
        $fetched->delete();

        // Assert new state
        $this->assertSame(0, Persistence::count());
    }

    public function testUserRelation(): void
    {
        /** @var User */
        $user = User::factory()->create();

        Persistence::factory()->count(3)->for($user)->create();

        $this->assertSame(3, $user->persistences()->count());
        $this->assertContainsOnlyInstancesOf(PersistenceInterface::class, $user->persistences); // @phpstan-ignore-line

        // Test force deletion and cascade deletion
        $user->forceDelete();
        $this->assertSame(0, Persistence::count());
    }

    public function testDateCasting(): void
    {
        /** @var User */
        $user = User::factory()->create();

        /** @var Persistence */
        $persistence = Persistence::factory()
            ->state(['expires_at' => new DateTime('2022-01-01')])
            ->for($user)
            ->create();

        $this->assertInstanceOf(DateTime::class, $persistence->expires_at);
        $this->assertSame('2022-01-01', $persistence->expires_at->format('Y-m-d'));
    }

    public function testScopeNotExpired(): void
    {
        /** @var User */
        $user = User::factory()->create();

        // Not expired
        Persistence::factory()
            ->state(['expires_at' => new DateTime('now + 2 days')])
            ->for($user)
            ->create();

        // Expired
        Persistence::factory()
            ->state(['expires_at' => new DateTime('now - 2 days')])
            ->for($user)
            ->create();

        $this->assertSame(2, Persistence::count());
        $this->assertSame(1, Persistence::notExpired()->count());
        $this->assertSame(2, $user->persistences()->count());
        $this->assertSame(1, $user->persistences()->notExpired()->count()); // @phpstan-ignore-line
    }
}
