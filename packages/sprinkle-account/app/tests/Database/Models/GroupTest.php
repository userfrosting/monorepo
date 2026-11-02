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

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use UserFrosting\Sprinkle\Account\Database\Models\Group;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\GroupInterface;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Tests\AccountTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

/**
 * GroupTest Class. Tests the Group Model.
 */
class GroupTest extends AccountTestCase
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

    public function testGroup(): void
    {
        // Assert Initial DB state
        $this->assertSame(0, Group::count());

        // Create new group
        $group = new Group([
            'slug'  => 'testing',
            'name'  => 'Test Group',
        ]);
        $group->save();
        $this->assertInstanceOf(GroupInterface::class, $group); // @phpstan-ignore-line

        // Assert new state
        $this->assertSame(1, Group::count());

        // Get User and assert it's properties
        /** @var Group */
        $fetchedGroup = Group::find($group->id);
        $this->assertSame('testing', $fetchedGroup->slug);
        $this->assertSame('Test Group', $fetchedGroup->name);
        $this->assertSame('', $fetchedGroup->description);
        $this->assertIsString($fetchedGroup->icon); // @phpstan-ignore-line

        // Delete
        $fetchedGroup->delete();

        // Assert new state
        $this->assertSame(0, Group::count());
        $this->assertSame(1, Group::withTrashed()->count());
        $this->assertTrue($fetchedGroup->trashed());

        $fetchedGroup->restore();
        $this->assertSame(1, Group::count());
    }

    public function testUserRelation(): void
    {
        /** @var Group */
        $group = Group::factory()->create();

        // Assert initial state
        $this->assertSame(0, $group->users->count());

        /** @var User */
        $user = User::factory()->make();

        // Attach user to group
        $group->users()->save($user);
        $group->refresh();

        // Assert new state
        $this->assertSame(1, $group->users->count());

        // Assert reverse relation
        $this->assertSame($group->id, $user->group?->id);
    }
}
