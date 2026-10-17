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
    case ADD_ROLE = 'account_add_role';
    case REMOVE_ROLE = 'account_remove_role';
    case ENABLE = 'account_enable';
    case DISABLE = 'account_disable';
    case VERIFY = 'account_verify';
    case UNVERIFY = 'account_unverify';
    case FORCE_PASSWORD_RESET = 'account_force_password_reset';
    case CHANGED_PASSWORD = 'changed_password';

    /**
     * {@inheritDoc}
     */
    public static function getI18nKey(string $value): ?string
    {
        return match (self::tryFrom($value)) {
            self::UPDATE_INFO          => 'ACCOUNT.ACTIVITY.UPDATE_INFO',
            self::ADD_TO_GROUP         => 'ACCOUNT.ACTIVITY.ADD_TO_GROUP',
            self::REMOVE_FROM_GROUP    => 'ACCOUNT.ACTIVITY.REMOVE_FROM_GROUP',
            self::ADD_ROLE             => 'ACCOUNT.ACTIVITY.ADD_ROLE',
            self::REMOVE_ROLE          => 'ACCOUNT.ACTIVITY.REMOVE_ROLE',
            self::ENABLE               => 'ACCOUNT.ACTIVITY.ENABLE',
            self::DISABLE              => 'ACCOUNT.ACTIVITY.DISABLE',
            self::VERIFY               => 'ACCOUNT.ACTIVITY.VERIFY',
            self::UNVERIFY             => 'ACCOUNT.ACTIVITY.UNVERIFY',
            self::FORCE_PASSWORD_RESET => 'ACCOUNT.ACTIVITY.FORCE_PASSWORD_RESET',
            self::CHANGED_PASSWORD     => 'ACCOUNT.ACTIVITY.CHANGED_PASSWORD',
            default                    => null,
        };
    }

    /**
     * {@inheritDoc}
     */
    public static function getLabelI18nKey(string $value): ?string
    {
        return match (self::tryFrom($value)) {
            self::UPDATE_INFO          => 'ACCOUNT.ACTIVITY.LABEL.UPDATE_INFO',
            self::ADD_TO_GROUP         => 'ACCOUNT.ACTIVITY.LABEL.ADD_TO_GROUP',
            self::REMOVE_FROM_GROUP    => 'ACCOUNT.ACTIVITY.LABEL.REMOVE_FROM_GROUP',
            self::ADD_ROLE             => 'ACCOUNT.ACTIVITY.LABEL.ADD_ROLE',
            self::REMOVE_ROLE          => 'ACCOUNT.ACTIVITY.LABEL.REMOVE_ROLE',
            self::ENABLE               => 'ACCOUNT.ACTIVITY.LABEL.ENABLE',
            self::DISABLE              => 'ACCOUNT.ACTIVITY.LABEL.DISABLE',
            self::VERIFY               => 'ACCOUNT.ACTIVITY.LABEL.VERIFY',
            self::UNVERIFY             => 'ACCOUNT.ACTIVITY.LABEL.UNVERIFY',
            self::FORCE_PASSWORD_RESET => 'ACCOUNT.ACTIVITY.LABEL.FORCE_PASSWORD_RESET',
            self::CHANGED_PASSWORD     => 'ACCOUNT.ACTIVITY.LABEL.CHANGED_PASSWORD',
            default                    => null,
        };
    }
}
