<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Database\Models\Events;

use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;

/**
 * Model event fired after a user has been soft deleted and anonymized.
 * Original values are available through `$user->getPreDeleteAttributes()`.
 */
class UserSoftDeletedEvent
{
    public function __construct(public readonly UserInterface $user)
    {
    }
}
