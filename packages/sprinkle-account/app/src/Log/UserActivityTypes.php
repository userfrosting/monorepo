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

enum UserActivityTypes: string implements ActivityTypes
{
    case REGISTER = 'sign_up';
    case VERIFIED = 'verified';
    case PASSWORD_RESET = 'password_reset';
    case LOGGED_IN = 'sign_in';
    case LOGGED_OUT = 'sign_out';
    case PASSWORD_UPGRADED = 'password_upgraded';

    /**
     * {@inheritDoc}
     */
    public static function getI18nKey(string $value): ?string
    {
        return match (self::tryFrom($value)) {
            self::REGISTER          => 'ACCOUNT.ACTIVITY.REGISTER',
            self::VERIFIED          => 'ACCOUNT.ACTIVITY.VERIFIED',
            self::PASSWORD_RESET    => 'ACCOUNT.ACTIVITY.PASSWORD_RESET',
            self::LOGGED_IN         => 'ACCOUNT.ACTIVITY.LOGGED_IN',
            self::LOGGED_OUT        => 'ACCOUNT.ACTIVITY.LOGGED_OUT',
            self::PASSWORD_UPGRADED => 'ACCOUNT.ACTIVITY.PASSWORD_UPGRADED',
            default                 => null,
        };
    }

    /**
     * {@inheritDoc}
     */
    public static function getLabelI18nKey(string $value): ?string
    {
        return match (self::tryFrom($value)) {
            self::REGISTER          => 'ACCOUNT.ACTIVITY.LABEL.REGISTER',
            self::VERIFIED          => 'ACCOUNT.ACTIVITY.LABEL.VERIFIED',
            self::PASSWORD_RESET    => 'ACCOUNT.ACTIVITY.LABEL.PASSWORD_RESET',
            self::LOGGED_IN         => 'ACCOUNT.ACTIVITY.LABEL.LOGGED_IN',
            self::LOGGED_OUT        => 'ACCOUNT.ACTIVITY.LABEL.LOGGED_OUT',
            self::PASSWORD_UPGRADED => 'ACCOUNT.ACTIVITY.LABEL.PASSWORD_UPGRADED',
            default                 => null,
        };
    }
}
