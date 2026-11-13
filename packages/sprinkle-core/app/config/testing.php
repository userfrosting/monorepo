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
        'default' => env('DB_TEST_CONNECTION', env('DB_CONNECTION', 'memory')),

        'connections' => [
            'mysql' => [
                'url'         => env('DB_TEST_URL', env('DB_URL')),
                'host'        => env('DB_TEST_HOST', env('DB_HOST', 'localhost')),
                'port'        => env('DB_TEST_PORT', env('DB_PORT', '3306')),
                'database'    => env('DB_TEST_NAME', env('DB_NAME')),
                'username'    => env('DB_TEST_USER', env('DB_USER')),
                'password'    => env('DB_TEST_PASSWORD', env('DB_PASSWORD')),
                'unix_socket' => env('DB_TEST_SOCKET', env('DB_SOCKET', '')),
            ],

            'mariadb' => [
                'url'         => env('DB_TEST_URL', env('DB_URL')),
                'host'        => env('DB_TEST_HOST', env('DB_HOST', 'localhost')),
                'port'        => env('DB_TEST_PORT', env('DB_PORT', '3306')),
                'database'    => env('DB_TEST_NAME', env('DB_NAME')),
                'username'    => env('DB_TEST_USER', env('DB_USER')),
                'password'    => env('DB_TEST_PASSWORD', env('DB_PASSWORD')),
                'unix_socket' => env('DB_TEST_SOCKET', env('DB_SOCKET', '')),
            ],

            'sqlite' => [
                'url'                     => env('DB_TEST_URL', env('DB_URL')),
                'database'                => env('DB_TEST_NAME', env('DB_NAME', 'database.sqlite')),
                'foreign_key_constraints' => env('DB_TEST_FOREIGN_KEYS', env('DB_FOREIGN_KEYS', true)),
            ],

            'pgsql' => [
                'url'            => env('DB_TEST_URL', env('DB_URL')),
                'host'           => env('DB_TEST_HOST', env('DB_HOST', '127.0.0.1')),
                'port'           => env('DB_TEST_PORT', env('DB_PORT', '5432')),
                'database'       => env('DB_TEST_NAME', env('DB_NAME')),
                'username'       => env('DB_TEST_USER', env('DB_USER')),
                'password'       => env('DB_TEST_PASSWORD', env('DB_PASSWORD')),
                'unix_socket'    => env('DB_TEST_SOCKET', env('DB_SOCKET', '')),
            ],
        ],
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
