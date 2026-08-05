<?php

declare(strict_types=1);

/*
 * UserFrosting Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-admin
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Admin\Tests\Sprunje;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\Sequence;
use UserFrosting\I18n\Translator;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\PermissionInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\RoleInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Admin\Sprunje\PermissionUserSprunje;
use UserFrosting\Sprinkle\Admin\Sprunje\UserSprunje;
use UserFrosting\Sprinkle\Admin\Tests\AdminTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

/**
 * Tests a UserSprunje.
 */
class PermissionUserSprunjeTest extends AdminTestCase
{
    use RefreshDatabase;

    /** @var EloquentCollection<int, User> */
    protected EloquentCollection $users;
    protected RoleInterface $role;
    protected PermissionInterface $permission;

    public function setUp(): void
    {
        parent::setUp();

        // Set database up.
        $this->refreshDatabase();
        $this->createData();
    }

    protected function createData(): void
    {
        // @phpstan-ignore-next-line
        $this->users = User::factory()
                ->count(3)
                ->sequence(fn ($sequence) => [
                    'first_name' => 'First ' . $sequence->index,
                    'last_name'  => 'Name ' . $sequence->index,
                ])
                ->state(new Sequence(
                    ['flag_enabled' => 1, 'flag_verified' => 1],
                    ['flag_enabled' => 0, 'flag_verified' => 1],
                    ['flag_enabled' => 1, 'flag_verified' => 0],
                ))
                ->create();

        /** @var Permission */
        $permission = Permission::factory()->create();
        $this->permission = $permission;

        /** @var Role */
        $role = Role::factory()->create();
        $role->permissions()->attach($this->permission);
        $role->users()->attach($this->users);
        $role->save();
        $this->role = $role;
    }

    public function testBaseSprunje(): void
    {
        /** @var Translator */
        $translator = $this->getService(Translator::class);
        $sprunje = new PermissionUserSprunje($this->permission, $translator);
        $data = $sprunje->getArray();

        $this->assertEquals(3, $data['count']);
        $this->assertEquals(3, $data['count_filtered']);
        $this->assertCount(3, $data['rows']); // @phpstan-ignore-line
        $this->assertEquals([
            'status' => [
                ['value' => 'active', 'text' => 'Active'],
                ['value' => 'unactivated', 'text' => 'Unactivated'],
                ['value' => 'disabled', 'text' => 'Disabled'],
            ]
        ], $data['listable']);
    }

    public function testWithPagination(): void
    {
        /** @var Translator */
        $translator = $this->getService(Translator::class);
        $sprunje = new PermissionUserSprunje($this->permission, $translator);
        $sprunje->setOptions([
            'size' => 1,
            'page' => 1, // First page is 0, so second row will be displayed.
        ]);
        $data = $sprunje->getArray();

        $this->assertEquals(3, $data['count']);
        $this->assertEquals(3, $data['count_filtered']);
        $this->assertCount(1, $data['rows']); // @phpstan-ignore-line
    }

    public function testForFilterName(): void
    {
        /** @var Translator */
        $translator = $this->getService(Translator::class);
        $sprunje = new PermissionUserSprunje($this->permission, $translator);
        $sprunje->setOptions([
            'filters' => ['name' => $this->users[0]->email], // @phpstan-ignore-line
        ]);
        $data = $sprunje->getArray();

        $this->assertEquals(3, $data['count']);
        $this->assertEquals(1, $data['count_filtered']);
        $this->assertCount(1, $data['rows']); // @phpstan-ignore-line

        // Filter by name
        $sprunje->setOptions([
            'filters' => ['name' => $this->users[0]->first_name], // @phpstan-ignore-line
        ]);
        $this->assertEquals(1, $sprunje->getArray()['count_filtered']);

        // Filter by last name
        $sprunje->setOptions([
            'filters' => ['name' => 'Name '], // @phpstan-ignore-line
        ]);
        $this->assertEquals(3, $sprunje->getArray()['count_filtered']);
    }

    public function testForFilterStatus(): void
    {
        /** @var Translator */
        $translator = $this->getService(Translator::class);
        $sprunje = new PermissionUserSprunje($this->permission, $translator);

        // Filter by disabled
        $sprunje->setOptions([
            'filters' => ['status' => 'disabled'],
        ]);
        $data = $sprunje->getArray();
        $this->assertEquals(3, $data['count']);
        $this->assertEquals(1, $data['count_filtered']);
        $this->assertCount(1, $data['rows']); // @phpstan-ignore-line
        $this->assertEquals(2, $data['rows'][0]['id']); // @phpstan-ignore-line

        // Filter by unactivated
        $sprunje->setOptions([
            'filters' => ['status' => 'unactivated'],
        ]);
        $data = $sprunje->getArray();
        $this->assertEquals(1, $data['count_filtered']);
        $this->assertEquals(3, $data['rows'][0]['id']); // @phpstan-ignore-line

        // Filter by active
        $sprunje->setOptions([
            'filters' => ['status' => 'active'],
        ]);
        $data = $sprunje->getArray();
        $this->assertEquals(1, $data['count_filtered']);
        $this->assertEquals(1, $data['rows'][0]['id']); // @phpstan-ignore-line
    }

    public function testForSortName(): void
    {
        /** @var Translator */
        $translator = $this->getService(Translator::class);
        $sprunje = new PermissionUserSprunje($this->permission, $translator);

        $sprunje->setOptions([
            'sorts' => ['name' => 'desc'],
        ]);
        $data = $sprunje->getArray();
        $this->assertEquals(3, $data['count']);
        $this->assertEquals(3, $data['count_filtered']);
        $this->assertCount(3, $data['rows']); // @phpstan-ignore-line
        $this->assertEquals($this->users[2]->id, $data['rows'][0]['id']); // @phpstan-ignore-line
        $this->assertEquals($this->users[1]->id, $data['rows'][1]['id']); // @phpstan-ignore-line
        $this->assertEquals($this->users[0]->id, $data['rows'][2]['id']); // @phpstan-ignore-line

        $sprunje->setOptions([
            'sorts' => ['name' => 'asc'],
        ]);
        $data = $sprunje->getArray();
        $this->assertEquals($this->users[0]->id, $data['rows'][0]['id']); // @phpstan-ignore-line
        $this->assertEquals($this->users[1]->id, $data['rows'][1]['id']); // @phpstan-ignore-line
        $this->assertEquals($this->users[2]->id, $data['rows'][2]['id']); // @phpstan-ignore-line
    }

    public function testForSortStatus(): void
    {
        /** @var Translator */
        $translator = $this->getService(Translator::class);
        $sprunje = new PermissionUserSprunje($this->permission, $translator);

        $sprunje->setOptions([
            'sorts' => ['status' => 'desc'],
        ]);
        $data = $sprunje->getArray();
        $this->assertEquals(3, $data['count']);
        $this->assertEquals(3, $data['count_filtered']);
        $this->assertCount(3, $data['rows']); // @phpstan-ignore-line
        $this->assertEquals($this->users[0]->id, $data['rows'][0]['id']); // @phpstan-ignore-line
        $this->assertEquals($this->users[2]->id, $data['rows'][1]['id']); // @phpstan-ignore-line
        $this->assertEquals($this->users[1]->id, $data['rows'][2]['id']); // @phpstan-ignore-line

        $sprunje->setOptions([
            'sorts' => ['status' => 'asc'],
        ]);
        $data = $sprunje->getArray();
        $this->assertEquals($this->users[1]->id, $data['rows'][0]['id']); // @phpstan-ignore-line
        $this->assertEquals($this->users[2]->id, $data['rows'][1]['id']); // @phpstan-ignore-line
        $this->assertEquals($this->users[0]->id, $data['rows'][2]['id']); // @phpstan-ignore-line
    }
}
