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
use UserFrosting\UniformResourceLocator\ResourceInterface;
use UserFrosting\UniformResourceLocator\ResourceLocation;
use UserFrosting\UniformResourceLocator\ResourceLocator;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;
use UserFrosting\UniformResourceLocator\ResourceStream;
use UserFrosting\UniformResourceLocator\ResourceStreamInterface;

/**
 * Tests for ResourceLocator.
 */
class BuildingLocatorTest extends TestCase
{
    /** @var string */
    protected $basePath = __DIR__ . '/Building/';

    /** @var ResourceLocatorInterface */
    protected static $locator;

    /**
     * Setup shared locator for resources tests.
     *
     * This will setup the following streams:
     *      cars://     -> A Shared stream, loading from Building/Garage/cars, not subject to locations
     *      files://    -> Returning all files from `Building/Floors/{floorX}/files` as well as `Building/upload/data/files/`
     *      conf://     -> Returning all files from `Building/Floors/{floorX}/config` only
     *
     * Locations are : Floor1, Floor2 & Floor3
     * This means Floor 3 as top priority, and will be searched first
     *
     * Test file structure :
     *  Floor1  ->  files/test/blah.json
     *          ->  files/test.json
     *  Floor2  ->  config/test.json
     *          ->  files/data/foo.json
     *          ->  files/foo.json
     *          ->  files/test.json
     *  Floor3  ->  cars/cars.json
     *          ->  files/test.json
     *  Garage  ->  cars/cars.json
     *          ->  files/blah.json
     *  upload  ->  data/files/foo.json
     *
     * So, files found for each stream should be, when looking only at the top most :
     *      cars://
     *          - Garage/cars/cars.json
     *      files://
     *          - Floors/Floor3/files/test.json
     *          - Floors/Floor2/files/foo.json
     *          - upload/data/files/foo.json (as data/foo.json using prefix)
     *          - Floors/Floor/files/test/blah.json
     *      conf://
     *          - Floors/Floor2/config/test.json
     *
     * The following files are purely as placeholder, and should never be found :
     *  - Floors/Floor3/cars/cars.json : Should never be returned when listing cars, because the floors are not part of the cars:// search path
     *  - Floors/Floor2/test.json : Overwritten by Floor3 version
     *  - Floors/Floor1/test.json : Overwritten by Floor3 version
     *  - Garage/files/blah.json : Should never be found, because the Garage is not part of the file:// search path
     */
    public function setUp(): void
    {
        parent::setUp();

        self::$locator = new ResourceLocator($this->basePath);

        // Register the floors.
        // Note the missing `/` at the end for Floor 3. This shouldn't make any difference.
        // At the beginning, it means the locator use an absolute path, bypassing Locator base path for that locator
        // Floor2 simulate an absolute path for that location. Note it won't make any sense (and fail) if both
        // the location and the stream uses absolute paths
        $floor1 = new ResourceLocation('Floor1', 'Floors/Floor/');
        $floor2 = new ResourceLocation('Floor2', $this->getBasePath() . 'Floors/Floor2/');
        $floor3 = new ResourceLocation('Floor3', 'Floors/Floor3');
        self::$locator->addLocation($floor1)
                      ->addLocation($floor2)
                      ->addLocation($floor3);

        // Register the streams
        self::$locator->addStream(new ResourceStream('files'))                                               // Search path -> Building/Floors/{floorX}/file (normal stream)
                      ->addStream(new ResourceStream('conf', 'config'))                                      // Search path -> Building/Floors/{floorX}/config (stream where scheme != path)
                      ->addStream(new ResourceStream('cars', 'Garage/cars/', true))                          // Search path -> Building/Garage/cars (Stream shared, no prefix)
                      ->addStream(new ResourceStream('absCars', $this->getBasePath() . 'Garage/cars/', true)); // Search path -> Building/Garage/cars (Stream shared, no prefix, using absolute path)
    }

    public function testGetResourceThrowExceptionIfSchemeNotExist(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        self::$locator->getResource('foo://');
    }

    /**
     * @dataProvider sharedResourceProvider
     *
     * @param string      $scheme
     * @param string      $file
     * @param string|null $location
     * @param string[]    $expectedPaths
     */
    public function testGetResourceForSharedStream(string $scheme, string $file, ?string $location, array $expectedPaths): void
    {
        $locator = self::$locator;
        $uri = $scheme . '://' . $file;

        $resource = $locator->getResource($uri);
        $this->assertInstanceOf(ResourceInterface::class, $resource);
        $this->assertEquals($this->getBasePath() . $expectedPaths[0], $resource);
        $this->assertEquals($this->getBasePath() . $expectedPaths[0], $locator($uri));
        $this->assertSame($expectedPaths[0], $resource->getPath());
        $this->assertNull($resource->getLocation());
        $this->assertSame($uri, $resource->getUri());
        $this->assertInstanceOf(ResourceStreamInterface::class, $resource->getStream()); // @phpstan-ignore-line
    }

    /**
     * @depends testGetResourceForSharedStream
     */
    public function testGetResourceForSharedStreamReturnFalseIfNoResourceFalse(): void
    {
        $locator = self::$locator;

        $this->assertNull($locator->getResource('cars://idontExist.txt'));
        $this->assertNull($locator('cars://idontExist.txt'));
    }

    /**
     * @dataProvider sharedResourceProvider
     * @depends testGetResourceForSharedStream
     *
     * @param string      $scheme
     * @param string      $file
     * @param string|null $location
     * @param string[]    $expectedPaths
     */
    public function testGetResourcesForSharedStream(string $scheme, string $file, ?string $location, array $expectedPaths): void
    {
        $locator = self::$locator;
        $uri = $scheme . '://' . $file;

        $resources = $locator->getResources($uri);
        $this->assertCount(count($expectedPaths), $resources);
        $this->assertContainsOnlyInstancesOf(ResourceInterface::class, $resources);
        $this->assertEquals($this->getBasePath() . $expectedPaths[0], $resources[0]);
        $this->assertSame($uri, $resources[0]->getUri());
    }

    /**
     * @depends testGetResourcesForSharedStream
     */
    public function testGetResourcesForSharedStreamReturnFalseIfNoResourceFalse(): void
    {
        $locator = self::$locator;

        $resources = $locator->getResources('cars://idontExist.txt');
        $this->assertCount(0, $resources);
    }

    /**
     * @dataProvider sharedResourceProvider
     * @depends testGetResourceForSharedStream
     * @depends testGetResourcesForSharedStream
     *
     * @param string      $scheme
     * @param string      $file
     * @param string|null $location
     * @param string[]    $expectedPaths
     */
    public function testFindResourceForSharedStream(string $scheme, string $file, ?string $location, array $expectedPaths): void
    {
        $locator = self::$locator;
        $uri = $scheme . '://' . $file;

        // Same tests, for `__invoke`, findResource` & `findResources`
        $this->assertSame($this->getBasePath() . $expectedPaths[0], $locator($uri));
        $this->assertSame($this->getBasePath() . $expectedPaths[0], $locator->findResource($uri)); // @phpstan-ignore-line
        $this->assertSame([$this->getBasePath() . $expectedPaths[0]], $locator->findResources($uri)); // @phpstan-ignore-line

        // Expect same result with relative paths
        $this->assertSame($expectedPaths[0], $locator->findResource($uri, false)); // @phpstan-ignore-line
        $this->assertSame($expectedPaths, $locator->findResources($uri, false)); // @phpstan-ignore-line
    }

    public function testFindResourceForSharedStreamReturnFalseIfNoResourceFalse(): void
    {
        $locator = self::$locator;
        $uri = 'cars://idontExist.txt';

        // Same tests, for `__invoke`, `findResource` & `findResources`
        $this->assertNull($locator($uri));
        $this->assertNull($locator->findResource($uri)); // @phpstan-ignore-line
        $this->assertSame([], $locator->findResources($uri)); // @phpstan-ignore-line

        // Expect same result with relative paths
        $this->assertNull($locator->findResource($uri, false)); // @phpstan-ignore-line
        $this->assertSame([], $locator->findResources($uri, false)); // @phpstan-ignore-line
    }

    /**
     * @dataProvider resourceProvider
     *
     * @param string      $scheme
     * @param string      $file
     * @param string      $filename
     * @param string      $basename
     * @param string      $extension
     * @param string      $dirname
     * @param string      $relativeDirname
     * @param string|null $location
     * @param string[]    $expectedPaths
     * @param string[]    $expectedAllPaths
     * @param bool        $isDir
     */
    public function testGetResource(
        string $scheme,
        string $file,
        string $filename,
        string $basename,
        string $extension,
        string $dirname,
        string $relativeDirname,
        ?string $location,
        array $expectedPaths,
        array $expectedAllPaths,
        bool $isDir
    ): void {
        $locator = self::$locator;
        $uri = $scheme . '://' . $file;

        $resource = $locator->getResource($uri);
        $this->assertInstanceOf(ResourceInterface::class, $resource);
        $this->assertEquals($this->getBasePath() . $expectedPaths[0], $resource);
        $this->assertEquals($this->getBasePath() . $expectedPaths[0], $locator($uri));
        $this->assertSame($expectedPaths[0], $resource->getPath());
        $this->assertInstanceOf(ResourceStreamInterface::class, $resource->getStream()); // @phpstan-ignore-line

        // Test file attributes
        $this->assertSame($uri, $resource->getUri());
        $this->assertSame($isDir, $resource->isDir());
        $this->assertSame($scheme . '://' . $dirname, $resource->getDirUri());
        $this->assertSame($filename, $resource->getFilename());
        $this->assertSame($basename, $resource->getBasename());
        $this->assertSame($extension, $resource->getExtension());
        $this->assertSame($this->getBasePath() . $relativeDirname, $resource->getAbsoluteDirname());
        $this->assertSame($relativeDirname, $resource->getRelativeDirname());
        $this->assertSame($dirname, $resource->getDirname());

        if (is_null($location)) {
            $this->assertNull($resource->getLocation());
        } else {
            $this->assertSame($location, $resource->getLocation()?->getName());
        }
    }

    /**
     * @dataProvider resourceProvider
     * @depends testGetResource
     *
     * @param string      $scheme
     * @param string      $file
     * @param string      $filename
     * @param string      $basename
     * @param string      $extension
     * @param string      $dirname
     * @param string      $relativeDirname
     * @param string|null $location
     * @param string[]    $expectedPaths
     * @param string[]    $expectedAllPaths
     * @param bool        $isDir
     */
    public function testGetResources(
        string $scheme,
        string $file,
        string $filename,
        string $basename,
        string $extension,
        string $dirname,
        string $relativeDirname,
        ?string $location,
        array $expectedPaths,
        array $expectedAllPaths,
        bool $isDir
    ): void {
        $locator = self::$locator;
        $uri = $scheme . '://' . $file;

        $resources = $locator->getResources($uri);
        $this->assertCount(count($expectedPaths), $resources);
        $this->assertEquals($this->relativeToAbsolutePaths($expectedPaths), $resources);
        $this->assertContainsOnlyInstancesOf(ResourceInterface::class, $resources);
        $this->assertEquals($this->getBasePath() . $expectedPaths[0], $resources[0]);
        $this->assertSame($uri, $resources[0]->getUri());
    }

    /**
     * Test when a location is outside the scope of the main locator path.
     * Could cause issue with absolute path parsing.
     *
     * @depends testGetResources
     */
    public function testGetResourcesWithLocationOutsideMain(): void
    {
        $expectedPaths = [
            Normalizer::normalizePath(__DIR__) . 'Poolhouse/files/test.json',
            $this->getBasePath() . 'Floors/Floor3/files/test.json',
            $this->getBasePath() . 'Floors/Floor2/files/test.json',
            $this->getBasePath() . 'Floors/Floor/files/test.json',
        ];

        $locator = self::$locator;

        // Add a new location
        $locator->addLocation(new ResourceLocation('Poolhouse', __DIR__ . '/Poolhouse/'));

        // Assertions
        $resources = $locator->getResources('files://test.json');
        $this->assertCount(count($expectedPaths), $resources);
        $this->assertSame($expectedPaths, array_map('strval', $resources));
    }

    /**
     * @dataProvider resourceProvider
     * @depends testGetResource
     * @depends testGetResources
     *
     * @param string      $scheme
     * @param string      $file
     * @param string      $filename
     * @param string      $basename
     * @param string      $extension
     * @param string      $dirname
     * @param string      $relativeDirname
     * @param string|null $location
     * @param string[]    $expectedPaths
     * @param string[]    $expectedAllPaths
     * @param bool        $isDir
     */
    public function testFindResource(
        string $scheme,
        string $file,
        string $filename,
        string $basename,
        string $extension,
        string $dirname,
        string $relativeDirname,
        ?string $location,
        array $expectedPaths,
        array $expectedAllPaths,
        bool $isDir
    ): void {
        $locator = self::$locator;
        $uri = $scheme . '://' . $file;

        // Same tests, for `__invoke`, findResource` & `findResources`
        $this->assertSame($this->getBasePath() . $expectedPaths[0], $locator($uri));
        $this->assertSame($this->getBasePath() . $expectedPaths[0], $locator->findResource($uri)); // @phpstan-ignore-line
        $this->assertSame($this->relativeToAbsolutePaths($expectedPaths), $locator->findResources($uri)); // @phpstan-ignore-line

        // Expect same result with relative paths
        $this->assertSame($expectedPaths[0], $locator->findResource($uri, false)); // @phpstan-ignore-line
        $this->assertSame($expectedPaths, $locator->findResources($uri, false)); // @phpstan-ignore-line
    }

    public function testListResourcesForSharedStream(): void
    {
        $list = self::$locator->listResources('cars://');
        $this->assertCount(1, $list);
        $this->assertSame([
            $this->getBasePath() . 'Garage/cars/cars.json',
        ], array_map('strval', $list));
    }

    /**
     * @depends testListResourcesForSharedStream
     */
    public function testListResourcesForSharedStreamWithAllArgument(): void
    {
        $list = self::$locator->listResources('cars://', true);
        $this->assertCount(1, $list);
        $this->assertSame([
            $this->getBasePath() . 'Garage/cars/cars.json',
        ], array_map('strval', $list));
    }

    /**
     * In this test, `Floors/Floor2/files/data/foo.json` is not returned,
     * because we don't list recursively.
     */
    public function testListResourcesForFiles(): void
    {
        $expected = [
            $this->getBasePath() . 'Floors/Floor/files/test/blah.json',
            $this->getBasePath() . 'Floors/Floor2/files/data/foo.json',
            $this->getBasePath() . 'Floors/Floor2/files/foo.json',
            $this->getBasePath() . 'Floors/Floor3/files/test.json',
        ];

        $list = self::$locator->listResources('files://');
        $this->assertContainsOnlyInstancesOf(ResourceInterface::class, $list);
        $this->assertCount(4, $list);
        $this->assertSame($expected, array_map('strval', $list));
        $this->assertEquals($expected, $list); // Assert cast to string
    }

    /**
     * List all resources under listResources.
     *
     * @depends testListResourcesForFiles
     */
    public function testListResourcesForFilesWithAllArgument(): void
    {
        $list = self::$locator->listResources('files://', true);
        $this->assertCount(6, $list);
        $this->assertSame([
            $this->getBasePath() . 'Floors/Floor/files/test.json',
            $this->getBasePath() . 'Floors/Floor/files/test/blah.json',
            $this->getBasePath() . 'Floors/Floor2/files/data/foo.json',
            $this->getBasePath() . 'Floors/Floor2/files/foo.json',
            $this->getBasePath() . 'Floors/Floor2/files/test.json',
            $this->getBasePath() . 'Floors/Floor3/files/test.json',
        ], array_map('strval', $list));
    }

    /**
     * List all resources under listResources and apply global sorting.
     *
     * @depends testListResourcesForFilesWithAllArgument
     */
    public function testListResourcesForFilesWithAllArgumentAndSort(): void
    {
        $list = self::$locator->listResources('files://', true, false);
        $this->assertCount(6, $list);
        $this->assertSame([
            $this->getBasePath() . 'Floors/Floor3/files/test.json',
            $this->getBasePath() . 'Floors/Floor2/files/data/foo.json',
            $this->getBasePath() . 'Floors/Floor2/files/foo.json',
            $this->getBasePath() . 'Floors/Floor2/files/test.json',
            $this->getBasePath() . 'Floors/Floor/files/test.json',
            $this->getBasePath() . 'Floors/Floor/files/test/blah.json',
        ], array_map('strval', $list));
    }

    public function testFindCachedReturnFalseOnBadUriPart(): void
    {
        $locator = new ResourceLocator();
        $resource = $locator->getResource('path/to/../../../file.txt');
        $this->assertNull($resource);
    }

    public function testFindCachedReturnFalseOnBadUriPartWithArray(): void
    {
        $locator = new ResourceLocator();
        $resources = $locator->getResources('path/to/../../../file.txt');
        $this->assertSame([], $resources);
    }

    /**
     * Test streamWrapper setup making sure a file that don't exist doesn't
     * return false positive.
     */
    public function testStreamWrapper(): void
    {
        $filename = 'test://cars.json';
        $this->assertFalse(@file_exists($filename));

        // Register
        $stream = new ResourceStream('test', 'Garage/cars/', true);
        self::$locator->addStream($stream);
        $this->assertTrue(file_exists($filename));

        // Unregister
        self::$locator->removeStream('test');
        // @phpstan-ignore-next-line - Result of file_exists() will be different than previous lines, as it's modified by `removeStream`
        $this->assertFalse(@file_exists($filename));
    }

    /**
     * Test streamWrapper setup by reading a file using it's uri.
     *
     * @depends testStreamWrapper
     */
    public function testStreamWrapperReadFile(): void
    {
        $filename = 'cars://cars.json';

        $this->assertTrue(file_exists($filename));

        $handle = fopen($filename, 'r');
        $this->assertNotFalse($handle);

        $filesize = filesize($filename);
        $this->assertNotFalse($filesize);

        $contents = fread($handle, $filesize); // @phpstan-ignore-line

        $this->assertNotEquals('', $contents);
        $this->assertSame('Tesla', json_decode($contents, true)['cars'][1]['make']); // @phpstan-ignore-line

        fclose($handle);
    }

    /**
     * Test streamWrapper setup making sure a file that don't exist doesn't
     * return false positive.
     */
    public function testStreamWrapperNotExist(): void
    {
        $this->assertFalse(file_exists('cars://idontExist.txt'));
    }

    /**
     * DataProvider for testFind
     * Return all files available from our test case.
     *
     * @return mixed[]
     */
    public static function resourceProvider(): array
    {
        return [
            /**
             * Return structure:
             *  - $scheme
             *  - $file
             *  - $filename
             *  - $basename
             *  - $extension
             *  - $dirname
             *  - $relativeDirname
             *  - $location
             *  - $expectedPaths
             *  - $expectedAllPaths
             *  - $isDir
             */

            // #0
            [
                'files',     // scheme
                'test.json', // file
                'test',      // filename
                'test.json', // basename
                'json',      // extension
                '',          // dirname
                'Floors/Floor3/files', // relative dirname
                'Floor3',    // location
                [
                    'Floors/Floor3/files/test.json',
                    'Floors/Floor2/files/test.json',
                    'Floors/Floor/files/test.json',
                ],
                [
                    'Floors/Floor3/files/test.json',
                    'Floors/Floor2/files/test.json',
                    'Floors/Floor/files/test.json',
                ],
                false, // isDir
            ],

            // #1
            [
                'files',     // scheme
                'foo.json',  // file
                'foo',       // filename
                'foo.json',  // basename
                'json',      // extension
                '',          // dirname
                'Floors/Floor2/files', // relative dirname
                'Floor2',    // location
                [
                    'Floors/Floor2/files/foo.json',
                ],
                [
                    'Floors/Floor3/files/foo.json',
                    'Floors/Floor2/files/foo.json',
                    'Floors/Floor/files/foo.json',
                ],
                false, // isDir
            ],

            // #2
            [
                'files',         // scheme
                'test/blah.json', // file
                'blah',          // filename
                'blah.json',     // basename
                'json',          // extension
                'test',          // dirname
                'Floors/Floor/files/test', // relative dirname
                'Floor1',        // location
                [
                    'Floors/Floor/files/test/blah.json',
                ],
                [
                    'Floors/Floor3/files/test/blah.json',
                    'Floors/Floor2/files/test/blah.json',
                    'Floors/Floor/files/test/blah.json',
                ],
                false, // isDir
            ],

            // #3
            // N.B.: upload/data/files is not returned here as the `data` prefix is not used
            //       Plus, the relative dirname will still be it's parent.
            [
                'files',   // scheme
                '',        // file
                '',        // filename
                '',        // basename
                '',        // extension
                '',        // dirname
                'Floors/Floor3', // relative dirname
                'Floor3',  // location
                [
                    'Floors/Floor3/files',
                    'Floors/Floor2/files',
                    'Floors/Floor/files',
                ],
                [
                    'Floors/Floor3/files',
                    'Floors/Floor2/files',
                    'Floors/Floor/files',
                ],
                true, // isDir
            ],

            // #4
            [
                'conf',      // scheme
                'test.json', // file
                'test',      // filename
                'test.json', // basename
                'json',      // extension
                '',          // dirname
                'Floors/Floor2/config', // relative dirname
                'Floor2',    // location
                [
                    'Floors/Floor2/config/test.json',
                ],
                [
                    'Floors/Floor3/config/test.json',
                    'Floors/Floor2/config/test.json',
                    'Floors/Floor/config/test.json',
                ],
                false, // isDir
            ],

            // #5 - Dot file + subdirectory
            [
                'files',                          // scheme
                'data/bar/.test',                 // file
                '',                               // filename
                '.test',                          // basename
                'test',                           // extension
                'data/bar',                       // dirname
                'Floors/Floor2/files/data/bar',   // relative dirname
                'Floor2',                         // location
                [
                    'Floors/Floor2/files/data/bar/.test',
                ],
                [
                    'Floors/Floor2/files/data/bar/.test',
                ],
                false, // isDir
            ],
        ];
    }

    /**
     * Data provider for shared stream tests.
     *
     * @return mixed[]
     */
    public static function sharedResourceProvider(): array
    {
        return [
            //[$scheme, $file, $location, $expectedPaths, $expectedAllPaths],
            // #0
            ['cars', 'cars.json', null, [
                'Garage/cars/cars.json',
            ], [
                'Garage/cars/cars.json',
            ]],

            // #1
            ['cars', '', null, [
                'Garage/cars',
            ], [
                'Garage/cars',
            ]],

            // #2
            ['absCars', 'cars.json', null, [
                'Garage/cars/cars.json',
            ], [
                'Garage/cars/cars.json',
            ]],
        ];
    }

    /**
     * Convert an array of relative paths to absolute paths.
     *
     * @param string[] $paths relative paths
     *
     * @return string[] absolute paths
     */
    protected function relativeToAbsolutePaths(array $paths): array
    {
        $pathsWithAbsolute = [];
        foreach ($paths as $p) {
            $pathsWithAbsolute[] = $this->getBasePath() . $p;
        }

        return $pathsWithAbsolute;
    }

    /**
     * @return string
     */
    protected function getBasePath(): string
    {
        return Normalizer::normalizePath($this->basePath);
    }
}
