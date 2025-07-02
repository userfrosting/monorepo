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
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Account\Database\Models\Group;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Testing\WithTestUser;
use UserFrosting\Sprinkle\Admin\Tests\AdminTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

class GroupDeleteActionTest extends AdminTestCase
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
        $request = $this->createJsonRequest('DELETE', '/api/groups/g/foo');
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
        $request = $this->createJsonRequest('DELETE', '/api/groups/g/foo');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse([
            'title'       => 'Not Found',
            'description' => 'Group not found',
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
        /** @var Group */
        $groupToDelete = Group::factory()->create();

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('DELETE', '/api/groups/g/' . $groupToDelete->slug);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse('Access Denied', $response, 'title');
        $this->assertResponseStatus(403, $response);
    }

    public function testPost(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['delete_group']);

        // Create a second user, to be deleted.
        /** @var Group */
        $groupToDelete = Group::factory()->create();

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('DELETE', '/api/groups/g/' . $groupToDelete->slug);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(200, $response);
        $this->assertJsonResponse([
            'title'       => 'Successfully deleted group <strong>' . $groupToDelete->name . '</strong>',
            'description' => '',
        ], $response);

        // Make sure the user is deleted from the db by querying it
        $group = Group::where('slug', $groupToDelete->slug)->first();
        $this->assertNull($group);
    }

    public function testPostForDefaultGroup(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['delete_group']);

        // Create a second user, to be deleted.
        /** @var Group */
        $groupToDelete = Group::factory()->create();

        // Set default group
        /** @var Config */
        $config = $this->ci->get(Config::class);
        $config->set('site.registration.user_defaults.group', $groupToDelete->slug);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('DELETE', '/api/groups/g/' . $groupToDelete->slug);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse([
            'title'       => 'Group error',
            'description' => "You can't delete the group <strong>{$groupToDelete->name}</strong> because it is the default group for newly registered users.",
            'status'      => 400,
        ], $response);
        $this->assertResponseStatus(400, $response);
    }

    public function testPostForNonEmptyGroup(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['delete_group']);

        // Create a second user, to be deleted.
        /** @var Group */
        $groupToDelete = Group::factory()->create();

        // Assign user to group
        $user->group()->associate($groupToDelete);
        $user->save();

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('DELETE', '/api/groups/g/' . $groupToDelete->slug);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse("You can't do that because there are still users associated with the group <strong>{$groupToDelete->name}</strong>.", $response, 'description');
        $this->assertResponseStatus(400, $response);
    }
}
