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
use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\Admin\Sprunje\RoleSprunje;
use UserFrosting\Sprinkle\Admin\Tests\AdminTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

/**
 * Tests a RoleSprunje.
 */
class RoleSprunjeTest extends AdminTestCase
{
    use RefreshDatabase;

    /** @var EloquentCollection<int, Role> */
    protected EloquentCollection $roles;

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
        $this->roles = Role::factory()
                        ->count(3)
                        ->sequence(fn ($sequence) => ['name' => 'Name ' . $sequence->index])
                        ->create();
    }

    public function testBaseSprunje(): void
    {
        /** @var RoleSprunje */
        $sprunje = $this->getService(RoleSprunje::class);
        $data = $sprunje->getArray();

        $this->assertEquals(3, $data['count']);
        $this->assertEquals(3, $data['count_filtered']);
        $this->assertCount(3, $data['rows']); // @phpstan-ignore-line
        $this->assertEquals([], $data['listable']);
    }

    public function testWithInfoFilter(): void
    {
        /** @var RoleSprunje */
        $sprunje = $this->getService(RoleSprunje::class);
        $sprunje->setOptions([
            'filters' => ['info' => 'Name 1'],
        ]);
        $data = $sprunje->getArray();

        $this->assertEquals(3, $data['count']);
        $this->assertEquals(1, $data['count_filtered']);
        $this->assertCount(1, $data['rows']); // @phpstan-ignore-line
    }
}
