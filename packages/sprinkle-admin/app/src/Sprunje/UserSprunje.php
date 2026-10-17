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

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use UserFrosting\I18n\Translator;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\ActivityInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Log\ActivityTypeRegistryInterface;
use UserFrosting\Sprinkle\Core\Sprunje\Sprunje;

/**
 * Implements Sprunje for the users API.
 */
class UserSprunje extends Sprunje
{
    protected string $name = 'users';

    protected array $listable = [
        'status',
    ];

    protected array $sortable = [
        'name',
        'last_activity',
        'status',
    ];

    protected array $filterable = [
        'name',
        'last_activity',
        'status',
    ];

    protected array $excludeForAll = [
        'last_activity',
    ];

    public function __construct(
        protected UserInterface $userModel,
        protected ActivityInterface $activityModel,
        protected ActivityTypeRegistryInterface $activityTypeRegistry,
        protected Translator $translator,
    ) {
        parent::__construct();
    }

    /**
     * Add the composed activity description to each user's last activity.
     *
     * @return array<string, mixed>
     */
    public function getArray(): array
    {
        $data = parent::getArray();
        $activityIds = [];

        // Collect all activity IDs from the last_activity field to efficiently
        // load them in a single query.
        foreach ($data['rows'] as $index => $row) {
            if (!is_array($row) || !is_array($row['last_activity'] ?? null)) {
                continue;
            }

            $id = $row['last_activity']['id'] ?? null;
            if (is_int($id) || (is_string($id) && ctype_digit($id))) {
                $activityIds[] = (int) $id;
            }
        }

        // If no activity IDs were collected, return early.
        if ($activityIds === []) {
            return $data;
        }

        // Load all activities corresponding to the collected IDs, including
        // their context and subject relations.
        $activities = $this->activityModel->newQuery()
            ->with(['context', 'subject'])
            ->findMany(array_unique($activityIds))
            ->keyBy('id');

        // Attach the composed activity description to each user's last activity.
        foreach ($data['rows'] as $index => $row) {
            if (!is_array($row) || !is_array($row['last_activity'] ?? null)) {
                continue;
            }

            $id = $row['last_activity']['id'] ?? null;
            $activityId = is_int($id) || (is_string($id) && ctype_digit($id))
                ? (int) $id
                : null;
            $activity = $activityId === null ? null : $activities->get($activityId);
            if ($activity instanceof ActivityInterface) {
                $data['rows'][$index]['last_activity']['description'] = $activity->getComposedDescription(
                    $this->activityTypeRegistry,
                    $this->translator
                );
            }
        }

        return $data;
    }

    /**
     * Join user's most recent activity
     * {@inheritdoc}
     */
    protected function baseQuery(): EloquentBuilder|QueryBuilder|Relation|Model
    {
        // @phpstan-ignore-next-line Activity interface mixin Model and non-static method.
        return $this->userModel->joinLastActivity();
    }

    /**
     * Filter LIKE the last activity description.
     *
     * @param EloquentBuilder|QueryBuilder|Relation $query
     * @param string                                $value
     *
     * @return static
     */
    protected function filterLastActivity($query, string $value): static
    {
        // Split value on separator for OR queries
        $values = explode($this->orSeparator, $value);
        $query->where(function ($query) use ($values) {
            foreach ($values as $value) {
                $query->orLike('activities.description', $value);
            }
        });

        return $this;
    }

    /**
     * Filter LIKE the first name, last name, or email.
     *
     * @param EloquentBuilder|QueryBuilder|Relation $query
     * @param string                                $value
     *
     * @return static
     */
    protected function filterName($query, string $value): static
    {
        // Split value on separator for OR queries
        $values = explode($this->orSeparator, $value);
        $query->where(function ($query) use ($values) {
            foreach ($values as $value) {
                $query->orLike('first_name', $value)
                        ->orLike('last_name', $value)
                        ->orLike('email', $value);
            }
        });

        return $this;
    }

    /**
     * Filter by status (active, disabled, unactivated).
     *
     * @param EloquentBuilder|QueryBuilder|Relation $query
     * @param string                                $value
     *
     * @return static
     */
    protected function filterStatus($query, string $value): static
    {
        // Split value on separator for OR queries
        $values = explode($this->orSeparator, $value);
        $query->where(function ($query) use ($values) {
            foreach ($values as $value) {
                if ($value === 'disabled') {
                    $query->orWhere('flag_enabled', 0);
                } elseif ($value === 'unactivated') {
                    $query->orWhere('flag_verified', 0);
                } elseif ($value === 'active') {
                    $query->orWhere(function ($query) {
                        $query->where('flag_enabled', 1)->where('flag_verified', 1);
                    });
                }
            }
        });

        return $this;
    }

    /**
     * Return a list of possible user statuses.
     *
     * @return array{value: string, text: string}[]
     */
    protected function listStatus(): array
    {
        return [
            [
                'value' => 'active',
                'text'  => $this->translator->translate('ACTIVE'),
            ],
            [
                'value' => 'unactivated',
                'text'  => $this->translator->translate('UNACTIVATED'),
            ],
            [
                'value' => 'disabled',
                'text'  => $this->translator->translate('DISABLED'),
            ],
        ];
    }

    /**
     * Sort based on last activity time.
     *
     * @param EloquentBuilder|QueryBuilder|Relation $query
     * @param string                                $direction
     *
     * @return static
     */
    protected function sortLastActivity($query, string $direction): static
    {
        $query->orderBy('last_activity', $direction);

        return $this;
    }

    /**
     * Sort based on last name.
     *
     * @param EloquentBuilder|QueryBuilder|Relation $query
     * @param string                                $direction
     *
     * @return static
     */
    protected function sortName($query, string $direction): static
    {
        $query->orderBy('last_name', $direction);

        return $this;
    }

    /**
     * Sort active, unactivated, disabled.
     *
     * @param EloquentBuilder|QueryBuilder|Relation $query
     * @param string                                $direction
     *
     * @return static
     */
    protected function sortStatus($query, string $direction): static
    {
        $query->orderBy('flag_enabled', $direction)->orderBy('flag_verified', $direction);

        return $this;
    }

    /**
     * Count needs to be done without the joined query.
     *
     * @see https://stackoverflow.com/a/10475248/445757
     * @see https://oliverlundquist.com/2018/03/06/laravel-wrap-query-and-get-count-from-subquery.html
     *
     * {@inheritdoc}
     */
    protected function count($query): int
    {
        $countQuery = "select count(*) as aggregate from ({$query->toSql()}) c";

        // @phpstan-ignore-next-line Laravel magick methods...
        $count = collect(Capsule::select($countQuery, $query->getBindings()))->pluck('aggregate')->first();

        return intval($count);
    }

    /**
     * Count needs to be done without the joined query.
     * {@inheritdoc}
     */
    protected function countFiltered($query): int
    {
        return $this->count($query);
    }
}
