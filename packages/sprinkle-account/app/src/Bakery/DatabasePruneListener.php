<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Bakery;

use UserFrosting\Sprinkle\Account\Database\Models\UserVerification;
use UserFrosting\Sprinkle\Core\Bakery\Event\DatabasePruneEvent;

/**
 * Include account models in database cleanup.
 */
class DatabasePruneListener
{
    public function __invoke(DatabasePruneEvent $event): void
    {
        $event->addModel(UserVerification::class);
    }
}
