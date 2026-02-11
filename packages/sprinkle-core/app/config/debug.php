<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

/*
 * Debug development config file for UserFrosting. Sets every debug options on to help debug what's going wrong
 */
return [
    'cache' => [
        'twig' => false,
    ],
    'debug' => [
        'deprecation'   => true,
        'queries'       => true,
        'twig'          => true,
        'exception'     => true,
    ],
    'logs' => [
        'exception' => true,
    ],
    'site' => [
        'debug' => [
            'info' => true,
        ],
    ],
];
