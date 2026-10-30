<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit\Config;

use PHPUnit\Framework\TestCase;

class CacheConfigTest extends TestCase
{
    public function testCacheDriverDefaultsToFile(): void
    {
        $config = $this->loadDefaultConfig('CACHE_DRIVER', null);

        $this->assertSame('file', $config['cache']['driver']);
    }

    public function testCacheDriverCanBeSetThroughEnvironment(): void
    {
        $config = $this->loadDefaultConfig('CACHE_DRIVER', 'memcached');

        $this->assertSame('memcached', $config['cache']['driver']);
    }

    public function testMemcachedHostCanBeSetThroughEnvironment(): void
    {
        $config = $this->loadDefaultConfig('MEMCACHED_HOST', 'memcached');

        $this->assertSame('memcached', $config['cache']['memcached']['host']);
    }

    public function testMemcachedHostDefaultsToLocalhost(): void
    {
        $config = $this->loadDefaultConfig('MEMCACHED_HOST', null);

        $this->assertSame('127.0.0.1', $config['cache']['memcached']['host']);
    }

    /**
     * @return array<string, mixed>
     */
    private function loadDefaultConfig(string $variable, ?string $value): array
    {
        $hadEnv = array_key_exists($variable, $_ENV);
        $previousEnv = $_ENV[$variable] ?? null;
        $hadServer = array_key_exists($variable, $_SERVER);
        $previousServer = $_SERVER[$variable] ?? null;
        $previousProcessEnv = getenv($variable);

        if ($value === null) {
            unset($_ENV[$variable], $_SERVER[$variable]);
            putenv($variable);
        } else {
            $_ENV[$variable] = $value;
            $_SERVER[$variable] = $value;
            putenv($variable . '=' . $value);
        }

        try {
            /** @var array<string, mixed> $config */
            $config = require dirname(__DIR__, 3) . '/config/default.php';

            return $config;
        } finally {
            if ($hadEnv) {
                $_ENV[$variable] = $previousEnv;
            } else {
                unset($_ENV[$variable]);
            }

            if ($hadServer) {
                $_SERVER[$variable] = $previousServer;
            } else {
                unset($_SERVER[$variable]);
            }

            if ($previousProcessEnv === false) {
                putenv($variable);
            } else {
                putenv($variable . '=' . $previousProcessEnv);
            }
        }
    }
}
