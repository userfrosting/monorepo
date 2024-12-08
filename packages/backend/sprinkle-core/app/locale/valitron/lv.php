<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

return [
    'required'      => 'ir obligāts lauks',
    'equals'        => "jāsakrīt ar '%s'",
    'different'     => "nedrīkst sakrist ar '%s' lauku",
    'accepted'      => 'laukam jābūt apstiprinātam',
    'numeric'       => 'jābūt skaitliskai vērtībai',
    'integer'       => 'jābūt ciparam (0-9)',
    'length'        => 'nedrīkst būt garāks par %d simboliem',
    'min'           => 'jābūt garākam par %s simboliem',
    'max'           => 'jābūt īsākam par %s simboliem',
    'in'            => 'lauks satur nederīgu vērtību',
    'notIn'         => 'lauks satur nederīgu vērtību',
    'ip'            => ' lauks nav derīga IP adrese',
    'email'         => 'lauks nav norādīta derīga epasta adrese',
    'url'           => 'lauks nav tīmekļa saite',
    'urlActive'     => 'saite neatrodas esošajā domēna vārdā',
    'alpha'         => 'lauks var saturēt tikai alfabēta burtus a-z',
    'alphaNum'      => 'lauks var saturēt tikai alfabēta burtus un/vai ciparus 0-9',
    'slug'          => 'lauks var saturēt tikai alfabēta burtus un/vai ciparus 0-9, domuzīmes and zemsvītras',
    'regex'         => 'lauks satur nederīgus simbolus',
    'date'          => 'lauks ir nederīgā datuma formātā',
    'dateFormat'    => "laukam jābūt datuma formātā '%s'",
    'dateBefore'    => "lauka datumam jābūt pirms '%s'",
    'dateAfter'     => "lauka datumam jābūt pēc '%s'",
    'contains'      => 'laukam jāsatur %s',
    'boolean'       => 'laukam jābūt ir/nav vērtībai',
    'lengthBetween' => 'lauka garumam jābūt no %d līdz %d simbolu garam',
    'creditCard'    => 'laukam jābūt derīgam kredītkartes numuram',
];
