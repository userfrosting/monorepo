<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Listener;

use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\GroupInterface;
use UserFrosting\Sprinkle\Account\Event\UserCreatedEvent;
use UserFrosting\Sprinkle\Account\Exceptions\DefaultGroupException;

class AssignDefaultGroups
{
    public function __construct(
        protected Config $config,
        protected GroupInterface $groupModel,
    ) {
    }

    public function __invoke(UserCreatedEvent $event): void
    {
        // TODO : Default group should be defined in the DB instead of config.
        $defaultGroupSlug = $this->config->getString('site.registration.user_defaults.group', '');

        // Stop if default group is null
        if ($defaultGroupSlug === '') {
            return;
        }

        // Get default group
        /** @var GroupInterface|null */
        $defaultGroup = $this->groupModel->where('slug', $defaultGroupSlug)->first();
        if ($defaultGroup === null) {
            $e = new DefaultGroupException("Default group with slug '{$defaultGroupSlug}' not found.");
            $e->setSlug(strval($defaultGroupSlug));

            throw $e;
        }

        // @phpstan-ignore-next-line False positive. GroupInterface mixin \Illuminate\Database\Eloquent\Model
        $event->user->group()->associate($defaultGroup);
        $event->user->save();
    }
}
