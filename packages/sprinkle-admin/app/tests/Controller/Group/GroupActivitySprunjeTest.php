<?php

declare(strict_types=1);

/*
 * UserFrosting Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-admin
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Admin\Tests\Controller\Group;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use UserFrosting\Sprinkle\Account\Database\Models\Activity;
use UserFrosting\Sprinkle\Account\Database\Models\Group;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Testing\WithTestUser;
use UserFrosting\Sprinkle\Admin\Tests\AdminTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

class GroupActivitySprunjeTest extends AdminTestCase
{
    use RefreshDatabase;
    use WithTestUser;
    use MockeryPHPUnitIntegration;

    /**
     * Setup test database for controller tests
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
    }

    public function testPageForGuestUser(): void
    {
        $request = $this->createJsonRequest('GET', '/api/groups/g/foo/activities');
        $response = $this->handleRequest($request);

        $this->assertJsonResponse('Login Required', $response, 'title');
        $this->assertResponseStatus(401, $response);
    }

    public function testPageForNoGroup(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user);

        $request = $this->createJsonRequest('GET', '/api/groups/g/foo/activities');
        $response = $this->handleRequest($request);

        $this->assertJsonResponse([
            'title'       => 'Not Found',
            'description' => 'Group not found',
            'status'      => 404,
        ], $response);
        $this->assertResponseStatus(404, $response);
    }

    public function testPageForForbiddenException(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user);
        /** @var Group */
        $group = Group::factory()->create();

        $request = $this->createJsonRequest('GET', '/api/groups/g/' . $group->slug . '/activities');
        $response = $this->handleRequest($request);

        $this->assertJsonResponse('Access Denied', $response, 'title');
        $this->assertResponseStatus(403, $response);
    }

    public function testPage(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['view_group_field']);
        /** @var Group */
        $group = Group::factory()->create();

        $request = $this->createRequest('GET', '/api/groups/g/' . $group->slug . '/activities');
        $response = $this->handleRequest($request);

        $this->assertResponseStatus(200, $response);
        $this->assertNotEmpty((string) $response->getBody());
    }

    public function testPageOnlyReturnsActivitiesForSelectedGroup(): void
    {
        /** @var User */
        $user = User::factory()->create();
        /** @var Group */
        $group = Group::factory()->create();
        /** @var Group */
        $otherGroup = Group::factory()->create();
        /** @var Activity */
        $selectedActivity = Activity::factory()->create([
            'context_type' => $group->getMorphClass(),
            'context_id'   => $group->getKey(),
            'subject_type' => $group->getMorphClass(),
            'subject_id'   => $group->getKey(),
        ]);
        Activity::factory()->create([
            'context_type' => $otherGroup->getMorphClass(),
            'context_id'   => $otherGroup->getKey(),
            'subject_type' => $otherGroup->getMorphClass(),
            'subject_id'   => $otherGroup->getKey(),
        ]);
        $this->actAsUser($user, permissions: ['view_group_field']);

        $request = $this->createRequest('GET', '/api/groups/g/' . $group->slug . '/activities');
        $response = $this->handleRequest($request);
        $payload = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertResponseStatus(200, $response);
        $activityIds = array_column($payload['rows'], 'id');
        $this->assertContains($selectedActivity->getKey(), $activityIds);
        $this->assertNotContains(
            Activity::where('context_id', $otherGroup->getKey())->value('id'),
            $activityIds
        );
    }
}