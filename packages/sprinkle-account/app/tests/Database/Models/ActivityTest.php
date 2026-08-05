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

use Illuminate\Database\Eloquent\Factories\Sequence;
use UserFrosting\Sprinkle\Account\Database\Models\Activity;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\ActivityInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Tests\AccountTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

/**
 * ActivityTest Class. Tests the Activity Model.
 */
class ActivityTest extends AccountTestCase
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

    public function testActivity(): void
    {
        // Assert Initial DB state
        $this->assertSame(0, Activity::count());

        /** @var User */
        $user = User::factory()->create();

        $activity = new Activity([
            'type'  => 'TEST',
        ]);
        $activity->user()->associate($user);
        $activity->save();
        $this->assertInstanceOf(ActivityInterface::class, $activity); // @phpstan-ignore-line

        // Assert new state
        $this->assertSame(1, Activity::count());

        // Get User and assert it's properties
        /** @var Activity */
        $fetched = Activity::find($activity->id);
        $this->assertNull($fetched->ip_address);
        $this->assertSame($user->id, $fetched->user_id);
        $this->assertSame('TEST', $fetched->type);
        $this->assertNull($fetched->occurred_at);
        $this->assertSame('', $fetched->description);

        // Delete
        $fetched->delete();

        // Assert new state
        $this->assertSame(0, Activity::count());
    }

    public function testUserRelation(): void
    {
        /** @var User */
        $user = User::factory()->create();

        $activities = Activity::factory()
            ->count(3)
            ->state(new Sequence(
                ['type' => 'one'],
                ['type' => 'two'],
                ['type' => 'three'],
            ))
            ->state(new Sequence(
                fn ($sequence) => ['occurred_at' => new \DateTime("now -{$sequence->index} day")],
            ))
            ->for($user)
            ->create();

        // Assert one way
        $this->assertSame($user->id, $activities[0]->user->id);

        // Assert Reverse relation
        $this->assertSame(3, $user->activities->count());
        $this->assertContainsOnlyInstancesOf(ActivityInterface::class, $user->activities); // @phpstan-ignore-line

        // Assert lastActivity.
        // N.B.: First one will be "oldest", since they are created in reverse
        // order in the sequence.
        $this->assertInstanceOf(ActivityInterface::class, $user->lastActivity()); // @phpstan-ignore-line
        $this->assertSame($activities[0]->type, $user->lastActivity()->type);

        // Assert property/attribute alias
        $this->assertSame($user->lastActivity?->type, $user->lastActivity()->type);

        // Assert lastActivity with type. Let's use 2nd one for test
        $this->assertSame($activities[1]->id, $user->lastActivity($activities[1]->type)?->id);

        // Assert Last activity time
        $this->assertEquals($activities[0]->occurred_at, $user->lastActivity()->occurred_at);
        $this->assertEquals($activities[0]->occurred_at, $user->lastActivityTime());

        // Assert getSecondsSinceLastActivity
        $this->assertIsInt($user->getSecondsSinceLastActivity()); // @phpstan-ignore-line

        // Test force deletion and cascade deletion
        $user->forceDelete();
        $this->assertSame(0, Activity::count());
    }

    public function testNoLastActivity(): void
    {
        /** @var User */
        $user = User::factory()->create();

        $this->assertSame(0, $user->activities->count());
        $this->assertNull($user->lastActivity());
        $this->assertNull($user->last_activity);
    }

    /**
     * Test for join operation for Sprunje.
     */
    public function testUserJoinLastActivity(): void
    {
        /** @var User */
        $userA = User::factory()->has(
            Activity::factory()
                ->state(['occurred_at' => new \DateTime('2019-01-01')])
        )->has(
            Activity::factory()
                ->state(['occurred_at' => new \DateTime('2022-01-01')])
        )->create();
        $userB = User::factory()->has(
            Activity::factory()
                ->state(['occurred_at' => new \DateTime('2020-01-01')])
        )->create();

        // Default order is 1, 2
        $nonSortedUsers = User::all();
        $this->assertContainsOnlyInstancesOf(UserInterface::class, $nonSortedUsers);
        $this->assertSame('2022-01-01', $nonSortedUsers[0]->lastActivity?->occurred_at?->format('Y-m-d'));
        $this->assertSame('2020-01-01', $nonSortedUsers[1]->lastActivity?->occurred_at?->format('Y-m-d'));
        $this->assertSame([$userA->id, $userB->id], $nonSortedUsers->pluck('id')->toArray()); // @phpstan-ignore-line

        // Sort by lastActivity, order will be 2, 1
        $sortedUsers = User::joinLastActivity()
                            ->orderBy('last_activity')
                            ->get();

        $this->assertContainsOnlyInstancesOf(UserInterface::class, $sortedUsers);
        $this->assertSame('2020-01-01', $sortedUsers[0]->lastActivity?->occurred_at?->format('Y-m-d'));
        $this->assertSame('2022-01-01', $sortedUsers[1]->lastActivity?->occurred_at?->format('Y-m-d'));
        $this->assertSame([$userB->id, $userA->id], $sortedUsers->pluck('id')->toArray()); // @phpstan-ignore-line
    }

    /**
     * Test for join operation for Sprunje.
     */
    public function testJoinUser(): void
    {
        /** @var User */
        $userFoo = User::factory()
            ->has(Activity::factory())
            ->state(['user_name' => 'foo'])
            ->create();
        $userBar = User::factory()
            ->has(Activity::factory())
            ->state(['user_name' => 'bar'])
            ->create();

        // Default order is 'foo', 'bar'
        $nonSortedActivity = Activity::all();
        $this->assertContainsOnlyInstancesOf(ActivityInterface::class, $nonSortedActivity);
        $this->assertSame('foo', $nonSortedActivity[0]->user->user_name);
        $this->assertSame('bar', $nonSortedActivity[1]->user->user_name);
        $this->assertSame([$userFoo->id, $userBar->id], $nonSortedActivity->pluck('id')->toArray()); // @phpstan-ignore-line

        // Sort by lastActivity, order will be 2, 1
        $sortedUsers = Activity::joinUser()
                            ->orderBy('users.user_name')
                            ->get();

        $this->assertContainsOnlyInstancesOf(ActivityInterface::class, $sortedUsers);
        $this->assertSame('bar', $sortedUsers[0]->user->user_name);
        $this->assertSame('foo', $sortedUsers[1]->user->user_name);
        $this->assertSame([$userBar->id, $userFoo->id], $sortedUsers->pluck('id')->toArray()); // @phpstan-ignore-line
    }
}
