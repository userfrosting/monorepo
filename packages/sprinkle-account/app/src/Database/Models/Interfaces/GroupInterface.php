<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Database\Models\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use UserFrosting\Sprinkle\Core\Database\Models\Model;

/**
 * Group Model Interface.
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @mixin \Illuminate\Database\Eloquent\Model
 * @mixin \UserFrosting\Sprinkle\Core\Database\Models\Model
 *
 * @property int                                  $id
 * @property string                               $slug
 * @property string                               $name
 * @property string                               $description
 * @property string                               $icon
 * @property \DateTimeImmutable                   $created_at
 * @property \DateTimeImmutable                   $updated_at
 * @property Collection<int, UserInterface&Model> $users
 */
interface GroupInterface
{
    /**
     * Users which belong to this group.
     *
     * @return HasMany
     */
    public function users(): HasMany;
}
