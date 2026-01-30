<?php

declare(strict_types=1);

/*
 * UserFrosting Framework (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/framework
 * @copyright Copyright (c) 2013-2024 Alexander Weissman, Louis Charette, Jordan Mele
 * @license   https://github.com/userfrosting/framework/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests\Cache;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use UserFrosting\Cache\Driver\FileTagSet;
use UserFrosting\Cache\Driver\TaggableFileStore;
use UserFrosting\Cache\Driver\TaggedFileCache;

class TaggableFileDriverTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public Filesystem $file;
    public string $path;

    public function setUp(): void
    {
        parent::setUp();
        $this->file = new Filesystem();
        $this->path = __DIR__ . '/store';
    }

    public function testTagKeyGeneratesPrefixedKey(): void
    {
        $store = new TaggableFileStore($this->file, $this->path);
        $tagSet = new FileTagSet($store, ['foobar']);
        $this->assertEquals('cache_tags~#~foobar', $tagSet->tagKey('foobar'));
    }

    public function testTagKeyGeneratesPrefixedKeyWithCustomSeparator(): void
    {
        $store = new TaggableFileStore($this->file, $this->path, '~|~');
        $tagSet = new FileTagSet($store, ['foobar']);
        $this->assertEquals('cache_tags~|~foobar', $tagSet->tagKey('foobar'));
    }

    public function testPathGeneratesCorrectPathForKeyWithoutSeparator(): void
    {
        $reflectionMethod = new \ReflectionMethod(TaggableFileStore::class, 'path');

        $store = new TaggableFileStore($this->file, $this->path);
        $path = $reflectionMethod->invoke($store, 'foobar');

        $this->assertTrue(Str::contains($path, $this->path));
        $this->assertTrue(str_replace($this->path, '', $path) === '/88/43/8843d7f92416211de9ebb963ff4ce28125932878');
    }

    public function testPathGeneratesCorrectPathForKeyWithSeparator(): void
    {
        $reflectionMethod = new \ReflectionMethod(TaggableFileStore::class, 'path');

        $store = new TaggableFileStore($this->file, $this->path);
        $path = $reflectionMethod->invoke($store, 'boofar~#~foobar');

        $this->assertTrue(Str::contains($path, $this->path));
        $this->assertTrue(str_replace($this->path, '', $path) === '/boofar/88/43/8843d7f92416211de9ebb963ff4ce28125932878');
    }

    public function testPathGeneratesCorrectPathForKeyWithCustomSeparator(): void
    {
        $reflectionMethod = new \ReflectionMethod(TaggableFileStore::class, 'path');

        $store = new TaggableFileStore($this->file, $this->path, '~|~');
        $path = $reflectionMethod->invoke($store, 'boofar~|~foobar');

        $this->assertTrue(Str::contains($path, $this->path));
        $this->assertTrue(str_replace($this->path, '', $path) === '/boofar/88/43/8843d7f92416211de9ebb963ff4ce28125932878');
    }

    public function testTagsReturnsTaggedFileCache(): void
    {
        $store = new TaggableFileStore($this->file, $this->path);

        $cache = $store->tags(['abc', 'def']);

        $this->assertInstanceOf(TaggedFileCache::class, $cache); // @phpstan-ignore-line
    }

    public function testFlushOldTagDeletesTagFolders(): void
    {
        /** @var Filesystem&\Mockery\MockInterface */
        $filesMock = Mockery::mock(new Filesystem());
        $store = new TaggableFileStore($filesMock, '/');

        $filesMock->shouldReceive('directories')->with('/')->andReturn([
            'test/foobar',
            'foobar',
            'testfoobar',
            'testfoobartest',
            'test/testfoobartest',
        ]);

        $filesMock->shouldReceive('deleteDirectory')->with('test/foobar')->once();
        $filesMock->shouldReceive('deleteDirectory')->with('foobar')->once();
        $filesMock->shouldReceive('deleteDirectory')->with('testfoobar')->once();
        $filesMock->shouldReceive('deleteDirectory')->with('testfoobartest')->once();
        $filesMock->shouldReceive('deleteDirectory')->with('test/testfoobartest')->once();

        $store->flushOldTag('foobar');
    }

    public function testFlushOldTagDoesNotDeletesOtherFolders(): void
    {
        /** @var Filesystem&\Mockery\MockInterface */
        $filesMock = Mockery::mock(new Filesystem());
        $store = new TaggableFileStore($filesMock, '/');

        $filesMock->shouldReceive('directories')->with('/')->andReturn([
            'test/foobar/foo',
            'foobar/test',
            'test',
        ]);

        $filesMock->shouldNotReceive('deleteDirectory')->with('test/foobar/foo');
        $filesMock->shouldNotReceive('deleteDirectory')->with('foobar/test');
        $filesMock->shouldNotReceive('deleteDirectory')->with('test');

        $store->flushOldTag('foobar');
    }

    public function testItemKeyCallsTaggedItemKey(): void
    {
        $store = new TaggableFileStore($this->file, $this->path);
        $cache = new TaggedFileCache($store, new FileTagSet($store, ['foobar']));

        $mock = Mockery::mock($cache);
        $mock->shouldReceive('taggedItemKey')->with('test')->once();

        $mock->itemKey('test'); // @phpstan-ignore-line
    }

    public function testItemKeyReturnsTaggedItemKey(): void
    {
        $store = new TaggableFileStore($this->file, $this->path);
        $cache = new TaggedFileCache($store, new FileTagSet($store, ['foobar']));

        $mock = Mockery::mock($cache);
        $mock->shouldReceive('taggedItemKey')->with('test')->andReturn('boofar');

        $this->assertEquals('boofar', $mock->itemKey('test')); // @phpstan-ignore-line
    }
}
