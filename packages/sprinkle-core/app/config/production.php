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
 * Default production config file for UserFrosting.  You may override/extend this in your site's configuration file to customize deploy settings.
 */
return [
    /*
    * ----------------------------------------------------------------------
    * Asset bundler Config
    * ----------------------------------------------------------------------
    * Under production, don't use Vite dev server by default.
    */
    'assets' => [
        'vite' => [
            'dev' => env('VITE_DEV_ENABLED', false),
        ],
    ],

    /*
     * `confirm_sensitive_command` in production mode
     */
    'bakery' => [
        'confirm_sensitive_command' => true,
    ],
    /*
     * Enable Twig & route cache
     */
    'cache' => [
        'twig'  => true,
        'route' => true,
    ],
    /*
     * Turn off debug logs
     */
    'debug' => [
        'twig'      => false,
        'auth'      => false,
        'exception' => false,
    ],
    /*
    * Log error in production
    */
    'logs' => [
        'exception' => true,
    ],
    /*
     * Enable analytics, disable more debugging
     */
    'site' => [
        'analytics' => [
            'google' => [
                'enabled' => true,
            ],
        ],
        'debug' => [
            'info' => false,
        ],
        'uri' => [
            'public' => env('URI_PUBLIC', ''), // This should be set in production !
        ],
    ],
    /*
     * Send errors to log
     */
    'php' => [
        'log_errors'      => true,
    ],
];
