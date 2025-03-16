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
 * Core configuration file for UserFrosting.  You must override/extend this in your site's configuration file.
 *
 * Sensitive credentials should be stored in an environment variable or your .env file.
 * Database password: DB_PASSWORD
 * SMTP server password: SMTP_PASSWORD
 */
return [
    /*
    * ----------------------------------------------------------------------
    * Address Book
    * ----------------------------------------------------------------------
    * Admin is the one sending email from the system. You can set the sender
    * email address and name using this config.
    */
    'address_book' => [
        'admin' => [
            'email' => env('MAIL_FROM_ADDRESS', 'noreply@example.com'),
            'name'  => env('MAIL_FROM_NAME', 'Site Administrator'),
        ],
    ],

    /*
    * ----------------------------------------------------------------------
    * Alert Service Config
    * ----------------------------------------------------------------------
    * Alerts can be stored in the session, or cache system. Switch to the
    * cache system if you experience issue with persistent alerts.
    */
    'alert' => [
        'storage'   => 'session',       // Supported storage : `session`, `cache`
        'key'       => 'site.alerts',   // the key to use to store flash messages
    ],

    /*
    * ----------------------------------------------------------------------
    * Asset bundler Config
    * ----------------------------------------------------------------------
    * Frontend assets can be handle either by Vite or Webpack. This section
    * is used to define which bundler is used, and their configuration.
    */
    'assets' => [
        'bundler' => env('ASSETS_BUNDLER'), // Either 'vite' or 'webpack'
        'vite'    => [
            'manifest' => 'assets://.vite/manifest.json',
            'dev'      => env('VITE_DEV_ENABLED', true),
            'base'     => 'assets/',
            'server'   => 'http://[::1]:3000/',
        ],
        // Defines path to Webpack Encore `entrypoints.json` and `manifest.json` files.
        'webpack' => [
            'entrypoints' => 'assets://entrypoints.json',
            'manifest'    => 'assets://manifest.json',
        ],
    ],

    /*
    * ----------------------------------------------------------------------
    * Bakery Config
    * ----------------------------------------------------------------------
    * `confirm_sensitive_command` set to true will ask for confirmation before
    * running some sensitive bakery commands, such as database altering
    * migrations.
    */
    'bakery' => [
        'confirm_sensitive_command' => false,
    ],

    /*
    * ----------------------------------------------------------------------
    * Cache Service Config
    * ----------------------------------------------------------------------
    * Redis & Memcached driver configuration
    * See Laravel for more info : https://laravel.com/docs/10.x/cache
    *
    * Edit prefix to something unique when multiple instance of memcached /
    * redis are used on the same server.
    */
    'cache' => [
        'driver'     => 'file', // Supported drivers : `file`, `memcached`, `redis`, `array`
        'prefix'     => 'userfrosting',
        'memcached'  => [
            'host'   => '127.0.0.1',
            'port'   => 11211,
            'weight' => 100,
        ],
        'redis' => [
            'host'     => '127.0.0.1',
            'password' => null,
            'port'     => 6379,
            'database' => 0,
        ],
        // Cache twig file to disk?
        'twig' => false,
        // Cache routes? And filename for route cache.
        'routerFile' => 'routes.cache',
        'route'      => false,
    ],

    /*
    * ----------------------------------------------------------------------
    * CSRF middleware settings
    * ----------------------------------------------------------------------
    * See https://github.com/slimphp/Slim-Csrf
    * Note : CSRF Middleware should only be disabled for dev or debug purposes.
    */
    'csrf' => [
        'enabled'          => env('CSRF_ENABLED', true),
        'name'             => 'csrf',
        'storage_limit'    => 200,
        'strength'         => 16,
        'persistent_token' => true,
        'blacklist'        => [
            // A list of url paths to ignore CSRF checks on
            // URL paths will be matched against each regular expression in this list.
            // Each regular expression should map to an array of methods.
            // Regular expressions will be delimited with ~ in preg_match, so if you
            // have routes with ~ in them, you must escape this character in your regex.
            // Also, remember to use ^ when you only want to match the beginning of a URL path!
        ],
    ],

    /*
    * ----------------------------------------------------------------------
    * Database Config
    * ----------------------------------------------------------------------
    * Settings for the default database connections. Actual config values
    * should be store in environment variables.
    */
    'db' => [
        'default'    => env('DB_CONNECTION', 'mysql'),

        'connections' => [
            'mysql' => [
                'driver'      => 'mysql',
                'url'         => env('DB_URL'),
                'host'        => env('DB_HOST', 'localhost'),
                'port'        => env('DB_PORT', '3306'),
                'database'    => env('DB_NAME'),
                'username'    => env('DB_USER'),
                'password'    => env('DB_PASSWORD'),
                'unix_socket' => env('DB_SOCKET', ''),
                'charset'     => 'utf8',
                'collation'   => 'utf8_unicode_ci',
                'prefix'      => '',
            ],

            'sqlite' => [
                'driver'                  => 'sqlite',
                'url'                     => env('DB_URL'),
                'database'                => env('DB_NAME', 'database.sqlite'),
                'prefix'                  => '',
                'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
            ],

            'memory' => [
                'driver'   => 'sqlite',
                'database' => ':memory:',
            ],

            'pgsql' => [
                'driver'         => 'pgsql',
                'url'            => env('DB_URL'),
                'host'           => env('DB_HOST', '127.0.0.1'),
                'port'           => env('DB_PORT', '5432'),
                'database'       => env('DB_NAME'),
                'username'       => env('DB_USER'),
                'password'       => env('DB_PASSWORD'),
                'unix_socket'    => env('DB_SOCKET', ''),
                'charset'        => 'utf8',
                'prefix'         => '',
                'prefix_indexes' => true,
                'schema'         => 'public',
                'sslmode'        => 'prefer',
            ],

            'sqlsrv' => [
                'driver'         => 'sqlsrv',
                'url'            => env('DATABASE_URL'),
                'host'           => env('DB_HOST', 'localhost'),
                'port'           => env('DB_PORT', '1433'),
                'database'       => env('DB_NAME', 'forge'),
                'username'       => env('DB_USER', 'forge'),
                'password'       => env('DB_PASSWORD', ''),
                'charset'        => 'utf8',
                'prefix'         => '',
                'prefix_indexes' => true,
            ],
        ],
    ],

    /**
     * ----------------------------------------------------------------------
     * Debug Configuration
     * ----------------------------------------------------------------------
     * Turn any of those on to help debug your app.
     */
    'debug' => [
        'deprecation'   => true,
        'queries'       => false,
        'twig'          => false,
        'exception'     => true,
    ],

    /**
     * ----------------------------------------------------------------------
     * Error Configuration
     * ----------------------------------------------------------------------
     * Configuration for built in Twig error page returned by the PrettyPageRenderer.
     *
     * @see \UserFrosting\Sprinkle\Core\Error\Renderer\PrettyPageRenderer
     */
    'error' => [
        'pages' => [
            'status' => 'pages/error/%d.html.twig', // Use %d as placeholder for the status code (eg. 404, 400, 500, etc.)
            'error'  => 'pages/error/error.html.twig', // Fallback page, if the status one isn't available
        ],
    ],

    /*
    * ----------------------------------------------------------------------
    *  Filesystem Configuration
    * ----------------------------------------------------------------------
    * You may configure as many filesystem "disks" as you wish, and you
    * may even configure multiple disks of the same driver. You may also
    * select the default filesystem disk that should be used by UserFrosting.
    *
    * Supported Drivers for disk: "local", "ftp", "sftp", "s3", "rackspace"
    */
    'filesystems' => [
        'default' => env('FILESYSTEM_DRIVER', 'local'),
        'cloud'   => env('FILESYSTEM_CLOUD', 's3'),

        'disks' => [
            /*
             * Default storage disk. Default path is `app/storage/`. All
             * files are accessible through the FilesystemManager, but not
             * publicly accessible through an URL. Can still be downloaded
             * using the `download` method in a custom controller
             */
            'local' => [
                'driver' => 'local',
                'root'   => 'storage://',
            ],
            /*
            * Public files are directly accessible through the webserver for
            * better performances, but at the expanse of all files being public.
            * Direct access from http://{url}/files/, physically located in `/public/files`
            * Great storage disk for assets (images, avatar, etc).
            */
            'public' => [
                'driver' => 'local',
                'root'   => 'public://files',
                'url'    => 'files/',
            ],
            /*
             * Amazon S3 Bucket Config. Config should go in .env file. For help, see :
             * https://aws.amazon.com/en/blogs/security/wheres-my-secret-access-key/
             *
             * As of version 4.3, https://github.com/thephpleague/flysystem-aws-s3-v3
             * is required inside a custom Sprinkle to use this filesystem.
             *
             * Include thephpleague/flysystem-aws-s3-v3 in a custom Sprinkle to use.
             */
            's3' => [
                'driver' => 's3',
                'key'    => env('AWS_ACCESS_KEY_ID', ''),
                'secret' => env('AWS_SECRET_ACCESS_KEY', ''),
                'region' => env('AWS_DEFAULT_REGION', ''), // See : http://docs.aws.amazon.com/general/latest/gr/rande.html
                'bucket' => env('AWS_BUCKET', ''),
                'url'    => env('AWS_URL', ''),
            ],
            /*
             * Rackspace Config. Config should go in .env file. see :
             * https://laravel.com/docs/10.x/filesystem#configuration
             *
             * As of version 4.3, https://github.com/thephpleague/flysystem-rackspace
             * is required inside a custom Sprinkle to use this filesystem.
             *
             * Include thephpleague/flysystem-rackspace in a custom Sprinkle to use.
             */
            'rackspace' => [
                'driver'    => 'rackspace',
                'username'  => env('RACKSPACE_USERNAME', ''),
                'key'       => env('RACKSPACE_KEY', ''),
                'container' => env('RACKSPACE_CONTAINER', ''),
                'endpoint'  => env('RACKSPACE_ENDPOINT', ''),
                'region'    => env('RACKSPACE_REGION', ''),
                'url_type'  => env('RACKSPACE_URL_TYPE', ''),
            ],
        ],
    ],

    /*
    * ----------------------------------------------------------------------
    * Logs Config
    * ----------------------------------------------------------------------
    */
    'logs' => [
        'exception' => false, // Send exceptions details to the logs
        'path'      => 'logs://userfrosting.log',
    ],

    /*
    * ----------------------------------------------------------------------
    * Mail Service Config
    * ----------------------------------------------------------------------
    * See https://learn.userfrosting.com/mail/the-mailer-service
    */
    'mail' => [
        'mailer'          => env('MAIL_MAILER', 'smtp'), // Set to one of 'smtp', 'mail', 'qmail', 'sendmail'
        'host'            => env('SMTP_HOST'),
        'port'            => env('SMTP_PORT', 587),
        'auth'            => env('SMTP_AUTH', true),
        'secure'          => env('SMTP_SECURE', 'tls'), // Enable TLS encryption. Set to `tls`, `ssl` or `false` (to disabled)
        'username'        => env('SMTP_USER'),
        'password'        => env('SMTP_PASSWORD'),
        'smtp_debug'      => 4,
        'message_options' => [
            'CharSet'   => 'UTF-8',
            'isHtml'    => true,
            'Timeout'   => 15,
        ],
    ],

    /*
    * ----------------------------------------------------------------------
    * Session Config
    * ----------------------------------------------------------------------
    * Custom PHP Sessions Handler config. Sessions can be store in file or
    * database. Array handler can be used for testing
    */
    'session' => [
        'handler'       => 'file', // Supported Handler : `file`, `database` or `array`
        // Config values for when using db-based sessions
        'database'      => [
            'table' => 'sessions',
        ],
        'name'          => 'uf4',
        'minutes'       => 120,
        'cache_limiter' => false,
        // Decouples the session keys used to store certain session info
        'keys' => [
            'csrf'    => 'site.csrf', // the key (prefix) used to store an ArrayObject of CSRF tokens.
        ],
    ],

    /*
    * ----------------------------------------------------------------------
    * Site Settings
    * ----------------------------------------------------------------------
    * "Site" settings are automatically passed to Twig and exposed via an API 
    * for access by the frontend.
    */
    'site' => [
        // Google Analytics Settings
        'analytics' => [
            'google' => [
                'code'    => '',
                'enabled' => false,
            ],
        ],
        'author'    => 'Author', // Site author
        'debug'     => [
            'ajax' => false,
            'info' => true,
        ],
        'locales' => [
            // Should be ordered according to https://en.wikipedia.org/wiki/List_of_languages_by_total_number_of_speakers,
            // with the exception of English, which as the default language comes first.
            'available' => [
                'en_US' => true,
                'fr_FR' => true,
            ],
            // The default locale to use for non-registered users.
            // Browser requested languages might overwrite this value.
            'default' => 'en_US',
        ],
        'title' => 'UserFrosting', // Site display name
        // Global ufTable settings
        'uf_table' => [
            'use_loading_transition' => true,
        ],
        // URLs
        'uri' => [
            'author'    => 'https://www.userfrosting.com',
            'publisher' => '',
            'public'    => null,
        ],
    ],

    /*
    * ----------------------------------------------------------------------
    * PHP global settings
    * ----------------------------------------------------------------------
    */
    'php' => [
        'timezone'              => 'America/New_York',
        'error_reporting'       => E_ALL, // Development - report all errors and suggestions
        'log_errors'            => false,
        'display_errors_native' => false, // Let PHP itself render errors natively.  Useful if a fatal error is raised in our custom shutdown handler.
    ],

    /**
     * ----------------------------------------------------------------------
     * Writable Stream Config
     * ----------------------------------------------------------------------
     * Resource stream to check for write permission. True means it should be
     * writeable, false means it should not be writable.
     */
    'writable' => [
        'logs://'     => true,
        'cache://'    => true,
        'sessions://' => true,
    ],
];
