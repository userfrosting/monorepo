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

use UserFrosting\Support\ClassRepositoryInterface;

/**
 * Find all registered activity type enums and resolve their i18n keys.
 *
 * @extends ClassRepositoryInterface<ActivityTypes>
 */
interface ActivityTypeRegistryInterface extends ClassRepositoryInterface
{
    /**
     * Return the i18n key for a persisted activity type value.
     *
     * @param string $value
     *
     * @return string|null
     */
    public function getI18nKey(string $value): ?string;
}
