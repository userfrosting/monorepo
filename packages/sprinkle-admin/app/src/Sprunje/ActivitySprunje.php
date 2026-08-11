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
use Illuminate\Support\Collection;
use UserFrosting\I18n\Translator;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\ActivityInterface;
use UserFrosting\Sprinkle\Account\Log\ActivityTypeRegistryInterface;
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
        protected ActivityTypeRegistryInterface $activityTypeRegistry,
        protected Translator $translator,
    ) {
        parent::__construct();
    }

    /**
     * Translate activity types after the database query has been executed.
     *
     * @param Collection<int, Model> $collection
     *
     * @return Collection<int, Model>
     */
    protected function applyTransformations(Collection $collection): Collection
    {
        return $collection->each(function (Model $activity): void {
            $type = (string) $activity->getAttribute('type');
            $i18nKey = $this->activityTypeRegistry->getI18nKey($type);

            if ($i18nKey === null || !$this->translator->getDictionary()->has($i18nKey)) {
                $conventionKey = 'ACTIVITY.TYPE.' . $type;
                $i18nKey = $this->translator->getDictionary()->has($conventionKey)
                    ? $conventionKey
                    : null;
            }

            if ($i18nKey !== null) {
                $placeholders = $activity->getAttribute('metadata');
                if (!is_array($placeholders)) {
                    $placeholders = [];
                }
                $placeholders['context'] = $activity->getRelationValue('context');
                $placeholders['subject'] = $activity->getRelationValue('subject');

                $activity->setAttribute(
                    'description',
                    $this->translator->translate($i18nKey, $placeholders)
                );

                return;
            }

            $description = $activity->getAttribute('description');
            if (!is_string($description) || $description === '') {
                $activity->setAttribute('description', $type);
            }
        });
    }

    /**
     * Set the initial query used by your Sprunje.
     * {@inheritDoc}
     */
    protected function baseQuery(): EloquentBuilder
    {
        // @phpstan-ignore-next-line Activity interface mixin Model and non-static method.
        $query = $this->activityModel->newQuery();
        $query->getQuery()
            ->leftJoin('users', 'activities.user_id', '=', 'users.id')
            ->select('activities.*');

        return $query
            ->with(['user' => function ($query) {
                $query->withTrashed();
            }, 'context', 'subject'])
            ->latest('occurred_at');
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
