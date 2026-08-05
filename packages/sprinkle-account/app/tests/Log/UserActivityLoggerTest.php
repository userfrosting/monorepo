<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\Repository;

use LogicException;
use UserFrosting\Sprinkle\Account\Database\Models\Activity;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Log\UserActivityLogger;
use UserFrosting\Sprinkle\Account\Log\UserActivityLoggerInterface;
use UserFrosting\Sprinkle\Account\Tests\AccountTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

final class UserActivityLoggerTest extends AccountTestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
    }

    public function testLogger(): void
    {
        // Assert initial state
        $this->assertSame(0, Activity::count());

        /** @var User */
        $user = User::factory()->create();

        /** @var UserActivityLogger */
        $logger = $this->getService(UserActivityLoggerInterface::class);

        $logger->info('User did a test', [
            'type'    => 'test',
            'user_id' => $user->id,
        ]);

        // Assert table status
        $activities = Activity::all();
        $this->assertCount(1, $activities);

        /** @var Activity */
        $activity = $activities->first();
        $this->assertSame('User did a test', $activity->description);
        $this->assertSame('test', $activity->type);
        $this->assertSame($user->id, $activity->user_id);
        $this->assertSame($user->id, $activity->user->id);
    }

    public function testLoggerWithNoDefaultData(): void
    {
        /** @var UserActivityLogger */
        $logger = $this->getService(UserActivityLoggerInterface::class);

        $this->expectException(LogicException::class);
        $logger->info('User did a test');
    }
}
