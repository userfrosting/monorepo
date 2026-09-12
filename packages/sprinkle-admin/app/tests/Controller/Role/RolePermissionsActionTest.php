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
use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Testing\WithTestUser;
use UserFrosting\Sprinkle\Admin\Log\RoleActivityTypes;
use UserFrosting\Sprinkle\Admin\Tests\AdminTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

class RolePermissionsActionTest extends AdminTestCase
{
    use RefreshDatabase;
    use WithTestUser;
    use MockeryPHPUnitIntegration;

    public function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
    }

    public function testPageForGuestUser(): void
    {
        $request = $this->createJsonRequest('PUT', '/api/roles/r/foo/permissions');
        $response = $this->handleRequest($request);

        $this->assertJsonResponse('Login Required', $response, 'title');
        $this->assertResponseStatus(401, $response);
    }

    public function testPageWithNotFoundRole(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user);

        $request = $this->createJsonRequest('PUT', '/api/roles/r/foo/permissions');
        $response = $this->handleRequest($request);

        $this->assertJsonResponse([
            'title'       => 'Not Found',
            'description' => 'Role not found',
            'status'      => 404,
        ], $response);
        $this->assertResponseStatus(404, $response);
    }

    public function testPageForNoPermissions(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user);

        /** @var Role */
        $role = Role::factory()->create();

        $request = $this->createJsonRequest(
            'PUT',
            '/api/roles/r/' . $role->slug . '/permissions',
            ['permissions' => []]
        );
        $response = $this->handleRequest($request);

        $this->assertJsonResponse('Access Denied', $response, 'title');
        $this->assertResponseStatus(403, $response);
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
         * N.B.: Expected value format, passed from uf-collection:
         * value[0][permission_id]: 1
         * value[1][permission_id]: 2
         * value[2][permission_id]: 3
         */
        // @phpstan-ignore-next-line
        $ids = $permissions->map(function ($item) {
            return ['permission_id' => $item->id];
        })->toArray();

        $request = $this->createJsonRequest(
            'PUT',
            '/api/roles/r/' . $role->slug . '/permissions',
            ['permissions' => $ids]
        );
        $response = $this->handleRequest($request);

        $this->assertResponseStatus(200, $response);
        $this->assertJsonResponse([
            'title'       => 'Permissions updated for role <strong>' . $role->name . '</strong>',
            'description' => '',
        ], $response);

        $role->refresh();
        $this->assertCount(2, $role->permissions);

        /** @var Activity|null $activity */
        $activity = Activity::query()
            ->where('type', RoleActivityTypes::UPDATE_PERMISSIONS->value)
            ->where('subject_id', $role->id)
            ->first();
        $this->assertNotNull($activity);
        $metadata = $activity->metadata;
        $this->assertIsArray($metadata);
        $this->assertSame($permissions[0]->name . ', ' . $permissions[1]->name, $metadata['added_permissions']);
        $this->assertSame('No permission', $metadata['removed_permissions']);
        $this->assertNull($activity->properties);
    }

    public function testPostForNumericPermissionIds(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['update_role_field']);

        /** @var Role */
        $role = Role::factory()->create();

        /** @var Permission */
        $permissions = Permission::factory()->count(2)->create();
        $ids = [$permissions[0]->id, $permissions[1]->id];

        $request = $this->createJsonRequest(
            'PUT',
            '/api/roles/r/' . $role->slug . '/permissions',
            ['permissions' => $ids]
        );
        $response = $this->handleRequest($request);

        $this->assertResponseStatus(200, $response);
        $role->refresh();
        $this->assertCount(2, $role->permissions);
    }

    public function testPostForRemovingAllPermissions(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['update_role_field']);

        /** @var Role */
        $role = Role::factory()->has(Permission::factory())->create();
        $this->assertCount(1, $role->permissions);

        $request = $this->createJsonRequest(
            'PUT',
            '/api/roles/r/' . $role->slug . '/permissions',
            ['permissions' => []]
        );
        $response = $this->handleRequest($request);

        $this->assertResponseStatus(200, $response);
        $this->assertJsonResponse([
            'title'       => 'Permissions updated for role <strong>' . $role->name . '</strong>',
            'description' => '',
        ], $response);

        $role->refresh();
        $this->assertCount(0, $role->permissions);

        /** @var Activity|null $activity */
        $activity = Activity::query()
            ->where('type', RoleActivityTypes::UPDATE_PERMISSIONS->value)
            ->where('subject_id', $role->id)
            ->first();
        $this->assertNotNull($activity);
        $metadata = $activity->metadata;
        $this->assertIsArray($metadata);
        $this->assertSame('No permission', $metadata['added_permissions']);
        $this->assertNotSame('No permission', $metadata['removed_permissions']);
        $this->assertNull($activity->properties);
    }

    public function testPostForMissingValueArgument(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['update_role_field']);

        /** @var Role */
        $role = Role::factory()->has(Permission::factory())->create();

        $request = $this->createJsonRequest('PUT', '/api/roles/r/' . $role->slug . '/permissions');
        $response = $this->handleRequest($request);

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

        $request = $this->createJsonRequest(
            'PUT',
            '/api/roles/r/' . $role->slug . '/permissions',
            ['permissions' => 'notAnArray']
        );
        $response = $this->handleRequest($request);

        $this->assertJsonResponse('The values for <strong>permissions</strong> must be in an array.', $response, 'description');
        $this->assertResponseStatus(400, $response);
    }
}
