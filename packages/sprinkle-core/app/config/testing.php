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
 * Default testing config file for UserFrosting.  You may override/extend this in your site's configuration file to customize deploy settings.
 */
return [
    /*
     * Don't use persistent caching in tests
     */
    'cache' => [
        'driver' => 'array',
    ],

    /*
     * Disable CSRF protection in tests
     */
    'csrf' => [
        'enabled' => false,
        'secret'  => 'test-csrf-secret',
    ],

    /*
     * Define in memory db for testing
     */
    'db' => [
        'default' => env('DB_TEST_CONNECTION', 'memory'),
    ],

    /*
     * Don't log deprecations in tests
     */
    'debug' => [
        'deprecation' => false,
        'queries'     => false,
        'exception'   => true,
    ],

    /*
    * Don't log exceptions in tests
    */
    'logs' => [
        'exception' => false,
    ],

    /*
     * Disable native sessions in tests
     */
    'session' => [
        'handler' => env('TEST_SESSION_HANDLER', 'array'),
    ],

    /*
     * Don't force writable directories in tests.
     */
    'writable' => [
        'logs://'     => null,
        'cache://'    => null,
        'sessions://' => null,
    ],
];
