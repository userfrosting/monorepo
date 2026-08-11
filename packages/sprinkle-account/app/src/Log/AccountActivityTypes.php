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

enum AccountActivityTypes: string implements ActivityTypes
{
    case CREATE = 'account_create';
    case DELETE = 'account_delete';
    case UPDATE_INFO = 'account_update_info';
    case UPDATE_FIELD = 'account_update_field';
    case UPDATE_PROFILE_SETTINGS = 'update_profile_settings';
    case UPDATE_ACCOUNT_SETTINGS = 'update_account_settings';

    /**
     * {@inheritDoc}
     */
    public static function getI18nKey(string $value): ?string
    {
        return match (self::tryFrom($value)) {
            self::CREATE                  => 'ACCOUNT.ACTIVITY.CREATE',
            self::DELETE                  => 'ACCOUNT.ACTIVITY.DELETE',
            self::UPDATE_INFO             => 'ACCOUNT.ACTIVITY.UPDATE_INFO',
            self::UPDATE_FIELD            => 'ACCOUNT.ACTIVITY.UPDATE_FIELD',
            self::UPDATE_PROFILE_SETTINGS => 'ACCOUNT.ACTIVITY.UPDATE_PROFILE_SETTINGS',
            self::UPDATE_ACCOUNT_SETTINGS => 'ACCOUNT.ACTIVITY.UPDATE_ACCOUNT_SETTINGS',
            default                       => null,
        };
    }
}
