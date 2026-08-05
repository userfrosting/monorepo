<?php

declare(strict_types=1);

/*
 * UserFrosting Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-admin
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Admin\Sprunje;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\ActivityInterface;
use UserFrosting\Sprinkle\Core\Sprunje\Sprunje;

/**
 * Implements Sprunje for the activities API.
 */
class ActivitySprunje extends Sprunje
{
    protected string $name = 'activities';

    protected array $sortable = [
        'occurred_at',
        'user',
        'description',
    ];

    protected array $filterable = [
        'occurred_at',
        'user',
        'description',
    ];

    public function __construct(
        protected ActivityInterface $activityModel,
    ) {
        parent::__construct();
    }

    /**
     * Set the initial query used by your Sprunje.
     * {@inheritDoc}
     */
    protected function baseQuery()
    {
        // @phpstan-ignore-next-line Activity interface mixin Model and non-static method.
        return $this->activityModel->joinUser();
    }

    /**
     * Filter LIKE the user info.
     *
     * @param EloquentBuilder|QueryBuilder|Relation $query
     * @param string                                $value
     *
     * @return static
     */
    protected function filterUser($query, string $value): static
    {
        // Split value on separator for OR queries
        $values = explode($this->orSeparator, $value);
        $query->where(function ($query) use ($values) {
            foreach ($values as $value) {
                $query->orLike('users.first_name', $value)
                    ->orLike('users.last_name', $value)
                    ->orLike('users.email', $value);
            }
        });

        return $this;
    }

    /**
     * Sort based on user last name.
     *
     * @param EloquentBuilder|QueryBuilder|Relation $query
     * @param string                                $direction
     *
     * @return static
     */
    protected function sortUser($query, string $direction): static
    {
        $query->orderBy('users.last_name', $direction);

        return $this;
    }

    /**
     * Sort based on activity occurred_at.
     *
     * @param EloquentBuilder|QueryBuilder|Relation $query
     * @param string                                $direction
     *
     * @return static
     */
    protected function sortOccurredAt($query, string $direction): static
    {
        $query->orderBy('activities.occurred_at', $direction)
              ->orderBy('activities.id', $direction);

        return $this;
    }
}
