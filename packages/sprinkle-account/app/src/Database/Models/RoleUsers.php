<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Database\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use UserFrosting\Sprinkle\Core\Database\Models\Interfaces\MorphableModelInterface;

/**
 * Represents a the User-Role many-to-many relationship intermediate table.
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class RoleUsers extends Pivot implements MorphableModelInterface
{
    /**
     * @var string The name of the table for the current model.
     */
    protected $table = 'role_users';
}
