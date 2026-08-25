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
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Query\Builder as QueryBuilder;
use UserFrosting\Sprinkle\Core\Database\Models\Interfaces\MorphableModelInterface;
use UserFrosting\Sprinkle\Core\Database\Models\Model;

/**
 * Activity Model Interface.
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @mixin \Illuminate\Database\Eloquent\Model
 * @mixin \UserFrosting\Sprinkle\Core\Database\Models\Model
 *
 * @property int                       $id
 * @property string|null               $ip_address
 * @property int|null                  $user_id
 * @property string                    $type
 * @property Datetime|null             $occurred_at
 * @property string                    $description  @deprecated 6.1
 * @property UserInterface|null        $user
 * @property string|null               $context_type
 * @property string|null               $context_id
 * @property string|null               $subject_type
 * @property string|null               $subject_id
 * @property array<string, mixed>|null $metadata
 * @property array<string, mixed>|null $properties
 * @property-read UserInterface|null   $user
 *
 * @method        $this   joinUser()
 * @method static $this   joinUser()
 * @method        Builder newQuery()
 */
interface ActivityInterface extends MorphableModelInterface
{
    /**
     * Users which belong to this activity.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo;

    /**
     * Get the optional third model related to this activity.
     */
    public function context(): MorphTo;

    /**
     * Get the model this activity was performed on.
     */
    public function subject(): MorphTo;

    /**
     * Scope a query to only include specific type.
     *
     * @param Builder $query
     *
     * @return Builder|QueryBuilder
     */
    public function scopeForType(Builder $query, string $type): Builder|QueryBuilder;

    /**
     * Joins the activity's user, so we can do things like sort, search, paginate, etc. in the Sprunje.
     *
     * @param Builder $query
     *
     * @return Builder|QueryBuilder
     */
    public function scopeJoinUser(Builder $query): Builder|QueryBuilder;
}
