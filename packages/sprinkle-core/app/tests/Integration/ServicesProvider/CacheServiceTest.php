<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\ServicesProvider;

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository as Cache;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase as TestCase;

/**
 * Test service implementation works.
 * This test is agnostic of the actual config. It only test all the parts works together in a default env.
 */
class CacheServiceTest extends TestCase
{
    public function testService(): void
    {
        $this->assertSame('testing', $this->getContainer()->get('UF_MODE'));
        $cache = $this->getService(Cache::class);
        $this->assertInstanceOf(ArrayStore::class, $cache->getStore());
    }
}
