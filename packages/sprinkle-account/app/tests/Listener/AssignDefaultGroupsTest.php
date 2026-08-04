<?php

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\Event;

use Exception;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Account\Database\Models\Group;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Event\UserCreatedEvent;
use UserFrosting\Sprinkle\Account\Exceptions\DefaultGroupException;
use UserFrosting\Sprinkle\Account\Listener\AssignDefaultGroups;
use UserFrosting\Sprinkle\Account\Tests\AccountTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

class AssignDefaultGroupsTest extends AccountTestCase
{
    use MockeryPHPUnitIntegration;
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
    }

    public function testDefaultGroup(): void
    {
        // Create a group
        /** @var Group */
        $group = Group::factory()->create();

        // Create a user
        /** @var User */
        $user = User::factory()->create();

        // Assert initial user is not in the group
        $this->assertNotSame($group->id, $user->group_id);

        /** @var Config */
        $config = $this->ci->get(Config::class);
        $config->set('site.registration.user_defaults.group', $group->slug);

        // Create event
        $event = new UserCreatedEvent($user);

        // Handle
        /** @var AssignDefaultGroups */
        $listener = $this->ci->get(AssignDefaultGroups::class);
        $listener($event);

        // Check user group
        $user->refresh();
        $this->assertSame($group->id, $user->group_id);
    }

    public function testNoDefaultGroup(): void
    {
        // Create a user
        /** @var User */
        $user = User::factory()->create();

        // Assert initial user is not in the group
        $this->assertNull($user->group_id);

        /** @var Config */
        $config = $this->ci->get(Config::class);
        $config->set('site.registration.user_defaults.group', null);

        // Create event
        $event = new UserCreatedEvent($user);

        // Handle
        /** @var AssignDefaultGroups */
        $listener = $this->ci->get(AssignDefaultGroups::class);
        $listener($event);

        // Check user group
        $user->refresh();
        $this->assertNull($user->group_id);
    }

    public function testDefaultGroupException(): void
    {
        // Create a user
        /** @var Mockery\MockInterface&User */
        $user = Mockery::mock(User::class);

        /** @var Config */
        $config = $this->ci->get(Config::class);
        $config->set('site.registration.user_defaults.group', 'foo');

        // Create event
        $event = new UserCreatedEvent($user);

        // Handle
        /** @var AssignDefaultGroups */
        $listener = $this->ci->get(AssignDefaultGroups::class);

        // Assert exception is thrown
        try {
            $listener($event);
        } catch (Exception $e) {
            $this->assertInstanceOf(DefaultGroupException::class, $e);
            $this->assertSame(['slug' => 'foo'], $e->getDescription()->parameters); // @phpstan-ignore-line
        }
    }
}
