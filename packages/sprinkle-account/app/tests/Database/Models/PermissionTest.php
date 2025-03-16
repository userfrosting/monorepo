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

use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\PermissionInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\RoleInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Tests\AccountTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

/**
 * PermissionTest Class. Tests the Permission Model.
 */
class PermissionTest extends AccountTestCase
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

    public function testPermission(): void
    {
        // Assert Initial DB state
        $this->assertSame(0, Permission::count());

        $permission = new Permission([
            'name'       => 'Test',
            'slug'       => 'test',
            'conditions' => 'always()',
        ]);
        $permission->save();
        $this->assertInstanceOf(PermissionInterface::class, $permission); // @phpstan-ignore-line

        // Assert new state
        $this->assertSame(1, Permission::count());

        // Get Model and assert it's default properties
        /** @var Permission */
        $fetched = Permission::find($permission->id);
        $this->assertSame('Test', $fetched->name);
        $this->assertSame('test', $fetched->slug);
        $this->assertSame('always()', $fetched->conditions);
        $this->assertSame('', $fetched->description);

        // Delete
        $fetched->delete();

        // Assert new state
        $this->assertSame(0, Permission::count());
    }

    public function testRoleRelation(): void
    {
        /** @var Permission */
        $permission = Permission::factory()->create();

        /** @var Role[] */
        $roles = Role::factory()
            ->count(3)
            ->hasAttached($permission)
            ->create();

        $this->assertContainsOnlyInstancesOf(RoleInterface::class, $permission->roles); // @phpstan-ignore-line
        $this->assertSame(3, $permission->roles()->count());
        $this->assertSame(3, $permission->roles->count());

        // Assert reverse relation
        $this->assertSame(1, $roles[0]->permissions->count());
        $this->assertSame([$permission->id], $roles[0]->permissions()->pluck('id')->all());
    }

    public function testForRoleScope(): void
    {
        /** @var Permission */
        $permissionA = Permission::factory()->create();
        /** @var Permission */
        $permissionB = Permission::factory()->create();

        /** @var Role */
        $roleA = Role::factory()->hasAttached($permissionA)->create();

        /** @var Role */
        $roleB = Role::factory()->hasAttached($permissionB)->create();

        // Assert Using id
        $result = Permission::forRole($roleA->id)->get();
        $this->assertSame([$permissionA->id], $result->pluck('id')->all());

        // Assert Using Model
        $result = Permission::forRole($roleB)->get();
        $this->assertSame([$permissionB->id], $result->pluck('id')->all());

        // Assert negative Using id
        $result = Permission::notForRole($roleA->id)->get();
        $this->assertSame([$permissionB->id], $result->pluck('id')->all());

        // Assert negative Using Model
        $result = Permission::notForRole($roleB)->get();
        $this->assertSame([$permissionA->id], $result->pluck('id')->all());
    }

    public function testUserRoleRelation(): void
    {
        /** @var Permission */
        $permission = Permission::factory()->create();

        /** @var Role */
        $role = Role::factory()->hasAttached($permission)->create();

        /** @var User */
        $user = User::factory()->hasAttached($role)->create();

        $this->assertContainsOnlyInstancesOf(UserInterface::class, $permission->users); // @phpstan-ignore-line
        $this->assertSame(1, $permission->users()->count());
        $this->assertSame([$user->id], $permission->roles->pluck('id')->all());

        // Assert reverse relation
        $this->assertSame(1, $user->permissions()->count());
        $this->assertSame([$permission->id], $user->permissions->pluck('id')->all());

        // Assert cached permissions
        $result = $user->getCachedPermissions();
        $this->assertCount(1, $result);
        $this->assertCount(1, $result[$permission->slug]);
        $this->assertSame([$permission->conditions], $result[$permission->slug]);

        // Add new permission
        /** @var Permission */
        $newPermission = Permission::factory()->hasAttached($role)->create();
        $this->assertSame(2, $user->permissions()->count());

        $this->assertCount(1, $user->getCachedPermissions());
        $result = $user->reloadCachedPermissions()->getCachedPermissions();
        $this->assertCount(2, $result);
        $this->assertSame([$permission->slug, $newPermission->slug], array_keys($result));
    }
}
