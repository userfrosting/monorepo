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
use UserFrosting\Sprinkle\Account\Database\Models\Group;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Testing\WithTestUser;
use UserFrosting\Sprinkle\Admin\Tests\AdminTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

class GroupApiTest extends AdminTestCase
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

    public function testForGuestUser(): void
    {
        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('GET', '/api/groups/g/foo');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse('Login Required', $response, 'title');
        $this->assertResponseStatus(401, $response);
    }

    public function testForForbiddenException(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user);

        /** @var Group */
        $group = Group::factory()->create();

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('GET', '/api/groups/g/' . $group->slug);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse('Access Denied', $response, 'title');
        $this->assertResponseStatus(403, $response);
    }

    public function testForNotFound(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['uri_group']);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('GET', '/api/groups/g/foo');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse('Group not found', $response, 'description');
        $this->assertResponseStatus(404, $response);
    }

    public function testApi(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['uri_group']);

        /** @var Group */
        $group = Group::factory()->create();

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('GET', '/api/groups/g/' . $group->slug);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(200, $response);
        $this->assertJsonStructure([
            'id',
            'slug',
            'name',
            'description',
            'icon',
            'created_at',
            'updated_at',
            'users_count',
        ], $response);
    }

    public function testAccessToOwnGroup(): void
    {
        /** @var Group */
        $group = Group::factory()->create();

        /** @var User */
        $user = User::factory()->create([
            'group_id' => $group->id,
        ]);
        $this->actAsUser($user, permissions: ['uri_group_own']);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('GET', '/api/groups/g/' . $group->slug);
        $response = $this->handleRequest($request);

        // Assert response status
        $this->assertResponseStatus(200, $response);
    }

    public function testAccessDeniedForNotYourGroup(): void
    {
        /** @var Group */
        $group = Group::factory()->create();

        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['uri_group_own']);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('GET', '/api/groups/g/' . $group->slug);
        $response = $this->handleRequest($request);

        // Assert response status
        $this->assertResponseStatus(403, $response);
    }
}
