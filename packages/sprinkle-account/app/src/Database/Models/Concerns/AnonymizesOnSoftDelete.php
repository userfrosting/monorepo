<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Database\Models\Concerns;

use Illuminate\Database\Eloquent\SoftDeletes;
use UserFrosting\I18n\Translator;

/**
 * Anonymizes unique / personal columns when a model is soft deleted, so the
 * same identifiers can be reused by a new record while the soft deleted row
 * stays in the database (activities keep pointing to it).
 *
 * Models using this trait must implement getAnonymizedAttributes() and isReservedIdentifier().
 */
trait AnonymizesOnSoftDelete
{
    use SoftDeletes;

    /**
     * @var array<string, mixed> Attributes as they were before anonymization.
     */
    protected array $preDeleteAttributes = [];

    /**
     * Get the attributes as they were right before the soft delete.
     *
     * @return array<string, mixed>
     */
    public function getPreDeleteAttributes(): array
    {
        return $this->preDeleteAttributes;
    }

    /**
     * Attributes to overwrite when the model is soft deleted.
     * This should return an array of column names and their anonymized values.
     *
     * @return array<string, mixed>
     */
    abstract protected function getAnonymizedAttributes(): array;

    /**
     * Whether the identifier is reserved for anonymized records.
     * 
     * @param string $value The value to check.
     * @param string $identifier The identifier column name.
     *
     * @return bool Whether the identifier is reserved for anonymized records.
     */
    abstract protected static function isReservedIdentifier(string $value, string $identifier): bool;

    /**
     * {@inheritDoc}
     *
     * Identifiers reserved for anonymized records are always reported as
     * taken, so they can't collide with a future anonymization.
     */
    public static function findUnique(string $value, string $identifier, bool $checkDeleted = true): ?static
    {
        if (static::isReservedIdentifier($value, $identifier)) {
            return new static(); // @phpstan-ignore new.static
        }

        return parent::findUnique($value, $identifier, $checkDeleted);
    }

    /**
     * Translate a placeholder key when this model has been soft deleted.
     */
    protected function translateIfDeleted(string $key, ?string $value): string
    {
        if ($value === $key && $this->getAttribute('deleted_at') !== null) {
            $translator = static::$ci?->get(Translator::class);
            if ($translator instanceof Translator) {
                return $translator->translate($key);
            }
        }

        return $value ?? '';
    }

    /**
     * Perform the soft delete query, anonymizing the model in the same query.
     */
    protected function runSoftDelete(): void
    {
        $this->preDeleteAttributes = $this->getAttributes();

        $time = $this->freshTimestamp();
        $attributes = array_merge($this->getAnonymizedAttributes(), [$this->getDeletedAtColumn() => $time]);

        if ($this->usesTimestamps() && $this->getUpdatedAtColumn() !== null) {
            $attributes[$this->getUpdatedAtColumn()] = $time;
        }

        $this->forceFill($attributes);

        $columns = [];
        foreach ($attributes as $column => $value) {
            $columns[$column] = $value instanceof \DateTimeInterface ? $this->fromDateTime($value) : $value;
        }

        // @phpstan-ignore argument.type
        $this->setKeysForSaveQuery($this->newModelQuery())->update($columns);
        $this->syncOriginalAttributes(array_keys($columns));
        $this->fireModelEvent('trashed', false);
    }
}
