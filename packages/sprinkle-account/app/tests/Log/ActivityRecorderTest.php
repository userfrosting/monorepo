<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\Log;

use UserFrosting\Sprinkle\Account\Database\Models\Activity;
use UserFrosting\Sprinkle\Account\Database\Models\Group;
use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Sprinkle\Account\Database\Models\Persistence;
use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\Account\Database\Models\RoleUsers;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Database\Models\UserVerification;
use UserFrosting\Sprinkle\Account\Log\ActivityRecorderInterface;
use UserFrosting\Sprinkle\Account\Tests\AccountTestCase;
use UserFrosting\Sprinkle\Core\Database\Models\Interfaces\MorphableModelInterface;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

class ActivityRecorderTest extends AccountTestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
    }

    public function testRecord(): void
    {
        /** @var User */
        $user = User::factory()->create();

        /** @var Role */
        $context = Role::create([
            'slug'        => 'test_role',
            'name'        => 'Test Role',
            'description' => 'A role for testing purposes.',
        ]);

        /** @var Group */
        $subject = Group::create([
            'slug'        => 'test_group',
            'name'        => 'Test Group',
            'description' => 'A group for testing purposes.',
        ]);

        $metadata = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];

        /** @var ActivityRecorderInterface */
        $recorder = $this->getService(ActivityRecorderInterface::class);
        $activity = $recorder->record(
            user: $user,
            type: TestActivityTypes::TEST_ACTIVITY,
            metadata: $metadata,
            context: $context,
            subject: $subject
        );

        $this->assertEquals($user->id, $activity->user?->id);
        $this->assertEquals($context->id, $activity->context_id);
        $this->assertEquals($subject->id, $activity->subject_id);
        $this->assertEquals('role', $activity->context_type);
        $this->assertEquals('group', $activity->subject_type);
        $this->assertEquals($context->id, $activity->context->id);
        $this->assertEquals($subject->id, $activity->subject->id);
        $this->assertSame('TEST_ACTIVITY', $activity->type);
        $this->assertSame($metadata, $activity->metadata);
        $this->assertNotNull($activity->occurred_at);
    }

    public function testBuiltinModelsImplementActivitySubjectInterface(): void
    {
        $models = [
            new Activity(),
            new Group(),
            new Permission(),
            new Persistence(),
            new Role(),
            new RoleUsers(),
            new User(),
            new UserVerification(),
        ];

        foreach ($models as $model) {
            // @phpstan-ignore-next-line method.alreadyNarrowedType
            $this->assertInstanceOf(MorphableModelInterface::class, $model);
        }
    }

    public function testRecordForNullRelations(): void
    {
        /** @var User */
        $user = User::factory()->create();

        /** @var ActivityRecorderInterface */
        $recorder = $this->getService(ActivityRecorderInterface::class);
        $activity = $recorder->record(
            user: $user,
            type: TestActivityTypes::TEST_NULL_RELATIONS,
            metadata: ['test' => 'success']
        );

        /** @var Activity|null $fetched */
        $fetched = Activity::find($activity->id);

        $this->assertNotNull($fetched);
        $this->assertNull($fetched->context_type);
        $this->assertNull($fetched->context_id);
        $this->assertNull($fetched->subject_type);
        $this->assertNull($fetched->subject_id);
        $this->assertSame(['test' => 'success'], $fetched->metadata);
    }
}

enum TestActivityTypes: string
{
    case TEST_ACTIVITY = 'TEST_ACTIVITY';
    case TEST_NULL_RELATIONS = 'TEST_NULL_RELATIONS';
}
