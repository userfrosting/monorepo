<?php

declare(strict_types=1);

/*
 * UserFrosting Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-admin
 * @copyright Copyright (c) 2022 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Admin\Tests\Controller\User;

use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Admin\Tests\AdminTestCase;
use UserFrosting\Sprinkle\Admin\Tests\testUserTrait;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

class RoleEditPermissionsModalTest extends AdminTestCase
{
    use RefreshDatabase;
    use testUserTrait;

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
        $request = $this->createJsonRequest('GET', '/modals/roles/permissions');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse('Login Required', $response, 'title');
        $this->assertResponseStatus(302, $response);

        // Assert Event Redirect
        $this->assertSame('/account/sign-in?redirect=%2Fmodals%2Froles%2Fpermissions', $response->getHeaderLine('Location'));
    }

    public function testPageForForbiddenException(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user);

        // Create Role
        /** @var Role */
        $role = Role::factory()->create();

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('GET', '/modals/roles/permissions')
                        ->withQueryParams(['slug' => $role->slug]);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse('Access Denied', $response, 'title');
        $this->assertResponseStatus(403, $response);
    }

    public function testPage(): void
    {
        // Setup custom permissions for group test.
        $permission = new Permission([
            'slug'       => 'update_role_field',
            'name'       => 'update_role_field',
            'conditions' => "subset(fields,['permissions'])",
        ]);
        $permission->save();

        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: [$permission]);

        // Create Role
        /** @var Role */
        $role = Role::factory()->create();

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('GET', '/modals/roles/permissions')
                        ->withQueryParams(['slug' => $role->slug]);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(200, $response);
        $this->assertNotEmpty((string) $response->getBody());
    }
}
