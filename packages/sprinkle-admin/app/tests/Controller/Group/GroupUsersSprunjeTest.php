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

class GroupUsersSprunjeTest extends AdminTestCase
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
        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('GET', '/api/groups/g/foo/users');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse('Login Required', $response, 'title');
        $this->assertResponseStatus(401, $response);
    }

    public function testPageForNoGroup(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('GET', '/api/groups/g/foo/users');
        $response = $this->handleRequest($request);

        // Assert response status & body
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

        // Create Group
        /** @var Group */
        $group = Group::factory()->create();

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('GET', '/api/groups/g/' . $group->slug . '/users');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse('Access Denied', $response, 'title');
        $this->assertResponseStatus(403, $response);
    }

    public function testPage(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['view_group_field']);

        // Create Group
        /** @var Group */
        $group = Group::factory()->create();

        // Create request with method and url and fetch response
        $request = $this->createRequest('GET', '/api/groups/g/' . $group->slug . '/users');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(200, $response);
        $this->assertJsonStructure([
            'count',
            'count_filtered',
            'rows',
            'listable',
            'sortable',
            'filterable',
        ], $response);
    }

    public function testPageForYourGroup(): void
    {
        // Create Group
        /** @var Group */
        $group = Group::factory()->create();

        /** @var User */
        $user = User::factory()->create([
            'group_id' => $group->id,
        ]);
        $this->actAsUser($user, permissions: ['view_group_field_own']);

        // Create request with method and url and fetch response
        $request = $this->createRequest('GET', '/api/groups/g/' . $group->slug . '/users');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(200, $response);
    }

    public function testPageForForbiddenExceptionNotYourGroup(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['view_group_field_own']);

        // Create Group
        /** @var Group */
        $group = Group::factory()->create();

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('GET', '/api/groups/g/' . $group->slug . '/users');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse('Access Denied', $response, 'title');
        $this->assertResponseStatus(403, $response);
    }
}
