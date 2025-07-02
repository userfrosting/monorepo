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
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Testing\WithTestUser;
use UserFrosting\Sprinkle\Admin\Tests\AdminTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

class RoleDeleteActionTest extends AdminTestCase
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
        $request = $this->createJsonRequest('DELETE', '/api/roles/r/foo');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse('Login Required', $response, 'title');
        $this->assertResponseStatus(401, $response);
    }

    public function testPageWithNotFoundGroup(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('DELETE', '/api/roles/r/foo');
        $response = $this->handleRequest($request);

        // Assert response status & body
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

        // Create a second user, to be deleted.
        /** @var Role */
        $role = Role::factory()->create();

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('DELETE', '/api/roles/r/' . $role->slug);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse('Access Denied', $response, 'title');
        $this->assertResponseStatus(403, $response);
    }

    public function testPost(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['delete_role']);

        // Create a second user, to be deleted.
        /** @var Role */
        $role = Role::factory()->create();

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('DELETE', '/api/roles/r/' . $role->slug);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(200, $response);
        $this->assertJsonResponse([
            'title'       => 'Successfully deleted role <strong>' . $role->name . '</strong>',
            'description' => '',
        ], $response);

        // Make sure the user is deleted from the db by querying it
        $result = Role::where('slug', $role->slug)->first();
        $this->assertNull($result);
    }

    public function testPostForDefaultRole(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['delete_role']);

        // Create a second user, to be deleted.
        /** @var Role */
        $role = Role::factory()->create();

        // Set default Role
        /** @var Config */
        $config = $this->ci->get(Config::class);
        $config->set('site.registration.user_defaults.roles', [$role->slug => true]);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('DELETE', '/api/roles/r/' . $role->slug);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse([
            'title'       => 'Role error',
            'description' => "You can't delete the role <strong>{$role->name}</strong> because it is a default role for newly registered users.",
            'status'      => 400,
        ], $response);
        $this->assertResponseStatus(400, $response);
    }

    public function testPostForNonEmptyRole(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['delete_role']);

        // Create a second user, to be deleted.
        /** @var Role */
        $role = Role::factory()->create();

        // Assign user to Role
        $user->roles()->attach($role);
        $user->save();

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('DELETE', '/api/roles/r/' . $role->slug);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse("You can't do that because there are still users who have the role <strong>{$role->name}</strong>.", $response, 'description');
        $this->assertResponseStatus(400, $response);
    }
}
