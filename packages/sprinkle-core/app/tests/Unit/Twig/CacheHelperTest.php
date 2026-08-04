<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit\Twig;

use Illuminate\Filesystem\Filesystem;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use UserFrosting\Sprinkle\Core\Twig\CacheHelper;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

class CacheHelperTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testClearCache(): void
    {
        /** @var Mockery\MockInterface&ResourceLocatorInterface */
        $locator = Mockery::mock(ResourceLocatorInterface::class)
            ->shouldReceive('findResource')->with('cache://twig', true)->andReturn('/path/to/cache')
            ->getMock();

        /** @var Filesystem */
        $filesystem = Mockery::mock(Filesystem::class)
            ->shouldReceive('exists')->with('/path/to/cache')->andReturn(true)
            ->shouldReceive('deleteDirectory')->with('/path/to/cache', true)->andReturn(true)
            ->getMock();

        $cacheHelper = new CacheHelper($locator, $filesystem);
        $this->assertTrue($cacheHelper->clearCache());
    }

    public function testClearCachePathDoesNotExist(): void
    {
        /** @var Mockery\MockInterface&ResourceLocatorInterface */
        $locator = Mockery::mock(ResourceLocatorInterface::class)
            ->shouldReceive('findResource')->with('cache://twig', true)->andReturn('/path/to/cache')
            ->getMock();

        /** @var Filesystem */
        $filesystem = Mockery::mock(Filesystem::class)
            ->shouldReceive('exists')->with('/path/to/cache')->andReturn(false)
            ->shouldNotReceive('deleteDirectory')
            ->getMock();

        $cacheHelper = new CacheHelper($locator, $filesystem);
        $this->assertTrue($cacheHelper->clearCache());
    }
}
