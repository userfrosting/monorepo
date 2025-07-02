<?php

declare(strict_types=1);

/*
 * UserFrosting Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-admin
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Admin\Tests\Controller\User;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Testing\WithTestUser;
use UserFrosting\Sprinkle\Admin\Tests\AdminTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

class UserUpdateFieldActionTest extends AdminTestCase
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
        $request = $this->createJsonRequest('PUT', '/api/users/u/foo/password');
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
        $request = $this->createJsonRequest('PUT', '/api/users/u/foo/password');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse([
            'title'       => 'Account Not Found',
            'description' => 'This account does not exist. It may have been deleted.',
            'status'      => 404,
        ], $response);
        $this->assertResponseStatus(404, $response);
    }

    public function testPostForNoData(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, isMaster: true);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('PUT', '/api/users/u/' . $user->user_name . '/password');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse('Validation error', $response, 'title');
        $this->assertJsonResponse('Please specify a value for <strong>password</strong>.', $response, 'description');
        $this->assertResponseStatus(400, $response);
    }

    public function testPageForNoPermissions(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user);

        // Create a second user, to be edited.
        /** @var User */
        $userToEdit = User::factory()->create();

        // Create request with method and url and fetch response
        $data = ['password' => 'newpassword'];
        $request = $this->createJsonRequest('PUT', '/api/users/u/' . $userToEdit->user_name . '/password', $data);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse('Access Denied', $response, 'title');
        $this->assertResponseStatus(403, $response);
    }

    public function testPostForPassword(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['update_user_field']);

        // Create a second user, to be edited.
        /** @var User */
        $userToEdit = User::factory()->create();

        // Create request with method and url and fetch response
        $data = [
            'password'  => 'newpassword',
            'passwordc' => 'newpassword',
        ];
        $request = $this->createJsonRequest('PUT', '/api/users/u/' . $userToEdit->user_name . '/password', $data);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(200, $response);
        $this->assertJsonResponse([
            'title'       => 'Account details updated for user <strong>' . $userToEdit->user_name . '</strong>',
            'description' => '',
        ], $response);
    }

    public function testPostForPasswordWithoutConfirmation(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['update_user_field']);

        // Create a second user, to be edited.
        /** @var User */
        $userToEdit = User::factory()->create();

        // Create request with method and url and fetch response
        $data = [
            'password'  => 'newpassword',
            'passwordc' => 'notnewpassword',
        ];
        $request = $this->createJsonRequest('PUT', '/api/users/u/' . $userToEdit->user_name . '/password', $data);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse('Your password and confirmation password must match.', $response, 'description');
        $this->assertResponseStatus(400, $response);
    }

    public function testPostForEnabled(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['update_user_field']);

        // Create request with method and url and fetch response
        $data = ['flag_enabled' => '1'];
        $request = $this->createJsonRequest('PUT', '/api/users/u/' . $user->user_name . '/flag_enabled', $data);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(200, $response);
        $this->assertJsonResponse([
            'title'       => 'Account for user <strong>' . $user->user_name . '</strong> has been successfully enabled.',
            'description' => '',
        ], $response);
    }

    public function testPostForDisabled(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['update_user_field']);

        // Create a second user, to be edited.
        /** @var User */
        $userToEdit = User::factory()->create();

        // Create request with method and url and fetch response
        $data = ['flag_enabled' => '0'];
        $request = $this->createJsonRequest('PUT', '/api/users/u/' . $userToEdit->user_name . '/flag_enabled', $data);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(200, $response);
        $this->assertJsonResponse([
            'title'       => 'Account for user <strong>' . $userToEdit->user_name . '</strong> has been successfully disabled.',
            'description' => '',
        ], $response);
    }

    public function testPostForVerified(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['update_user_field']);

        // Create request with method and url and fetch response
        $data = ['flag_verified' => '1'];
        $request = $this->createJsonRequest('PUT', '/api/users/u/' . $user->user_name . '/flag_verified', $data);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(200, $response);
        $this->assertJsonResponse([
            'title'       => $user->user_name . "'s account has been manually activated",
            'description' => '',
        ], $response);
    }

    public function testPostForRole(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['update_user_field']);

        /** @var Role */
        $roles = Role::factory()->count(2)->create();

        /*
        N.B.: Expected value format, passed from uf-collection :
        value[0][role_id]: 1
        value[1][role_id]: 2
        value[2][role_id]: 3
        */

        // @phpstan-ignore-next-line
        $rolesIds = $roles->map(function ($item) {
            return ['role_id' => $item->id];
        })->toArray();

        // Create request with method and url and fetch response
        $data = ['roles' => $rolesIds];
        $request = $this->createJsonRequest('PUT', '/api/users/u/' . $user->user_name . '/roles', $data);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(200, $response);
        $this->assertJsonResponse([
            'title'       => 'Account details updated for user <strong>' . $user->user_name . '</strong>',
            'description' => '',
        ], $response);

        // Make sure the user has the new roles.
        $user->refresh();
        $this->assertCount(2, $user->roles);
    }

    public function testPostForRemovingRoles(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['update_user_field']);
        $this->assertCount(1, $user->roles); // Default role above.

        // Create request with method and url and fetch response
        // uf-collection will pass no data when removing all roles_id.
        $request = $this->createJsonRequest('PUT', '/api/users/u/' . $user->user_name . '/roles', ['roles' => []]);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse([
            'title'       => 'Account details updated for user <strong>' . $user->user_name . '</strong>',
            'description' => '',
        ], $response);
        $this->assertResponseStatus(200, $response);

        // Make sure the user has the new roles.
        $user->refresh();
        $this->assertCount(0, $user->roles);
    }

    public function testPageForFailedValidation(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['update_user_field']);

        // Create a second user, to be edited.
        /** @var User */
        $userToEdit = User::factory()->create();

        // Create request with method and url and fetch response
        $data = ['email' => 'notAndEmail'];
        $request = $this->createJsonRequest('PUT', '/api/users/u/' . $userToEdit->user_name . '/email', $data);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse('Invalid email address.', $response, 'description');
        $this->assertResponseStatus(400, $response);
    }

    public function testPageForFailedToEditMasterUser(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['update_user_field']);

        // Create a second user, and set it to master.
        /** @var User */
        $userToEdit = User::factory()->create();

        /** @var Config */
        $config = $this->ci->get(Config::class);
        $config->set('reserved_user_ids.master', $userToEdit->id);

        // Create request with method and url and fetch response
        $data = ['email' => 'notAndEmail'];
        $request = $this->createJsonRequest('PUT', '/api/users/u/' . $userToEdit->user_name . '/email', $data);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse('Access Denied', $response, 'title');
        $this->assertResponseStatus(403, $response);
    }

    public function testPostForDisableMasterUser(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, isMaster: true);

        // Create request with method and url and fetch response
        $data = ['flag_enabled' => '0'];
        $request = $this->createJsonRequest('PUT', '/api/users/u/' . $user->user_name . '/flag_enabled', $data);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse('You cannot disable the master account', $response, 'title');
        $this->assertResponseStatus(400, $response);
    }

    public function testPostForDisableSameUser(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['update_user_field']);

        // Create request with method and url and fetch response
        $data = ['flag_enabled' => '0'];
        $request = $this->createJsonRequest('PUT', '/api/users/u/' . $user->user_name . '/flag_enabled', $data);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse('You cannot disable your own account', $response, 'title');
        $this->assertResponseStatus(400, $response);
    }
}
