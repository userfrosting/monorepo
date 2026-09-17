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

use BackedEnum;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use UserFrosting\I18n\DictionaryInterface;
use UserFrosting\I18n\Translator;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\ActivityInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Log\ActivityTypeRegistryInterface;
use UserFrosting\Sprinkle\Core\Database\Models\Interfaces\MorphableModelInterface;
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
        'label',
    ];

    protected array $listable = [
        'label',
    ];

    protected array $filterable = [
        'occurred_at',
        'user',
        'label',
    ];

    /** @var array<string, string>|null */
    protected ?array $activityLabels = null;

    protected ?DictionaryInterface $dictionary = null;

    public function __construct(
        protected ActivityInterface $activityModel,
        protected ActivityTypeRegistryInterface $activityTypeRegistry,
        protected Translator $translator,
    ) {
        parent::__construct();
    }

    /**
     * Limit the results to a specific activity type.
     *
     * @param string $type
     *
     * @return static
     */
    public function forType(string $type): static
    {
        return $this->extendQuery(function ($query) use ($type) {
            return $query->where('activities.type', $type);
        });
    }

    /**
     * Limit the results to activities performed by a user.
     *
     * @param UserInterface|int $user
     *
     * @return static
     */
    public function forUser(UserInterface|int $user): static
    {
        $userId = $user instanceof UserInterface ? $user->getKey() : $user;

        return $this->extendQuery(function ($query) use ($userId) {
            return $query->where('activities.user_id', $userId);
        });
    }

    /**
     * Limit the results to activities associated with a context.
     *
     * @param MorphableModelInterface|string $context A model or its morph type.
     * @param int|string|null                $key     The model key when using a morph type.
     *
     * @throws InvalidArgumentException If a morph type is provided without a key.
     * @return static
     */
    public function forContext(MorphableModelInterface|string $context, int|string|null $key = null): static
    {
        [$type, $id] = $this->resolveMorphTarget($context, $key);

        return $this->extendQuery(function ($query) use ($type, $id) {
            return $query
                ->where('activities.context_type', $type)
                ->where('activities.context_id', $id);
        });
    }

    /**
     * Limit the results to activities associated with a subject.
     *
     * @param MorphableModelInterface|string $subject A model or its morph type.
     * @param int|string|null                $key     The model key when using a morph type.
     *
     * @throws InvalidArgumentException If a morph type is provided without a key.
     * @return static
     */
    public function forSubject(MorphableModelInterface|string $subject, int|string|null $key = null): static
    {
        [$type, $id] = $this->resolveMorphTarget($subject, $key);

        return $this->extendQuery(function ($query) use ($type, $id) {
            return $query
                ->where('activities.subject_type', $type)
                ->where('activities.subject_id', $id);
        });
    }

    /**
     * Limit the results to activities matching any supplied target.
     *
     * @param UserInterface|int|null       $user
     * @param MorphableModelInterface|null $context
     * @param MorphableModelInterface|null $subject
     *
     * @return static
     */
    public function forAny(
        UserInterface|int|null $user = null,
        ?MorphableModelInterface $context = null,
        ?MorphableModelInterface $subject = null,
    ): static {
        if ($user === null && $context === null && $subject === null) {
            return $this;
        }

        $userId = $user instanceof UserInterface ? $user->getKey() : $user;

        return $this->extendQuery(function ($query) use ($userId, $context, $subject) {
            return $query->where(function ($query) use ($userId, $context, $subject) {
                $hasCondition = false;

                if ($userId !== null) {
                    $query->where('activities.user_id', $userId);
                    $hasCondition = true;
                }

                if ($context !== null) {
                    $contextQuery = function ($query) use ($context) {
                        $query
                            ->where('activities.context_type', $context->getMorphClass())
                            ->where('activities.context_id', $context->getKey());
                    };
                    if ($hasCondition) {
                        $query->orWhere($contextQuery);
                    } else {
                        $query->where($contextQuery);
                    }
                    $hasCondition = true;
                }

                if ($subject !== null) {
                    $subjectQuery = function ($query) use ($subject) {
                        $query
                            ->where('activities.subject_type', $subject->getMorphClass())
                            ->where('activities.subject_id', $subject->getKey());
                    };
                    if ($hasCondition) {
                        $query->orWhere($subjectQuery);
                    } else {
                        $query->where($subjectQuery);
                    }
                }
            });
        });
    }

    /**
     * Resolve a polymorphic model or explicit type/key pair.
     *
     * @param MorphableModelInterface|string $target
     * @param int|string|null                $key
     *
     * @throws InvalidArgumentException  If the target key is missing or non-scalar.
     * @return array{string, int|string}
     */
    protected function resolveMorphTarget(MorphableModelInterface|string $target, int|string|null $key): array
    {
        if ($target instanceof MorphableModelInterface) {
            if ($key !== null) {
                throw new InvalidArgumentException('A model target cannot be combined with an explicit key.');
            }

            $key = $target->getKey();
            $target = $target->getMorphClass();
        }

        if ($key === null) {
            throw new InvalidArgumentException('A polymorphic target requires both a type and a key.');
        }

        return [$target, $key];
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
            if ($activity->getAttribute('user_id') === null) {
                $activity->setRelation('user', null);
            } else {
                $activity->load([
                    'user' => function ($query): void {
                        $query->withTrashed();
                    },
                ]);
            }

            $type = (string) $activity->getAttribute('type');
            $activity->setAttribute('label', $this->getActivityLabel($type));

            $i18nKey = $this->activityTypeRegistry->getI18nKey($type);
            $dictionary = $this->getDictionary();

            if ($i18nKey === null || !$dictionary->has($i18nKey)) {
                $conventionKey = 'ACTIVITY.TYPE.' . $type;
                $i18nKey = $dictionary->has($conventionKey)
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
     * Resolve a concise localized label for a persisted activity type value.
     */
    protected function getActivityLabel(string $value): string
    {
        $i18nKey = $this->activityTypeRegistry->getLabelI18nKey($value);
        if ($i18nKey === null || !$this->getDictionary()->has($i18nKey)) {
            return $value;
        }

        return $this->translator->translate($i18nKey);
    }

    /**
     * Return localized labels indexed by their persisted activity type values.
     *
     * @return array<string, string>
     */
    protected function getActivityLabels(): array
    {
        if ($this->activityLabels !== null) {
            return $this->activityLabels;
        }

        $activityLabels = [];
        foreach ($this->activityTypeRegistry->all() as $activityType) {
            if (!$activityType instanceof BackedEnum) {
                continue;
            }

            $value = (string) $activityType->value;
            $activityLabels[$value] = $this->getActivityLabel($value);
        }

        return $this->activityLabels = $activityLabels;
    }

    /**
     * Return the possible localized activity labels.
     *
     * @return array{value: string, text: string}[]
     */
    protected function listLabel(): array
    {
        /** @var array<string, string[]> $valuesByLabel */
        $valuesByLabel = [];
        foreach ($this->getActivityLabels() as $value => $text) {
            $valuesByLabel[$text][] = $value;
        }

        /** @var array{value: string, text: string}[] $labels */
        $labels = [];
        foreach ($valuesByLabel as $text => $values) {
            $labels[] = [
                'value' => implode($this->orSeparator, $values),
                'text'  => $text,
            ];
        }

        usort($labels, static function (array $left, array $right): int {
            $textComparison = strcasecmp($left['text'], $right['text']);

            return $textComparison !== 0
                ? $textComparison
                : strcmp($left['value'], $right['value']);
        });

        return $labels;
    }

    /**
     * Filter by persisted activity type values represented by localized labels.
     *
     * @param EloquentBuilder|QueryBuilder|Relation $query
     * @param string                                $value
     *
     * @return static
     */
    protected function filterLabel($query, string $value): static
    {
        $values = array_values(array_intersect(
            explode($this->orSeparator, $value),
            array_keys($this->getActivityLabels())
        ));
        // @phpstan-ignore-next-line - Eloquent builders expose whereIn dynamically through this union.
        $query->whereIn('activities.type', $values);

        return $this;
    }

    /**
     * Sort by localized activity labels using persisted type values.
     *
     * @param EloquentBuilder|QueryBuilder|Relation $query
     * @param string                                $direction
     *
     * @return static
     */
    protected function sortLabel($query, string $direction): static
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';
        $cases = [];
        $bindings = [];

        foreach ($this->getActivityLabels() as $value => $label) {
            $cases[] = 'WHEN activities.type = ? THEN ?';
            $bindings[] = $value;
            $bindings[] = $label;
        }

        if ($cases !== []) {
            // @phpstan-ignore-next-line - Eloquent builders expose orderByRaw dynamically through this union.
            $query->orderByRaw(
                'CASE ' . implode(' ', $cases) . ' ELSE activities.type END ' . $direction,
                $bindings
            );
        }

        // @phpstan-ignore-next-line - Eloquent builders expose orderBy dynamically through this union.
        $query->orderBy('activities.type', $direction)
            ->orderBy('activities.id', $direction);

        return $this;
    }

    protected function getDictionary(): DictionaryInterface
    {
        return $this->dictionary ??= $this->translator->getDictionary();
    }

    /**
     * Set the initial query used by your Sprunje.
     * {@inheritDoc}
     */
    protected function baseQuery(): EloquentBuilder
    {
        // @phpstan-ignore-next-line staticMethod.dynamicCall
        $query = $this->activityModel->joinUser();

        return $query
            ->with(['context', 'subject']);
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
