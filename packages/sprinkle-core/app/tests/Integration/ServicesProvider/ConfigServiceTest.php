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

use UserFrosting\Config\Config;
use UserFrosting\Config\ConfigPathBuilder;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase as TestCase;
use UserFrosting\Support\Repository\Loader\ArrayFileLoader;

/**
 * Test service implementation works.
 * This test is agnostic of the actual config. It only test all the parts works together in a default env.
 */
class ConfigServiceTest extends TestCase
{
    public function testService(): void
    {
        $this->assertIsString($this->getContainer()->get('UF_MODE'));
        $this->assertInstanceOf(Config::class, $this->getService(Config::class)); // @phpstan-ignore-line
        $this->assertInstanceOf(ArrayFileLoader::class, $this->getService(ArrayFileLoader::class)); // @phpstan-ignore-line
        $this->assertInstanceOf(ConfigPathBuilder::class, $this->getService(ConfigPathBuilder::class)); // @phpstan-ignore-line
    }
}
