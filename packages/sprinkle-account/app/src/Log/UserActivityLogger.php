<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Log;

use UserFrosting\Sprinkle\Core\Log\Logger;

/**
 * User Activity Logger.
 *
 * @todo : We could bring back the processor, to add the current user into the context
 *
 * @deprecated 6.1 Use ActivityRecorder instead
 */
class UserActivityLogger extends Logger implements UserActivityLoggerInterface
{
    public function __construct(UserActivityDatabaseHandler $handler)
    {
        parent::__construct($handler, 'userActivity');
    }
}
