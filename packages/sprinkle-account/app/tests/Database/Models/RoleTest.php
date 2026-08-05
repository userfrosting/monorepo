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

use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\RoleInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Tests\AccountTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

/**
 * RoleTest Class. Tests the Role Model.
 */
class RoleTest extends AccountTestCase
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

    public function testRole(): void
    {
        // Assert Initial DB state
        $this->assertSame(0, Role::count());

        $role = new Role([
            'name'  => 'Test',
            'slug'  => 'test',
        ]);
        $role->save();
        $this->assertInstanceOf(RoleInterface::class, $role); // @phpstan-ignore-line

        // Assert new state
        $this->assertSame(1, Role::count());

        // Get Model and assert it's default properties
        /** @var Role */
        $fetched = Role::find($role->id);
        $this->assertSame('Test', $fetched->name);
        $this->assertSame('test', $fetched->slug);
        $this->assertSame('', $fetched->description);

        // Delete
        $fetched->delete();

        // Assert new state
        $this->assertSame(0, Role::count());
    }

    public function testUserRelation(): void
    {
        /** @var Role */
        $role = Role::factory()->create();

        $users = User::factory()
            ->count(3)
            ->hasAttached($role)
            ->create();

        $this->assertContainsOnlyInstancesOf(UserInterface::class, $role->users); // @phpstan-ignore-line
        $this->assertSame(3, $role->users()->count());
        $this->assertSame(3, $role->users->count());

        // Assert reverse relation
        $this->assertSame(1, $users[0]->roles->count());
        $this->assertSame([$role->id], $users[0]->roles()->pluck('id')->all());

        // Test force deletion
        $users[0]->forceDelete();
        $this->assertSame(1, Role::count()); // Role has no been cascade
        $this->assertSame(2, $role->users()->count());
    }

    public function testScopeForUser(): void
    {
        $roles = Role::factory()->count(3)->create();

        /** @var User */
        $user = User::factory()->hasAttached($roles[1])->create();

        $this->assertSame(3, Role::count());

        // Test scope with id
        $this->assertSame(1, Role::forUser($user->id)->count());
        $this->assertSame([$roles[1]->id], Role::forUser($user->id)->pluck('id')->all());

        // Test scope with Model
        $this->assertSame(1, Role::forUser($user)->count());
        $this->assertSame([$roles[1]->id], Role::forUser($user)->pluck('id')->all());
    }

    public function testUserScopeForRole(): void
    {
        /** @var Role */
        $roleA = Role::factory()->state(['slug' => 'foo'])->create();

        /** @var Role */
        $roleB = Role::factory()->state(['slug' => 'bar'])->create();

        /** @var User */
        $userFoo = User::factory()->hasAttached($roleA)->create();

        /** @var User */
        $userBar = User::factory()->hasAttached($roleB)->create();

        // Test scope with id
        $users = User::forRole($roleA->id)->get();
        $this->assertSame(1, $users->count());
        $this->assertSame([$userFoo->id], $users->pluck('id')->all());

        // Test scope with Model
        $users = User::forRole($roleB)->get();
        $this->assertSame(1, $users->count());
        $this->assertSame([$userBar->id], $users->pluck('id')->all());
    }
}
