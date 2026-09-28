<?php

declare(strict_types=1);

/*
 * UserFrosting Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-admin
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Admin\Tests\Controller\Permission;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use UserFrosting\Sprinkle\Account\Database\Models\Activity;
use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Testing\WithTestUser;
use UserFrosting\Sprinkle\Admin\Tests\AdminTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

class PermissionActivitySprunjeTest extends AdminTestCase
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
        $request = $this->createJsonRequest('GET', '/api/permissions/p/99/activities');
        $response = $this->handleRequest($request);

        $this->assertJsonResponse('Login Required', $response, 'title');
        $this->assertResponseStatus(401, $response);
    }

    public function testPageForNoPermission(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user);

        $request = $this->createJsonRequest('GET', '/api/permissions/p/99/activities');
        $response = $this->handleRequest($request);

        $this->assertJsonResponse([
            'title'       => 'Not Found',
            'description' => 'Permission not found',
            'status'      => 404,
        ], $response);
        $this->assertResponseStatus(404, $response);
    }

    public function testPageForForbiddenException(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user);
        /** @var Permission */
        $permission = Permission::factory()->create();

        $request = $this->createJsonRequest('GET', '/api/permissions/p/' . $permission->id . '/activities');
        $response = $this->handleRequest($request);

        $this->assertJsonResponse('Access Denied', $response, 'title');
        $this->assertResponseStatus(403, $response);
    }

    public function testPage(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['uri_permissions']);
        /** @var Permission */
        $permission = Permission::factory()->create();

        $request = $this->createRequest('GET', '/api/permissions/p/' . $permission->id . '/activities');
        $response = $this->handleRequest($request);

        $this->assertResponseStatus(200, $response);
        $this->assertNotEmpty((string) $response->getBody());
    }

    public function testPageOnlyReturnsActivitiesForSelectedPermission(): void
    {
        /** @var User */
        $user = User::factory()->create();
        /** @var Permission */
        $permission = Permission::factory()->create();
        /** @var Permission */
        $otherPermission = Permission::factory()->create();
        /** @var Activity */
        $selectedActivity = Activity::factory()->create([
            'context_type' => $permission->getMorphClass(),
            'context_id'   => $permission->getKey(),
            'subject_type' => $permission->getMorphClass(),
            'subject_id'   => $permission->getKey(),
        ]);
        Activity::factory()->create([
            'context_type' => $otherPermission->getMorphClass(),
            'context_id'   => $otherPermission->getKey(),
            'subject_type' => $otherPermission->getMorphClass(),
            'subject_id'   => $otherPermission->getKey(),
        ]);
        $this->actAsUser($user, permissions: ['uri_permissions']);

        $request = $this->createRequest('GET', '/api/permissions/p/' . $permission->id . '/activities');
        $response = $this->handleRequest($request);
        $payload = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertResponseStatus(200, $response);
        $activityIds = array_column($payload['rows'], 'id');
        $this->assertContains($selectedActivity->getKey(), $activityIds);
        $this->assertNotContains(
            Activity::where('context_id', $otherPermission->getKey())->value('id'),
            $activityIds
        );
    }
}