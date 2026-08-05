<?php

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\Event;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Account\Database\Models\Group;
use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Event\UserCreatedEvent;
use UserFrosting\Sprinkle\Account\Listener\AssignDefaultRoles;
use UserFrosting\Sprinkle\Account\Tests\AccountTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

class AssignDefaultRolesTest extends AccountTestCase
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
        /** @var Role */
        $role = Role::factory()->create();

        // Create a user
        /** @var User */
        $user = User::factory()->create();

        // Assert initial user is not in the group
        $this->assertSame([], $user->roles()->pluck('id')->all());

        /** @var Config */
        $config = $this->getService(Config::class);
        $config->set('site.registration.user_defaults.roles', [$role->slug => true]);

        // Create event
        $event = new UserCreatedEvent($user);

        // Handle
        /** @var AssignDefaultRoles */
        $listener = $this->getService(AssignDefaultRoles::class);
        $listener($event);

        // Check user group
        $user->refresh();
        $this->assertSame([$role->id], $user->roles()->pluck('id')->all());
    }
}
