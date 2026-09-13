<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\Event;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Event\UserLoggedInEvent;
use UserFrosting\Sprinkle\Account\Listener\UserSignInActivity;
use UserFrosting\Sprinkle\Account\Log\UserActivityLoggerInterface;
use UserFrosting\Sprinkle\Account\Log\UserActivityTypes;

final class UserSignInActivityTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testInvokeLogsSignInActivity(): void
    {
        $logger = Mockery::mock(UserActivityLoggerInterface::class);
        $logger->shouldReceive('info')
            ->once()
            ->with(
                'User alice signed in.',
                Mockery::on(fn (array $context): bool => $context === [
                    'type'    => UserActivityTypes::LOGGED_IN,
                    'user_id' => 123,
                ])
            );

        /** @var User */
        $user = Mockery::mock(User::class)
            ->shouldReceive('getAttribute')->with('user_name')->once()->andReturn('alice')
            ->shouldReceive('getAttribute')->with('id')->once()->andReturn(123)
            ->getMock();

        $listener = new UserSignInActivity($logger);
        $listener(new UserLoggedInEvent($user));
    }
}
