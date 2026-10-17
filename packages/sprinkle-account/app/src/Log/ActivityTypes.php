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

interface ActivityTypes
{
    /**
     * Return the i18n key for a persisted activity type value.
     *
     * @param string $value
     *
     * @return string|null
     */
    public static function getI18nKey(string $value): ?string;

    /**
     * Return the i18n key for a concise label for a persisted activity type value.
     *
     * @param string $value
     *
     * @return string|null
     */
    public static function getLabelI18nKey(string $value): ?string;
}
