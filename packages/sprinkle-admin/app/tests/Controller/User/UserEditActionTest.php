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
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Testing\WithTestUser;
use UserFrosting\Sprinkle\Admin\Tests\AdminTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

class UserEditActionTest extends AdminTestCase
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
        $request = $this->createJsonRequest('PUT', '/api/users/u/foo');
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
        $request = $this->createJsonRequest('PUT', '/api/users/u/foo');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse([
            'title'       => 'Account Not Found',
            'description' => 'This account does not exist. It may have been deleted.',
            'status'      => 404,
        ], $response);
        $this->assertResponseStatus(404, $response);
    }

    public function testPageForForbiddenException(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('PUT', '/api/users/u/' . $user->user_name);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse('Access Denied', $response, 'title');
        $this->assertResponseStatus(403, $response);
    }

    public function testPage(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['update_user_field']);

        /** @var Config */
        $config = $this->ci->get(Config::class);

        // Force locale config.
        $config->set('site.registration.user_defaults.locale', 'en_US');
        $config->set('site.locales.available', ['en_US' => true]);

        // Create a second user, to be edited.
        /** @var User */
        $userToEdit = User::factory()->create();

        // Set post payload
        $data = [
            'user_name'  => 'foo',
            'first_name' => 'Foo',
            'last_name'  => 'Bar',
            'email'      => 'foo@bar.com',
            'locale'     => 'en_US',
            'group_id'   => 0,
        ];

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('PUT', '/api/users/u/' . $userToEdit->user_name, $data);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(200, $response);
        $this->assertJsonStructure([
            'title',
            'description'
        ], $response);
        $this->assertJsonResponse('Account details updated for user <strong>foo</strong>', $response, 'title');

        // Test that the user was updated
        /** @var User */
        $editedUser = User::find($userToEdit->id);
        $this->assertSame('foo', $editedUser->user_name);
        $this->assertSame('foo@bar.com', $editedUser->email);
        $this->assertNull($editedUser->group_id);
    }

    public function testPageForEditMasterUser(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['update_user_field']);

        // Create a second user, to be edited.
        /** @var User */
        $userToEdit = User::factory()->create();

        /** @var Config */
        $config = $this->ci->get(Config::class);
        $config->set('reserved_user_ids.master', $userToEdit->id);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('PUT', '/api/users/u/' . $userToEdit->user_name);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse('Access Denied', $response, 'title');
        $this->assertResponseStatus(403, $response);
    }

    public function testPageForEmailInUse(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['update_user_field']);

        // Create a second user, to be edited.
        /** @var User */
        $userToEdit = User::factory()->create();

        // Set post payload
        $data = $userToEdit->toArray();
        $data['email'] = $user->email;

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('PUT', '/api/users/u/' . $userToEdit->user_name, $data);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(400, $response);
        $this->assertJsonResponse([
            'title'       => 'Invalid email',
            'description' => 'Email <strong>' . $user->email . '</strong> is already in use.',
            'status'      => 400,
        ], $response);
    }

    public function testPageForFailedValidation(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['update_user_field']);

        // Set post payload
        $data = [
            'user_name'  => 'foo',
            'first_name' => 'Foo',
            'last_name'  => 'Bar',
            'email'      => 'foo', // <-- Bad Email on purpose
            'locale'     => 'en_US',
            'group_id'   => 0,
        ];

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('PUT', '/api/users/u/' . $user->user_name, $data);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(400, $response);
        $this->assertJsonResponse([
            'title'       => 'Validation error',
            'description' => 'Invalid email address.',
            'status'      => 400,
        ], $response);
    }
}
