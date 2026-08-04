<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Database\Seeds;

use UserFrosting\Sprinkle\Account\Database\Models\Group;
use UserFrosting\Sprinkle\Core\Seeder\SeedInterface;

/**
 * Seeder for the default groups.
 */
class DefaultGroups implements SeedInterface
{
    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
        $groups = $this->getGroups();

        foreach ($groups as $group) {
            // Don't save if already exist
            if (Group::where('slug', $group->slug)->first() === null) {
                $group->save();
            }
        }
    }

    /**
     * @return Group[] Groups to seed
     */
    protected function getGroups(): array
    {
        return [
            new Group([
                'slug'        => 'hippo',
                'name'        => 'Hippos',
                'description' => 'Hippos are large, mostly herbivorous mammals native to sub-Saharan Africa, known for their massive size and semi-aquatic lifestyle.',
                'icon'        => 'hippo',
            ]),
            new Group([
                'slug'        => 'dove',
                'name'        => 'Doves',
                'description' => 'Doves are symbols of peace and harmony, often representing hope and new beginnings across various cultures.',
                'icon'        => 'dove',
            ]),
            new Group([
                'slug'        => 'dragon',
                'name'        => 'Dragons',
                'description' => 'Dragons are legendary creatures found in the myths of many cultures, often symbolizing power, wisdom, and strength.',
                'icon'        => 'dragon',
            ]),
        ];
    }
}
