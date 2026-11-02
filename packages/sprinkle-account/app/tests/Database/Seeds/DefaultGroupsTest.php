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

use UserFrosting\Sprinkle\Account\Database\Models\Group;
use UserFrosting\Sprinkle\Account\Database\Seeds\DefaultGroups;
use UserFrosting\Sprinkle\Account\Tests\AccountTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

/**
 * DefaultGroups Seed Test.
 */
class DefaultGroupsTest extends AccountTestCase
{
    use RefreshDatabase;

    public function testSeed(): void
    {
        // Setup fresh, empty table
        $this->refreshDatabase();

        // Assert initial table state
        $this->assertCount(0, Group::all());

        // Apply seed
        $seed = new DefaultGroups();
        $seed->run();
        $seed->run();

        // Assert new table state
        $this->assertCount(3, Group::all());

        $group = Group::where('slug', 'hippo')->firstOrFail();
        $group->delete();
        $seed->run();

        $this->assertCount(3, Group::all());
        $restoredGroup = Group::withTrashed()->where('slug', 'hippo')->first();
        $this->assertInstanceOf(Group::class, $restoredGroup);
        $this->assertFalse($restoredGroup->trashed());
    }
}
