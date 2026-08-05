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
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Admin\Sprunje\ActivitySprunje;
use UserFrosting\Sprinkle\Admin\Tests\AdminTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

/**
 * Tests a ActivitySprunje.
 */
class ActivitySprunjeTest extends AdminTestCase
{
    use RefreshDatabase;

    /** @var EloquentCollection<int, User> */
    protected EloquentCollection $users;

    public function setUp(): void
    {
        parent::setUp();

        // Set database up.
        $this->refreshDatabase();
        $this->createData();
    }

    protected function createData(): void
    {
        $this->users = User::factory()
                    ->count(2)
                    ->sequence(fn ($sequence) => [
                        'first_name' => 'First ' . $sequence->index,
                        'last_name'  => 'Name ' . $sequence->index,
                    ])
                    ->hasActivities(3) // @phpstan-ignore-line
                    ->create();
    }

    public function testBaseSprunje(): void
    {
        /** @var ActivitySprunje */
        $sprunje = $this->getService(ActivitySprunje::class);
        $data = $sprunje->getArray();

        $this->assertEquals(6, $data['count']);
        $this->assertEquals(6, $data['count_filtered']);
        $this->assertCount(6, $data['rows']); // @phpstan-ignore-line
        $this->assertEquals([], $data['listable']);
    }

    public function testWithPagination(): void
    {
        /** @var ActivitySprunje */
        $sprunje = $this->getService(ActivitySprunje::class);
        $sprunje->setOptions([
            'size' => 1,
            'page' => 1, // First page is 0, so second row will be displayed.
        ]);
        $data = $sprunje->getArray();

        $this->assertEquals(6, $data['count']);
        $this->assertEquals(6, $data['count_filtered']);
        $this->assertCount(1, $data['rows']); // @phpstan-ignore-line
    }

    public function testWithUserSort(): void
    {
        /** @var ActivitySprunje */
        $sprunje = $this->getService(ActivitySprunje::class);
        $sprunje->setOptions([
            'sorts' => ['user' => 'desc'],
        ]);
        $data = $sprunje->getArray();

        $this->assertEquals(6, $data['count']);
        $this->assertEquals(6, $data['count_filtered']);
        $this->assertCount(6, $data['rows']); // @phpstan-ignore-line
        $this->assertEquals($this->users[1]->id, $data['rows'][0]['user_id']); // @phpstan-ignore-line
        $this->assertEquals($this->users[1]->id, $data['rows'][1]['user_id']); // @phpstan-ignore-line
        $this->assertEquals($this->users[1]->id, $data['rows'][2]['user_id']); // @phpstan-ignore-line
        $this->assertEquals($this->users[0]->id, $data['rows'][3]['user_id']); // @phpstan-ignore-line
        $this->assertEquals($this->users[0]->id, $data['rows'][4]['user_id']); // @phpstan-ignore-line
        $this->assertEquals($this->users[0]->id, $data['rows'][5]['user_id']); // @phpstan-ignore-line

        $sprunje->setOptions([
            'sorts' => ['user' => 'asc'],
        ]);
        $data = $sprunje->getArray();
        $this->assertEquals($this->users[0]->id, $data['rows'][0]['user_id']); // @phpstan-ignore-line
        $this->assertEquals($this->users[0]->id, $data['rows'][1]['user_id']); // @phpstan-ignore-line
        $this->assertEquals($this->users[0]->id, $data['rows'][2]['user_id']); // @phpstan-ignore-line
        $this->assertEquals($this->users[1]->id, $data['rows'][3]['user_id']); // @phpstan-ignore-line
        $this->assertEquals($this->users[1]->id, $data['rows'][4]['user_id']); // @phpstan-ignore-line
        $this->assertEquals($this->users[1]->id, $data['rows'][5]['user_id']); // @phpstan-ignore-line
    }

    public function testWithOccurredAtSort(): void
    {
        /** @var ActivitySprunje */
        $sprunje = $this->getService(ActivitySprunje::class);
        $sprunje->setOptions([
            'sorts' => ['occurred_at' => 'desc'],
        ]);
        $data = $sprunje->getArray();

        $this->assertEquals(6, $data['count']);
        $this->assertEquals(6, $data['count_filtered']);
        $this->assertCount(6, $data['rows']); // @phpstan-ignore-line
        $this->assertEquals(6, $data['rows'][0]['id']); // @phpstan-ignore-line

        $sprunje->setOptions([
            'sorts' => ['occurred_at' => 'asc'],
        ]);
        $data = $sprunje->getArray();
        $this->assertEquals(1, $data['rows'][0]['id']); // @phpstan-ignore-line
    }

    public function testWithMultipleSorts(): void
    {
        /** @var ActivitySprunje */
        $sprunje = $this->getService(ActivitySprunje::class);
        $sprunje->setOptions([
            'sorts' => [
                'occurred_at' => 'desc',
                'user'        => 'asc'
            ],
        ]);
        $data = $sprunje->getArray();

        $this->assertEquals(6, $data['count']);
        $this->assertEquals(6, $data['count_filtered']);
        $this->assertCount(6, $data['rows']); // @phpstan-ignore-line
        $this->assertEquals(6, $data['rows'][0]['id']); // @phpstan-ignore-line
    }

    public function testWithUserFilter(): void
    {
        /** @var ActivitySprunje */
        $sprunje = $this->getService(ActivitySprunje::class);
        $sprunje->setOptions([
            'filters' => ['user' => $this->users[0]->email], // @phpstan-ignore-line
        ]);
        $data = $sprunje->getArray();

        $this->assertEquals(6, $data['count']);
        $this->assertEquals(3, $data['count_filtered']);
        $this->assertCount(3, $data['rows']); // @phpstan-ignore-line

        // Filter by name
        $sprunje->setOptions([
            'filters' => ['user' => $this->users[0]->first_name], // @phpstan-ignore-line
        ]);
        $this->assertEquals(3, $sprunje->getArray()['count_filtered']);

        // Filter by last name
        $sprunje->setOptions([
            'filters' => ['user' => 'Name '], // @phpstan-ignore-line
        ]);
        $this->assertEquals(6, $sprunje->getArray()['count_filtered']);
    }
}
