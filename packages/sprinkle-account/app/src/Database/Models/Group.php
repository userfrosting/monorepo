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

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use UserFrosting\Sprinkle\Account\Database\Factories\GroupFactory;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\GroupInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Core\Database\Models\Model;

/**
 * Group Model.
 *
 * Represents a group object as stored in the database.
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Group extends Model implements GroupInterface
{
    use SoftDeletes;
    use HasFactory;

    /**
     * @var string The name of the table for the current model.
     */
    protected $table = 'groups';

    /**
     * @var array<int, string> The attributes that are mass assignable.
     */
    protected $fillable = [
        'slug',
        'name',
        'description',
        'icon',
        'deleted_at',
    ];

    /**
     * @var string[] The attributes that should be mutated to dates.
     */
    protected $dates = [
        'deleted_at',
    ];

    /**
     * Cast nullable description to empty string if null.
     *
     * @param string|null $value
     *
     * @return string
     */
    public function getDescriptionAttribute(?string $value): string
    {
        return $value ?? '';
    }

    /**
     * Lazily load a collection of Users which belong to this group.
     *
     * @return HasMany
     */
    public function users(): HasMany
    {
        /** @var string */
        $relation = static::$ci?->get(UserInterface::class);

        return $this->hasMany($relation, 'group_id');
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return Factory<Group>
     */
    protected static function newFactory(): Factory
    {
        return GroupFactory::new();
    }
}
