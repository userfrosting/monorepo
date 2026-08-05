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
use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Sprinkle\Admin\Sprunje\PermissionSprunje;
use UserFrosting\Sprinkle\Admin\Tests\AdminTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

/**
 * Tests a PermissionSprunje.
 */
class PermissionSprunjeTest extends AdminTestCase
{
    use RefreshDatabase;

    /** @var EloquentCollection<int, Permission> */
    protected EloquentCollection $permissions;

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
        $this->permissions = Permission::factory()
                        ->count(3)
                        ->sequence(fn ($sequence) => ['slug' => 'name_' . $sequence->index])
                        ->create();
    }

    public function testBaseSprunje(): void
    {
        /** @var PermissionSprunje */
        $sprunje = $this->getService(PermissionSprunje::class);
        $data = $sprunje->getArray();

        $this->assertEquals(3, $data['count']);
        $this->assertEquals(3, $data['count_filtered']);
        $this->assertCount(3, $data['rows']); // @phpstan-ignore-line
        $this->assertEquals([], $data['listable']);
    }

    public function testWithInfoFilter(): void
    {
        /** @var PermissionSprunje */
        $sprunje = $this->getService(PermissionSprunje::class);
        $sprunje->setOptions([
            'filters' => ['info' => 'name_1'],
        ]);
        $data = $sprunje->getArray();

        $this->assertEquals(3, $data['count']);
        $this->assertEquals(1, $data['count_filtered']);
        $this->assertCount(1, $data['rows']); // @phpstan-ignore-line
    }

    public function testForSortProperties(): void
    {
        /** @var PermissionSprunje */
        $sprunje = $this->getService(PermissionSprunje::class);

        $sprunje->setOptions([
            'sorts' => ['properties' => 'desc'],
        ]);
        $data = $sprunje->getArray();
        $this->assertEquals(3, $data['count']);
        $this->assertEquals(3, $data['count_filtered']);
        $this->assertCount(3, $data['rows']); // @phpstan-ignore-line
        $this->assertEquals($this->permissions[2]->id, $data['rows'][0]['id']); // @phpstan-ignore-line
        $this->assertEquals($this->permissions[1]->id, $data['rows'][1]['id']); // @phpstan-ignore-line
        $this->assertEquals($this->permissions[0]->id, $data['rows'][2]['id']); // @phpstan-ignore-line

        $sprunje->setOptions([
            'sorts' => ['properties' => 'asc'],
        ]);
        $data = $sprunje->getArray();
        $this->assertEquals($this->permissions[0]->id, $data['rows'][0]['id']); // @phpstan-ignore-line
        $this->assertEquals($this->permissions[1]->id, $data['rows'][1]['id']); // @phpstan-ignore-line
        $this->assertEquals($this->permissions[2]->id, $data['rows'][2]['id']); // @phpstan-ignore-line
    }
}
