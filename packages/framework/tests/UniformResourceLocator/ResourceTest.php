<?php

declare(strict_types=1);

/*
 * UserFrosting Framework (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/framework
 * @copyright Copyright (c) 2013-2024 Alexander Weissman, Louis Charette, Jordan Mele
 * @license   https://github.com/userfrosting/framework/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests\UniformResourceLocator;

use PHPUnit\Framework\TestCase;
use UserFrosting\UniformResourceLocator\Normalizer;
use UserFrosting\UniformResourceLocator\Resource;
use UserFrosting\UniformResourceLocator\ResourceLocation;
use UserFrosting\UniformResourceLocator\ResourceStream;
use UserFrosting\UniformResourceLocator\ResourceStreamInterface;

/**
 * Tests for ResourceLocator.
 */
class ResourceTest extends TestCase
{
    protected ResourceStream $stream;
    protected string $streamScheme = 'streamPath';
    protected string $streamPath = 'streamPath/';
    protected bool $streamShared = false;
    protected ResourceLocation $location;
    protected string $locationName = 'locationPath';
    protected string $locationPath = 'locationPath/';

    public function setUp(): void
    {
        parent::setUp();

        $this->stream = new ResourceStream($this->streamScheme, $this->streamPath, $this->streamShared);
        $this->location = new ResourceLocation($this->locationName, $this->locationPath);
    }

    public function testGetStreamAndGetLocation(): void
    {
        $resource = new Resource($this->stream, $this->location, $this->streamPath . 'test.txt', 'basePath/');
        $this->assertEquals($this->stream, $resource->getStream());
        $this->assertEquals($this->location, $resource->getLocation());
    }

    /**
     * @dataProvider resourcesProvider
     *
     * @param bool   $useLocation
     * @param string $path
     * @param string $basePath
     */
    public function testGetBasePath(bool $useLocation, string $path, string $basePath): void
    {
        // Can't be done in resourcesProvider, as `setUp` is called after
        if ($useLocation) {
            $location = $this->location;
            $locationPath = $this->locationPath;
        } else {
            $location = null;
            $locationPath = '';
        }

        $resource = new Resource($this->stream, $location, $locationPath . $this->streamPath . $path, $basePath);

        // getBasePath
        $this->assertSame($path, $resource->getBasePath());

        // Test `getUri` as the too are connected
        $this->assertSame($this->streamScheme . '://' . $path, $resource->getUri());

        // Test `getAbsolutePath` and `__toString`
        $basePath = Normalizer::normalizePath($basePath);
        $this->assertSame($basePath . $locationPath . $this->streamPath . $path, $resource->getAbsolutePath());
        $this->assertSame($resource->getAbsolutePath(), (string) $resource);
    }

    /**
     * Test edge case where the locator base path is the same as the location path,
     * which would result in an empty string when removing the location path from
     * the resource path to get the base path.
     */
    public function testGetBasePathForEdgeCase(): void
    {
        $resource = new Resource($this->stream, $this->location, $this->streamPath . 'test.txt', $this->locationPath);

        // getBasePath
        $this->assertSame('test.txt', $resource->getBasePath());

        // Test `getUri` as the too are connected
        $this->assertSame($this->streamScheme . '://' . 'test.txt', $resource->getUri());

        // Test `getAbsolutePath` and `__toString`
        $basePath = Normalizer::normalizePath($this->locationPath);
        $this->assertSame($basePath . $this->streamPath . 'test.txt', $resource->getAbsolutePath());
        $this->assertSame($resource->getAbsolutePath(), (string) $resource);
    }

    /**
     * Data provider for testGetBasePath.
     *
     * Return a list of basePath values to test. The actual relative path
     * will be constructed by the test according to the stream used, so
     * we'll assume the relative paths are always correct. Also, mix and
     * match three providers: path, basePath, and useLocation (true/false).
     *
     * @return mixed[]
     */
    public static function resourcesProvider(): array
    {
        $paths = [
            'test.txt',                                 // No stream part. Shouldn't happen in real life
            '',                                         // No stream part. Shouldn't happen in real life
            'data/test.txt',                            // No stream part. Shouldn't happen in real life
            'locationPath/streamPath/test.txt',         // streamPath stream inside the locationPath location. Location always comes first
            'streamPath/locationPath/test.txt',         // We remove the streamPath, as it's the base stream, but keep the locationPath, as it's not a location.
            'streamPath/locationPath/streamPath/test.txt', // We remove the streamPath, as it's the base stream, but keep the locationPath, as it's not a location.
            'streamPath/test.txt',                      // `streamPath/` is removed, because it's the stream part
            'streamPath/streamPath/test.txt',           // The first `streamPath/` is removed, because it's the stream part, the other should be kept
            'locationPath/test.txt',                    // No stream part. Shouldn't happen in real life. locationPath should be kept
            'streamPath/',                              // Without extensions
            'streamPath',
            'streamPath/streamPath/',
            'streamPath/streamPath',
        ];

        $basePaths = [
            '',
            '/',
            '\\',
            'C:\\',
            'C:\\BasePath\\',
            'C:\\BasePath',
            'BasePath/',
            '/BasePath/',
            '/BasePath',
        ];

        $data = [];

        foreach ($paths as $path) {
            foreach ($basePaths as $basepath) {
                $data[] = [true, $path, $basepath];
                $data[] = [false, $path, $basepath];
            }
        }

        return $data;
    }

    /**
     * @dataProvider sharedResourceStreamProvider
     *
     * @param string $path
     */
    public function testSharedResourceStream(string $path): void
    {
        $stream = new ResourceStream('cars', $path, true);
        $resource = new Resource($stream, null, $path);

        $this->assertEquals($path, $resource);
        $this->assertEquals($path, $resource->getPath());
        $this->assertNull($resource->getLocation());
        $this->assertEquals('cars://', $resource->getUri());
        $this->assertInstanceOf(ResourceStreamInterface::class, $resource->getStream()); // @phpstan-ignore-line
    }

    /**
     * Data provider for testSharedResourceStream.
     *
     * Test different placement of slashes to make sure getUri and getBasePath
     * returns the correct path
     *
     * @return string[][]
     */
    public static function sharedResourceStreamProvider(): array
    {
        return [
            ['Garage/cars'],
            ['Garage/cars/'],
            ['Garage'],
            ['Garage/'],
            ['/Garage/cars'],
            ['/Garage/cars/'],
            ['/'],
            [''],
        ];
    }

    /**
     * @dataProvider FilesProvider
     *
     * @param string $path
     * @param string $expectedBasename
     * @param string $expectedFilename
     * @param string $expectedExtension
     * @param string $expectedDirname
     */
    public function testFilePropertiesGetters(
        string $path,
        string $expectedBasename,
        string $expectedFilename,
        string $expectedExtension,
        string $expectedDirname
    ): void {
        $resource = new Resource($this->stream, $this->location, $this->locationPath . $this->streamPath . $path);

        // Normalize $expectedDirname
        $expectedFullDirname = Normalizer::normalizePath($this->locationPath . $this->streamPath . $expectedDirname);
        $expectedFullDirname = rtrim($expectedFullDirname, '/\\');
        $expectedDirname = Normalizer::normalizePath($expectedDirname);
        $expectedDirname = rtrim($expectedDirname, '/\\');

        $this->assertSame($expectedBasename, $resource->getBasename());
        $this->assertSame($expectedFilename, $resource->getFilename());
        $this->assertSame($expectedExtension, $resource->getExtension());
        $this->assertSame($expectedFullDirname, $resource->getAbsoluteDirname());
        $this->assertSame($expectedDirname, $resource->getDirname());
    }

    /**
     * Data provider for testFilePropertiesGetters.
     *
     * Return a list of relPath to test. The abs path will be constructed by the
     * test according to the stream used, so we'll assume the abs path are always
     * correct. In any case, relPath == basePath (always).
     *
     * @return string[][]
     */
    public static function FilesProvider(): array
    {
        return [
            // RelPath, basename, filename, extension
            [
                'test.txt',
                'test.txt',
                'test',
                'txt',
                '',
            ],
            [
                '/streamPath/test.txt',
                'test.txt',
                'test',
                'txt',
                'streamPath',
            ],
            [
                'C:\\streamPath\\test.txt',
                'test.txt',
                'test',
                'txt',
                'C:\\streamPath',
            ],
            [
                'streamPath/test.txt',
                'test.txt',
                'test',
                'txt',
                'streamPath',
            ],
            [
                '/test.txt',
                'test.txt',
                'test',
                'txt',
                '/',
            ],
            [
                'lib.inc.php',
                'lib.inc.php',
                'lib.inc',
                'php',
                '',
            ],
        ];
    }
}
