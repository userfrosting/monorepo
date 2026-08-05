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
use UserFrosting\Sprinkle\Account\Database\Models\Group;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Admin\Sprunje\GroupSprunje;
use UserFrosting\Sprinkle\Admin\Sprunje\RoleSprunje;
use UserFrosting\Sprinkle\Admin\Tests\AdminTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

/**
 * Tests a RoleSprunje.
 */
class GroupSprunjeTest extends AdminTestCase
{
    use RefreshDatabase;

    /** @var EloquentCollection<int, Group> */
    protected EloquentCollection $groups;

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
        $this->groups = Group::factory()
                        ->count(3)
                        ->create();

        // Add some users to the groups
        User::factory()->count(2)
            ->for($this->groups[0])
            ->create();
    }

    public function testBaseSprunje(): void
    {
        /** @var GroupSprunje */
        $sprunje = $this->getService(GroupSprunje::class);
        $data = $sprunje->getArray();

        $this->assertEquals(3, $data['count']);
        $this->assertEquals(3, $data['count_filtered']);
        $this->assertCount(3, $data['rows']); // @phpstan-ignore-line
        $this->assertEquals([], $data['listable']);

        // Test User Count
        $this->assertEquals($this->groups[0]['id'], $data['rows'][0]['id']);
        $this->assertEquals(2, $data['rows'][0]['users_count']);
    }
}
