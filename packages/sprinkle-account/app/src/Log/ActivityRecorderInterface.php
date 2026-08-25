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
     * The $user is the actor performing the action (caused the activity). The
     * $subject is the model the action is performed on, while the optional
     * $context is a third related model that gives the activity additional
     * context.
     *
     * For example, if a user comments on a post, the subject would be the
     * comment and the context would be the post. A record can be displayed for user,
     * context, or subject, (eg. show activities related to a specific post,
     * comment, or a specific user).
     *
     * Context and subject are optional. For example, if a user logs in, there
     * is no context or subject.
     *
     * Metadata is an optional array of translation placeholders. Properties
     * are derived automatically from the subject's dirty attributes and store
     * their old and new values.
     *
     * @param UserInterface|null                      $user     The actor responsible for the activity, or null for CLI/unauthenticated actions.
     * @param BackedEnum                              $type     The action being logged. The type key (eg. `user_created`) will be used to retrieve the activity template and i18n for rendering.
     * @param array<string, scalar|array<mixed>|null> $metadata Additional placeholders passed to the translator.
     * @param MorphableModelInterface|null            $context  Optional third related model (polymorphic context).
     * @param MorphableModelInterface|null            $subject  Model the action is performed on (polymorphic subject).
     *
     * @return ActivityInterface
     */
    public function record(
        ?UserInterface $user,
        BackedEnum $type,
        array $metadata = [],
        ?MorphableModelInterface $context = null,
        ?MorphableModelInterface $subject = null,
    ): ActivityInterface;
}
