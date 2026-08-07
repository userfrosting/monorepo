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

enum AccountActivityTypes: string
{
    case CREATE = 'account_create';
    case DELETE = 'account_delete';
    case UPDATE_INFO = 'account_update_info';
    case UPDATE_FIELD = 'account_update_field';
    case UPDATE_PROFILE_SETTINGS = 'update_profile_settings';
    case UPDATE_ACCOUNT_SETTINGS = 'update_account_settings';
}
