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
use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Testing\WithTestUser;
use UserFrosting\Sprinkle\Admin\Tests\AdminTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

class RoleUpdateFieldActionTest extends AdminTestCase
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
        $request = $this->createJsonRequest('PUT', '/api/roles/r/foo/permissions');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse('Login Required', $response, 'title');
        $this->assertResponseStatus(401, $response);
    }

    public function testPageWithNotFoundUser(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('PUT', '/api/roles/r/foo/permissions');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse([
            'title'       => 'Not Found',
            'description' => 'Role not found',
            'status'      => 404,
        ], $response);
        $this->assertResponseStatus(404, $response);
    }

    public function testPostForNoData(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, isMaster: true);

        /** @var Role */
        $role = Role::factory()->create();

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('PUT', '/api/roles/r/' . $role->slug . '/name');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse('Validation error', $response, 'title');
        $this->assertJsonResponse('Please specify a value for <strong>name</strong>.', $response, 'description');
        $this->assertResponseStatus(400, $response);
    }

    public function testPageForNoPermissions(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user);

        /** @var Role */
        $role = Role::factory()->create();

        // Create request with method and url and fetch response
        $data = ['permissions' => []];
        $request = $this->createJsonRequest('PUT', '/api/roles/r/' . $role->slug . '/permissions', $data);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse('Access Denied', $response, 'title');
        $this->assertResponseStatus(403, $response);
    }

    public function testPostForName(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['update_role_field']);

        /** @var Role */
        $role = Role::factory()->create();

        // Create request with method and url and fetch response
        $data = [
            'name'  => 'New Foo',
        ];
        $request = $this->createJsonRequest('PUT', '/api/roles/r/' . $role->slug . '/name', $data);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(200, $response);
        $this->assertJsonResponse([
            'title'       => 'Details updated for role <strong>New Foo</strong>',
            'description' => '',
        ], $response);

        // Make sure the role has the new name.
        $role->refresh();
        $this->assertSame('New Foo', $role->name);
    }

    public function testPostForPermission(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['update_role_field']);

        /** @var Role */
        $role = Role::factory()->create();

        /** @var Permission */
        $permissions = Permission::factory()->count(2)->create();

        /*
        N.B.: Expected value format, passed from uf-collection :
        value[0][permission_id]: 1
        value[1][permission_id]: 2
        value[2][permission_id]: 3
        */

        // @phpstan-ignore-next-line
        $ids = $permissions->map(function ($item) {
            return ['permission_id' => $item->id];
        })->toArray();

        // Create request with method and url and fetch response
        $data = ['permissions' => $ids];
        $request = $this->createJsonRequest('PUT', '/api/roles/r/' . $role->slug . '/permissions', $data);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(200, $response);
        $this->assertJsonResponse([
            'title'       => 'Permissions updated for role <strong>' . $role->name . '</strong>',
            'description' => '',
        ], $response);

        // Make sure the role has the new permissions.
        $role->refresh();
        $this->assertCount(2, $role->permissions);
    }

    public function testPostForRemovingAllRoles(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['update_role_field']);

        /** @var Role */
        $role = Role::factory()->has(Permission::factory())->create();
        $this->assertCount(1, $role->permissions); // Default role above.

        // Create request with method and url and fetch response
        // uf-collection will pass no data when removing all roles_id.
        $data = ['permissions' => []];
        $request = $this->createJsonRequest('PUT', '/api/roles/r/' . $role->slug . '/permissions', $data);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(200, $response);
        $this->assertJsonResponse([
            'title'       => 'Permissions updated for role <strong>' . $role->name . '</strong>',
            'description' => '',
        ], $response);

        // Make sure the user has the new roles.
        $role->refresh();
        $this->assertCount(0, $role->permissions);
    }

    public function testPostForMissingValueArgument(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['update_role_field']);

        /** @var Role */
        $role = Role::factory()->has(Permission::factory())->create();
        $this->assertCount(1, $role->permissions); // Default role above.

        // Create request with method and url and fetch response
        // uf-collection will pass no data when removing all roles_id.
        $request = $this->createJsonRequest('PUT', '/api/roles/r/' . $role->slug . '/permissions');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(400, $response);
        $this->assertJsonResponse('Please specify a value for <strong>permissions</strong>.', $response, 'description');
    }

    public function testPageForFailedValidation(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['update_role_field']);

        /** @var Role */
        $role = Role::factory()->create();

        // Create request with method and url and fetch response
        $data = ['permissions' => 'notAnArray'];
        $request = $this->createJsonRequest('PUT', '/api/roles/r/' . $role->slug . '/permissions', $data);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse('The values for <strong>permissions</strong> must be in an array.', $response, 'description');
        $this->assertResponseStatus(400, $response);
    }
}
