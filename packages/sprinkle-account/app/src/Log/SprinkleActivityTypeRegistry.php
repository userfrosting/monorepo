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
use LogicException;
use UserFrosting\Sprinkle\Account\Sprinkle\Recipe\ActivityRecipe;
use UserFrosting\Sprinkle\SprinkleManager;
use UserFrosting\Support\ClassRepository;
use UserFrosting\Support\Exception\BadClassNameException;
use UserFrosting\Support\Exception\BadInstanceOfException;

/**
 * Find all registered activity type enums across all Sprinkles.
 *
 * @extends ClassRepository<ActivityTypes>
 */
final class SprinkleActivityTypeRegistry extends ClassRepository implements ActivityTypeRegistryInterface
{
    /**
     * @var ActivityTypes[]|null
     */
    protected ?array $activityTypes = null;

    /**
     * @var array<int|string, ActivityTypes>|null
     */
    protected ?array $activityTypesByValue = null;

    public function __construct(
        protected SprinkleManager $sprinkleManager,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function all(): array
    {
        $this->loadActivityTypes();

        return $this->activityTypes ?? [];
    }

    /**
     * {@inheritDoc}
     */
    public function getI18nKey(string $value): ?string
    {
        $this->loadActivityTypes();

        $activityType = $this->activityTypesByValue[$value] ?? null;
        if ($activityType === null) {
            return null;
        }

        return get_class($activityType)::getI18nKey($value);
    }

    /**
     * Expand the activity enum classes registered by all loaded Sprinkles.
     */
    protected function loadActivityTypes(): void
    {
        if ($this->activityTypes !== null) {
            return;
        }

        /** @var ActivityTypes[] $activityTypes */
        $activityTypes = [];

        /** @var array<int|string, ActivityTypes> $activityTypesByValue */
        $activityTypesByValue = [];

        foreach ($this->sprinkleManager->getSprinkles() as $sprinkle) {
            if (!$sprinkle instanceof ActivityRecipe) {
                continue;
            }

            foreach ($sprinkle->getActivityTypes() as $activityTypeClass) {
                foreach ($this->getActivityTypeCases($activityTypeClass) as $activityType) {
                    if (isset($activityTypesByValue[$activityType->value])) {
                        throw new LogicException("Activity type value `{$activityType->value}` is registered more than once.");
                    }

                    $activityTypes[] = $activityType;
                    $activityTypesByValue[$activityType->value] = $activityType;
                }
            }
        }

        $this->activityTypes = $activityTypes;
        $this->activityTypesByValue = $activityTypesByValue;
    }

    /**
     * Validate a registered class and return all of its enum cases.
     *
     * @param string $activityTypeClass
     *
     * @return array<int, BackedEnum&ActivityTypes>
     */
    protected function getActivityTypeCases(string $activityTypeClass): array
    {
        if (!class_exists($activityTypeClass)) {
            throw new BadClassNameException("Activity type class `$activityTypeClass` not found.");
        }

        if (!is_subclass_of($activityTypeClass, BackedEnum::class) || !is_subclass_of($activityTypeClass, ActivityTypes::class)) {
            throw new BadInstanceOfException("Activity type class `$activityTypeClass` doesn't implement " . ActivityTypes::class . ' and ' . BackedEnum::class . '.');
        }

        /** @var class-string<BackedEnum&ActivityTypes> $activityTypeClass */
        return $activityTypeClass::cases();
    }
}
