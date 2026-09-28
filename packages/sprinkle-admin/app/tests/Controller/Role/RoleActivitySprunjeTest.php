<?php

declare(strict_types=1);

/*
 * UserFrosting Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-admin
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Admin\Tests\Controller\Role;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use UserFrosting\Sprinkle\Account\Database\Models\Activity;
use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Testing\WithTestUser;
use UserFrosting\Sprinkle\Admin\Tests\AdminTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

class RoleActivitySprunjeTest extends AdminTestCase
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
        $request = $this->createJsonRequest('GET', '/api/roles/r/foo/activities');
        $response = $this->handleRequest($request);

        $this->assertJsonResponse('Login Required', $response, 'title');
        $this->assertResponseStatus(401, $response);
    }

    public function testPageForNoRole(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user);

        $request = $this->createJsonRequest('GET', '/api/roles/r/foo/activities');
        $response = $this->handleRequest($request);

        $this->assertJsonResponse([
            'title'       => 'Not Found',
            'description' => 'Role not found',
            'status'      => 404,
        ], $response);
        $this->assertResponseStatus(404, $response);
    }

    public function testPageForForbiddenException(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user);
        /** @var Role */
        $role = Role::factory()->create();

        $request = $this->createJsonRequest('GET', '/api/roles/r/' . $role->slug . '/activities');
        $response = $this->handleRequest($request);

        $this->assertJsonResponse('Access Denied', $response, 'title');
        $this->assertResponseStatus(403, $response);
    }

    public function testPage(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['view_role_field']);
        /** @var Role */
        $role = Role::factory()->create();

        $request = $this->createRequest('GET', '/api/roles/r/' . $role->slug . '/activities');
        $response = $this->handleRequest($request);

        $this->assertResponseStatus(200, $response);
        $this->assertNotEmpty((string) $response->getBody());
    }

    public function testPageOnlyReturnsActivitiesForSelectedRole(): void
    {
        /** @var User */
        $user = User::factory()->create();
        /** @var Role */
        $role = Role::factory()->create();
        /** @var Role */
        $otherRole = Role::factory()->create();
        /** @var Activity */
        $selectedActivity = Activity::factory()->create([
            'context_type' => $role->getMorphClass(),
            'context_id'   => $role->getKey(),
            'subject_type' => $role->getMorphClass(),
            'subject_id'   => $role->getKey(),
        ]);
        Activity::factory()->create([
            'context_type' => $otherRole->getMorphClass(),
            'context_id'   => $otherRole->getKey(),
            'subject_type' => $otherRole->getMorphClass(),
            'subject_id'   => $otherRole->getKey(),
        ]);
        $this->actAsUser($user, permissions: ['view_role_field']);

        $request = $this->createRequest('GET', '/api/roles/r/' . $role->slug . '/activities');
        $response = $this->handleRequest($request);
        $payload = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertResponseStatus(200, $response);
        $activityIds = array_column($payload['rows'], 'id');
        $this->assertContains($selectedActivity->getKey(), $activityIds);
        $this->assertNotContains(
            Activity::where('context_id', $otherRole->getKey())->value('id'),
            $activityIds
        );
    }
}