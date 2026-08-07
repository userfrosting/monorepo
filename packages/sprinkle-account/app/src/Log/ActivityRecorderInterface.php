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
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\ActivityInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Core\Database\Models\Interfaces\MorphableModelInterface;

interface ActivityRecorderInterface
{
    /**
     * Record one activity event.
     *
     * The $user is the one doing the action, while the $context and $subject
     * are the models affected by the action.
     *
     * For example, if a user comments on a post, the context would be the post,
     * and the subject would be the comment. A record can be displayed for user,
     * context, or subject, (eg. show activities related to a specific post,
     * comment, or a specific user).
     *
     * Context and subject are optional. For example, if a user logs in, there
     * is no context or subject.
     *
     * Metadata is an optional array of additional information to store with
     * the activity and time related to the activity. For example, renaming a
     * post could have metadata containing the old and new name of the post.
     *
     * @param UserInterface                           $user     The actor responsible for the activity.
     * @param BackedEnum                              $type     The action being logged. The type key (eg. `user_created`) will be used to retrieve the activity template and i18n for rendering.
     * @param array<string, scalar|array<mixed>|null> $metadata Additional event payload for UI rendering.
     * @param MorphableModelInterface|null            $context  Primary related model (polymorphic context).
     * @param MorphableModelInterface|null            $subject  Secondary related model (polymorphic subject).
     *
     * @return ActivityInterface
     */
    public function record(
        UserInterface $user,
        BackedEnum $type,
        array $metadata = [],
        ?MorphableModelInterface $context = null,
        ?MorphableModelInterface $subject = null,
    ): ActivityInterface;
}
