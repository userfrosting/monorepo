<?php

declare(strict_types=1);

/*
 * UserFrosting Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-admin
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Admin\Log;

use UserFrosting\Sprinkle\Account\Log\ActivityTypes;

enum AdminAccountActivityTypes: string implements ActivityTypes
{
    case UPDATE_INFO = 'account_update_info';
    case ADD_TO_GROUP = 'account_add_to_group';
    case REMOVE_FROM_GROUP = 'account_remove_from_group';
    case UPDATE_ROLES = 'account_update_roles';
    case UPDATE_FIELD = 'account_update_field';
    case ENABLE = 'account_enable';
    case DISABLE = 'account_disable';
    case VERIFY = 'account_verify';
    case UNVERIFY = 'account_unverify';

    /**
     * {@inheritDoc}
     */
    public static function getI18nKey(string $value): ?string
    {
        return match (self::tryFrom($value)) {
            self::UPDATE_INFO       => 'ACCOUNT.ACTIVITY.UPDATE_INFO',
            self::ADD_TO_GROUP      => 'ACCOUNT.ACTIVITY.ADD_TO_GROUP',
            self::REMOVE_FROM_GROUP => 'ACCOUNT.ACTIVITY.REMOVE_FROM_GROUP',
            self::UPDATE_ROLES      => 'ACCOUNT.ACTIVITY.UPDATE_ROLES',
            self::UPDATE_FIELD      => 'ACCOUNT.ACTIVITY.UPDATE_FIELD',
            self::ENABLE            => 'ACCOUNT.ACTIVITY.ENABLE',
            self::DISABLE           => 'ACCOUNT.ACTIVITY.DISABLE',
            self::VERIFY            => 'ACCOUNT.ACTIVITY.VERIFY',
            self::UNVERIFY          => 'ACCOUNT.ACTIVITY.UNVERIFY',
            default                 => null,
        };
    }

    /**
     * {@inheritDoc}
     */
    public static function getLabelI18nKey(string $value): ?string
    {
        return match (self::tryFrom($value)) {
            self::UPDATE_INFO       => 'ACCOUNT.ACTIVITY.LABEL.UPDATE_INFO',
            self::ADD_TO_GROUP      => 'ACCOUNT.ACTIVITY.LABEL.ADD_TO_GROUP',
            self::REMOVE_FROM_GROUP => 'ACCOUNT.ACTIVITY.LABEL.REMOVE_FROM_GROUP',
            self::UPDATE_ROLES      => 'ACCOUNT.ACTIVITY.LABEL.UPDATE_ROLES',
            self::UPDATE_FIELD      => 'ACCOUNT.ACTIVITY.LABEL.UPDATE_FIELD',
            self::ENABLE            => 'ACCOUNT.ACTIVITY.LABEL.ENABLE',
            self::DISABLE           => 'ACCOUNT.ACTIVITY.LABEL.DISABLE',
            self::VERIFY            => 'ACCOUNT.ACTIVITY.LABEL.VERIFY',
            self::UNVERIFY          => 'ACCOUNT.ACTIVITY.LABEL.UNVERIFY',
            default                 => null,
        };
    }
}
