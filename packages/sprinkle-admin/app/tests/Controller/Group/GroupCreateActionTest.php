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

class GroupCreateActionTest extends AdminTestCase
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
        $request = $this->createJsonRequest('POST', '/api/groups');
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

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/api/groups');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse('Access Denied', $response, 'title');
        $this->assertResponseStatus(403, $response);
    }

    public function testPost(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['create_group']);

        // Set post payload
        $data = [
            'name'        => 'The Foo',
            'slug'        => 'foo',
            'icon'        => 'fas fas-icon',
            'description' => 'Foo description',
        ];

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/api/groups', $data);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(200, $response);
        $this->assertJsonStructure(['title', 'description'], $response);
        $this->assertJsonResponse('Successfully created group <strong>The Foo</strong>', $response, 'title');

        // Make sure the user is added to the db by querying it
        /** @var Group */
        $group = Group::where('slug', 'foo')->first();
        $this->assertSame('The Foo', $group['name']);
        $this->assertSame('fas fas-icon', $group['icon']);
        $this->assertSame('Foo description', $group['description']);
    }

    /**
     * @depends testPost
     */
    public function testPostForFailedValidation(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['create_group']);

        // Set post payload
        $data = [
            'name'        => 'The Foo',
            'slug'        => '', //<-- Missing on purpose
            'icon'        => 'fas fas-icon',
            'description' => 'Foo description',
        ];

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/api/groups', $data);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(400, $response);
        $this->assertJsonResponse([
            'title'       => 'Validation error',
            'description' => 'Please specify a value for <strong>Slug</strong>. Slug must be between 1 and 255 characters in length.',
            'status'      => 400,
        ], $response);
    }

    /**
     * @depends testPost
     */
    public function testPostForDuplicateName(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['create_group']);

        // Create a group
        /** @var Group */
        $group = Group::factory()->create();

        // Set post payload
        $data = [
            'name'        => $group->name,
            'slug'        => 'foo',
            'icon'        => 'fas fas-icon',
            'description' => 'Foo description',
        ];

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/api/groups', $data);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(400, $response);
        $this->assertJsonResponse([
            'title'       => 'Group error',
            'description' => 'A group named <strong>' . $group->name . '</strong> already exist',
            'status'      => 400,
        ], $response);
    }

    /**
     * @depends testPost
     */
    public function testPostForDuplicateSlug(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['create_group']);

        // Create a group
        /** @var Group */
        $group = Group::factory()->create();

        // Set post payload
        $data = [
            'name'        => 'The Foo',
            'slug'        => $group->slug,
            'icon'        => 'fas fas-icon',
            'description' => 'Foo description',
        ];

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/api/groups', $data);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(400, $response);
        $this->assertJsonResponse([
            'title'       => 'Group error',
            'description' => 'A <strong>' . $group->slug . '</strong> slug already exists',
            'status'      => 400,
        ], $response);
    }
}
