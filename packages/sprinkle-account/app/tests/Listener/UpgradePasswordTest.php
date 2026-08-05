<?php

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
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Event\UserAuthenticatedEvent;
use UserFrosting\Sprinkle\Account\Listener\UpgradePassword;
use UserFrosting\Sprinkle\Account\Log\UserActivityLogger;
use UserFrosting\Sprinkle\Account\Log\UserActivityLoggerInterface;
use UserFrosting\Sprinkle\Account\Tests\AccountTestCase;

/**
 * Tests UserAuthenticatedEvent
 */
class UpgradePasswordTest extends AccountTestCase
{
    use MockeryPHPUnitIntegration;

    public function testNoNeedToUpgrade(): void
    {
        /** @var User */
        $user = Mockery::mock(User::class)
            ->shouldReceive('getAttribute')->with('password')->once()->andReturn('foo')
            ->shouldNotReceive('save')
            ->getMock();

        // Create event
        $event = new UserAuthenticatedEvent($user, 'email', 'foo', 'MyPassword');

        // Handle
        /** @var UpgradePassword */
        $listener = $this->getService(UpgradePassword::class);
        $listener($event);
    }

    public function testUpgrade(): void
    {
        /** @var Mockery\MockInterface&UserActivityLogger */
        $logger = Mockery::mock(UserActivityLogger::class)
            ->shouldReceive('debug')->once()
            ->getMock();
        $this->getContainer()->set(UserActivityLoggerInterface::class, $logger);

        /** @var User */
        $user = Mockery::mock(User::class)
            ->shouldReceive('getAttribute')->with('password')->once()->andReturn('87e995bde9ebdc73fc58cc75a9fadc4ae630d8207650fbe94e148ccc8058d5de5')
            ->shouldReceive('setAttribute')->with('password', 'MyPassword')->once()
            ->shouldReceive('save')->once()
            ->shouldReceive('getAttribute')->with('user_name')->once()->andReturn('My Username')
            ->shouldReceive('getAttribute')->with('id')->once()->andReturn(1)
            ->getMock();

        // Create event
        $event = new UserAuthenticatedEvent($user, 'email', 'foo', 'MyPassword');

        // Handle
        /** @var UpgradePassword */
        $listener = $this->getService(UpgradePassword::class);
        $listener($event);
    }
}
