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

enum GroupActivityTypes: string implements ActivityTypes
{
    case CREATE = 'group_create';
    case DELETE = 'group_delete';
    case UPDATE_INFO = 'group_update_info';

    /**
     * {@inheritDoc}
     */
    public static function getI18nKey(string $value): ?string
    {
        return match (self::tryFrom($value)) {
            self::CREATE      => 'GROUP.ACTIVITY.CREATE',
            self::DELETE      => 'GROUP.ACTIVITY.DELETE',
            self::UPDATE_INFO => 'GROUP.ACTIVITY.UPDATE_INFO',
            default           => null,
        };
    }

    /**
     * {@inheritDoc}
     */
    public static function getLabelI18nKey(string $value): ?string
    {
        return match (self::tryFrom($value)) {
            self::CREATE      => 'GROUP.ACTIVITY.LABEL.CREATE',
            self::DELETE      => 'GROUP.ACTIVITY.LABEL.DELETE',
            self::UPDATE_INFO => 'GROUP.ACTIVITY.LABEL.UPDATE_INFO',
            default           => null,
        };
    }
}
