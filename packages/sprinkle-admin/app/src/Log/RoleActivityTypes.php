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

enum RoleActivityTypes: string implements ActivityTypes
{
    case CREATE = 'role_create';
    case DELETE = 'role_delete';
    case UPDATE_INFO = 'role_update_info';
    case UPDATE_PERMISSIONS = 'role_update_permissions';
    case UPDATE_FIELD = 'role_update_field';

    /**
     * {@inheritDoc}
     */
    public static function getI18nKey(string $value): ?string
    {
        return match (self::tryFrom($value)) {
            self::CREATE             => 'ROLE.ACTIVITY.CREATE',
            self::DELETE             => 'ROLE.ACTIVITY.DELETE',
            self::UPDATE_INFO        => 'ROLE.ACTIVITY.UPDATE_INFO',
            self::UPDATE_PERMISSIONS => 'ROLE.ACTIVITY.UPDATE_PERMISSIONS',
            self::UPDATE_FIELD       => 'ROLE.ACTIVITY.UPDATE_FIELD',
            default                  => null,
        };
    }

    /**
     * {@inheritDoc}
     */
    public static function getLabelI18nKey(string $value): ?string
    {
        return match (self::tryFrom($value)) {
            self::CREATE             => 'ROLE.ACTIVITY.LABEL.CREATE',
            self::DELETE             => 'ROLE.ACTIVITY.LABEL.DELETE',
            self::UPDATE_INFO        => 'ROLE.ACTIVITY.LABEL.UPDATE_INFO',
            self::UPDATE_PERMISSIONS => 'ROLE.ACTIVITY.LABEL.UPDATE_PERMISSIONS',
            self::UPDATE_FIELD       => 'ROLE.ACTIVITY.LABEL.UPDATE_FIELD',
            default                  => null,
        };
    }
}
