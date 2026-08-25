<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Log;

use BackedEnum;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use UserFrosting\Sprinkle\Account\Database\Models\Activity;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Core\Database\Models\Interfaces\MorphableModelInterface;

class ActivityRecorder implements ActivityRecorderInterface
{
    /**
     * {@inheritDoc}
     */
    public function record(
        ?UserInterface $user,
        BackedEnum $type,
        array $metadata = [],
        ?MorphableModelInterface $context = null,
        ?MorphableModelInterface $subject = null,
    ): Activity {
        $activity = new Activity([
            'ip_address'   => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null,
            'user_id'      => $user?->getKey(),
            'context_type' => $context?->getMorphClass(),
            'context_id'   => $context?->getKey(),
            'subject_type' => $subject?->getMorphClass(),
            'subject_id'   => $subject?->getKey(),
            'type'         => $type->value,
            'metadata'     => $metadata,
            'properties'   => $this->getProperties($subject),
            'occurred_at'  => new DateTimeImmutable(),
        ]);

        $activity->save();

        return $activity;
    }

    /**
     * Extract safe old and new values from a dirty Eloquent subject.
     *
     * @param MorphableModelInterface|null $subject
     *
     * @return array<string, array{old: mixed, new: mixed}>|null
     */
    protected function getProperties(?MorphableModelInterface $subject): ?array
    {
        if (!$subject instanceof EloquentModel) {
            return null;
        }

        $hidden = $subject->getHidden();
        $properties = [];
        foreach (array_keys($subject->getDirty()) as $key) {
            if (in_array($key, $hidden, true) || $this->isSensitive($key)) {
                continue;
            }

            $properties[$key] = [
                'old' => $this->normalizeValue($subject->getOriginal($key)),
                'new' => $this->normalizeValue($subject->getAttribute($key)),
            ];
        }

        return $properties === [] ? null : $properties;
    }

    /**
     * Determine whether a property should never be recorded.
     */
    protected function isSensitive(string $key): bool
    {
        $key = strtolower($key);

        return $key === 'password'
            || str_contains($key, 'token')
            || str_contains($key, 'secret');
    }

    /**
     * Normalize a value so it can safely be stored in a JSON column.
     */
    protected function normalizeValue(mixed $value): mixed
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        if (is_scalar($value) || $value === null) {
            return $value;
        }

        if (is_array($value)) {
            return array_map(fn (mixed $item): mixed => $this->normalizeValue($item), $value);
        }

        return (string) $value;
    }
}
