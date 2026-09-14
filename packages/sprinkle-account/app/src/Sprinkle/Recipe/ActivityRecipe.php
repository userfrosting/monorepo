<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Sprinkle\Recipe;

use BackedEnum;
use UserFrosting\Sprinkle\Account\Log\ActivityTypes;

interface ActivityRecipe
{
    /**
     * Return an array of all registered activity type enums.
     *
     * @return class-string<BackedEnum&ActivityTypes>[]
     */
    public function getActivityTypes(): array;
}
