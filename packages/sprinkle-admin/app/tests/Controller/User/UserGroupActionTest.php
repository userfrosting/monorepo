<?php

declare(strict_types=1);

namespace UserFrosting\Sprinkle\Admin\Tests\Controller\User;

use UserFrosting\Sprinkle\Account\Database\Models\Activity;
use UserFrosting\Sprinkle\Account\Database\Models\Group;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Testing\WithTestUser;
use UserFrosting\Sprinkle\Admin\Log\AdminAccountActivityTypes;
use UserFrosting\Sprinkle\Admin\Tests\AdminTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

class UserGroupActionTest extends AdminTestCase
{
    use RefreshDatabase;
    use WithTestUser;

    public function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
    }

    public function testGroupCanBeAssignedAndRemoved(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['update_user_field']);

        /** @var User */
        $userToEdit = User::factory()->create();
        /** @var Group */
        $oldGroup = Group::factory()->create();
        /** @var Group */
        $newGroup = Group::factory()->create();
        $userToEdit->group_id = $oldGroup->id;
        $userToEdit->save();

        $request = $this->createJsonRequest(
            'PUT',
            '/api/users/u/' . $userToEdit->user_name . '/group',
            ['group_id' => $newGroup->id]
        );
        $response = $this->handleRequest($request);

        $this->assertResponseStatus(200, $response);
        $userToEdit->refresh();
        $this->assertSame($newGroup->id, $userToEdit->group_id);

        /** @var Activity|null $removedActivity */
        $removedActivity = Activity::query()
            ->where('type', AdminAccountActivityTypes::REMOVE_FROM_GROUP->value)
            ->where('subject_id', $userToEdit->id)
            ->where('context_id', $oldGroup->id)
            ->first();
        $this->assertNotNull($removedActivity);
        $this->assertSame((string) $oldGroup->id, $removedActivity->context_id);
        $this->assertSame([], $removedActivity->metadata);
        $this->assertNull($removedActivity->properties);

        /** @var Activity|null $addedActivity */
        $addedActivity = Activity::query()
            ->where('type', AdminAccountActivityTypes::ADD_TO_GROUP->value)
            ->where('subject_id', $userToEdit->id)
            ->where('context_id', $newGroup->id)
            ->first();
        $this->assertNotNull($addedActivity);
        $this->assertSame((string) $newGroup->id, $addedActivity->context_id);
        $this->assertSame([], $addedActivity->metadata);
        $this->assertNull($addedActivity->properties);

        $request = $this->createJsonRequest(
            'PUT',
            '/api/users/u/' . $userToEdit->user_name . '/group',
            ['group_id' => 0]
        );
        $response = $this->handleRequest($request);

        $this->assertResponseStatus(200, $response);
        $userToEdit->refresh();
        $this->assertNull($userToEdit->group_id);
        $this->assertCount(2, Activity::query()
            ->where('type', AdminAccountActivityTypes::REMOVE_FROM_GROUP->value)
            ->where('subject_id', $userToEdit->id)
            ->get());
        $this->assertCount(1, Activity::query()
            ->where('type', AdminAccountActivityTypes::ADD_TO_GROUP->value)
            ->where('subject_id', $userToEdit->id)
            ->get());
    }
}
