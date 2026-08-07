<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Listener;

use UserFrosting\Sprinkle\Account\Event\UserLoggedOutEvent;
use UserFrosting\Sprinkle\Account\Log\ActivityRecorderInterface;
use UserFrosting\Sprinkle\Account\Log\UserActivityTypes;

/**
 * Save the user activity when the user is logged-in.
 */
class UserLogoutActivity
{
    public function __construct(
        protected ActivityRecorderInterface $logger,
    ) {
    }

    public function __invoke(UserLoggedOutEvent $event): void
    {
        // Add a sign out activity (time is automatically set by database)
        $this->logger->record(
            user: $event->user,
            type: UserActivityTypes::LOGGED_OUT
        );
    }
}
