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

enum AdminActivityTypes: string implements ActivityTypes
{
    case CACHE_CLEARED = 'cache_cleared';

    /**
     * {@inheritDoc}
     */
    public static function getI18nKey(string $value): ?string
    {
        return match (self::tryFrom($value)) {
            self::CACHE_CLEARED => 'ADMIN.ACTIVITY.CACHE_CLEARED',
            default             => null,
        };
    }

    /**
     * {@inheritDoc}
     */
    public static function getLabelI18nKey(string $value): ?string
    {
        return match (self::tryFrom($value)) {
            self::CACHE_CLEARED => 'ADMIN.ACTIVITY.LABEL.CACHE_CLEARED',
            default             => null,
        };
    }
}
