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
use UserFrosting\Sprinkle\Account\Database\Models\Group;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Testing\WithTestUser;
use UserFrosting\Sprinkle\Admin\Tests\AdminTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

class UserApiTest extends AdminTestCase
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
        $request = $this->createJsonRequest('GET', '/api/users/u/foo');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse('Login Required', $response, 'title');
        $this->assertResponseStatus(401, $response);
    }

    public function testPageForForbiddenException(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user);

        // Create a second user
        /** @var User */
        $user2 = User::factory()->create();

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('GET', '/api/users/u/' . $user2->user_name);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse('Access Denied', $response, 'title');
        $this->assertResponseStatus(403, $response);
    }

    public function testPageAccessToSeeYourself(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('GET', '/api/users/u/' . $user->user_name);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(200, $response);
        $this->assertJsonResponse($user->user_name, $response, 'user_name');
    }

    public function testPageAccessToUserInYourGroupButNoPermissions(): void
    {
        /** @var Group */
        $group = Group::factory()->create();

        /** @var User */
        $user = User::factory()->create([
            'group_id' => $group->id,
        ]);
        $this->actAsUser($user);

        // Create a second user
        /** @var User */
        $user2 = User::factory()->create([
            'group_id' => $group->id,
        ]);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('GET', '/api/users/u/' . $user2->user_name);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse('Access Denied', $response, 'title');
        $this->assertResponseStatus(403, $response);
    }

    public function testPageAccessToUserInYourGroupWithPermissions(): void
    {
        /** @var Group */
        $group = Group::factory()->create();

        /** @var User */
        $user = User::factory()->create([
            'group_id' => $group->id,
        ]);
        $this->actAsUser($user, permissions: ['uri_user_in_group']);

        // Create a second user
        /** @var User */
        $user2 = User::factory()->create([
            'group_id' => $group->id,
        ]);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('GET', '/api/users/u/' . $user2->user_name);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(200, $response);
        $this->assertJsonResponse($user2->user_name, $response, 'user_name');
    }

    public function testPageAccessWithProperPermission(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['uri_user']);

        // Create a second user
        /** @var User */
        $user2 = User::factory()->create();

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('GET', '/api/users/u/' . $user2->user_name);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(200, $response);
        $this->assertJsonResponse($user2->user_name, $response, 'user_name');
    }

    public function testPage(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user);

        /** @var Config */
        $config = $this->ci->get(Config::class);

        // Force locale config.
        $config->set('site.registration.user_defaults.locale', 'en_US');
        $config->set('site.locales.available', ['en_US' => true]);

        // Create request with method and url and fetch response
        $request = $this->createRequest('GET', '/api/users/u/' . $user->user_name);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(200, $response);
        $this->assertJsonStructure([
            'id',
            'user_name',
            'email',
            'first_name',
            'last_name',
            'locale',
            'group_id',
            'flag_verified',
            'flag_enabled',
            'deleted_at',
            'created_at',
            'updated_at',
            'locale_name',
            'full_name',
            'avatar',
            'group',
        ], $response);
    }

    public function testPageWithMultipleLocales(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user);

        /** @var Config */
        $config = $this->ci->get(Config::class);

        // Force locale config.
        $config->set('site.registration.user_defaults.locale', 'en_US');
        $config->set('site.locales.available', [
            'en_US' => true,
            'fr_FR' => true,
        ]);

        // Create request with method and url and fetch response
        $request = $this->createRequest('GET', '/api/users/u/' . $user->user_name);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(200, $response);
        $this->assertNotEmpty((string) $response->getBody());
    }
}
