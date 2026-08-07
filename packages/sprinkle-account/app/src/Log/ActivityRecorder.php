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
use UserFrosting\Sprinkle\Account\Database\Models\Activity;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Core\Database\Models\Interfaces\MorphableModelInterface;

class ActivityRecorder implements ActivityRecorderInterface
{
    /**
     * {@inheritDoc}
     */
    public function record(
        UserInterface $user,
        BackedEnum $type,
        array $metadata = [],
        ?MorphableModelInterface $context = null,
        ?MorphableModelInterface $subject = null,
    ): Activity {
        $activity = new Activity([
            'ip_address'   => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null,
            'user_id'      => $user->getKey(),
            'context_type' => $context?->getMorphClass(),
            'context_id'   => $context?->getKey(),
            'subject_type' => $subject?->getMorphClass(),
            'subject_id'   => $subject?->getKey(),
            'type'         => $type->value,
            'metadata'     => $metadata,
            'occurred_at'  => new DateTimeImmutable(),
        ]);

        $activity->save();

        return $activity;
    }
}
