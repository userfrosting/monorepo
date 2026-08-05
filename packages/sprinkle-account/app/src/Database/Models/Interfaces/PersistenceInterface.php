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

use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as QueryBuilder;
use UserFrosting\Sprinkle\Core\Database\Models\Model;

/**
 * Persistence db Model Interface.
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @mixin \Illuminate\Database\Eloquent\Model
 * @mixin \UserFrosting\Sprinkle\Core\Database\Models\Model
 *
 * @property int                $id
 * @property int                $user_id
 * @property string             $token
 * @property string             $persistent_token
 * @property DateTime|null      $expires_at
 * @property \DateTimeImmutable $created_at
 * @property \DateTimeImmutable $updated_at
 * @property UserInterface      $user
 *
 * @method        $this notExpired()
 * @method static $this notExpired()
 */
interface PersistenceInterface
{
    /**
     * Relation with the user table.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo;

    /**
     * Scope a query to only include not expired entries.
     *
     * @param Builder $query
     *
     * @return Builder|QueryBuilder
     */
    public function scopeNotExpired(Builder $query): Builder|QueryBuilder;
}
