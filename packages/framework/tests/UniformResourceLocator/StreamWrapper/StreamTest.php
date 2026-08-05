<?php

declare(strict_types=1);

/*
 * UserFrosting Framework (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/framework
 * @copyright Copyright (c) 2013-2024 Alexander Weissman, Louis Charette, Jordan Mele
 * @license   https://github.com/userfrosting/framework/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests\UniformResourceLocator\StreamWrapper;

use PHPUnit\Framework\TestCase;
use UserFrosting\UniformResourceLocator\ResourceLocator;
use UserFrosting\UniformResourceLocator\ResourceStream;
use UserFrosting\UniformResourceLocator\StreamWrapper\Stream;
use UserFrosting\UniformResourceLocator\StreamWrapper\StreamBuilder;

/**
 * Tests for Stream.
 */
class StreamTest extends TestCase
{
    protected string $stream;
    protected string $file;
    protected string $bad_file;
    protected string $bad_dir;
    protected StreamBuilder $builder;
    protected ResourceLocator $locator;

    public function setUp(): void
    {
        parent::setUp();

        // Remove streams that would have been registered by another test
        @stream_wrapper_unregister('foo');
        @stream_wrapper_unregister('bar');

        // Setup test strings
        $this->stream = 'foo://';
        $this->file = $this->stream . 'test.txt';
        $this->bad_file = $this->stream . 'bad.txt';
        $this->bad_dir = $this->stream . 'bar/foo';

        // Setup builder. 'bar' stream will point to nothing
        // 'foo' stream will be setup by locator
        $this->builder = new StreamBuilder();
        $this->builder->add('bar', Stream::class);

        // Setup test locator
        $this->locator = new ResourceLocator(__DIR__ . '/data', streamBuilder: $this->builder);
        $this->locator->addStream(new ResourceStream('foo', '', true));
    }

    public function tearDown(): void
    {
        parent::tearDown();

        // Reset dir using normal method
        @unlink(__DIR__ . '/data/test.txt');
        @rmdir(__DIR__ . '/data/bar');
        @rmdir(__DIR__ . '/data/foo');

        // Call stream_wrapper_unregister for later tests
        $this->builder->remove('foo');
        $this->builder->remove('bar');
    }

    public function testSimpleFile(): void
    {
        // Test file creation
        $this->assertFileDoesNotExist(__DIR__ . '/data/test.txt');
        $this->assertFalse(file_exists($this->file));
        $this->assertTrue(touch($this->file, time(), time()));
        $this->assertTrue(touch($this->file));
        $this->assertFileExists(__DIR__ . '/data/test.txt');
        $this->assertTrue(file_exists($this->file));
        $this->assertSame('', file_get_contents($this->file));
        $this->assertSame(3, file_put_contents($this->file, 'bar')); //@phpstan-ignore-line
        $this->assertSame('bar', file_get_contents($this->file));
        $this->assertSame(3, fwrite(fopen($this->file, 'r+'), 'foo')); // @phpstan-ignore-line
        $this->assertSame('foo', file_get_contents($this->file));
        $this->assertTrue(unlink($this->file));
        $this->assertFileDoesNotExist(__DIR__ . '/data/test.txt');
    }

    /**
     * Test dir creation & manipulation
     */
    public function testSimpleDir(): void
    {
        // With existing dir
        $this->assertTrue(file_exists($this->stream));
        $this->assertTrue(is_dir($this->stream));

        // With new dir
        $this->assertFalse(file_exists($this->stream . 'bar'));
        $this->assertFalse(is_dir($this->stream . 'bar'));
        $this->assertTrue(mkdir($this->stream . 'bar'));
        $this->assertTrue(file_exists($this->stream . 'bar'));
        $this->assertTrue(is_dir($this->stream . 'bar'));

        $dir = opendir($this->stream);
        $this->assertIsResource($dir);
        $entries = [];
        while ($entry = readdir($dir)) {
            $entries[] = $entry;
        }
        $this->assertContains('bar', $entries);
        closedir($dir); // Close dir to remove lock on Windows

        $this->assertTrue(rename($this->stream . 'bar', $this->stream . 'foo'));
        $this->assertTrue(rmdir($this->stream . 'foo'));
    }

    /**
     * @depends testSimpleFile
     */
    public function testDirectoryRewind(): void
    {
        $this->assertTrue(touch($this->file)); // Touch basic file
        $directoryResource = opendir($this->stream);
        $this->assertIsResource($directoryResource);
        $entry = readdir($directoryResource);
        rewinddir($directoryResource);
        $this->assertSame($entry, readdir($directoryResource));
        closedir($directoryResource); // Close dir to remove lock on Windows
        $this->assertTrue(unlink($this->file)); // Reset state
    }

    /**
     * @depends testSimpleFile
     */
    public function testChmod(): void
    {
        touch($this->file); // Touch basic file
        $this->assertTrue(is_readable($this->file));

        $stat = stat($this->file);
        $this->assertIsArray($stat);

        // chmod
        $permission = fileperms($this->file);
        $this->assertIsInt($permission);
        $this->assertTrue(chmod($this->file, 0755));

        // chown
        // Don't check true/false here, we just want to make sure it's called
        $owner = fileowner($this->file);
        $this->assertIsInt($owner);
        $this->assertIsBool(chown($this->file, $owner)); // @phpstan-ignore-line
        $this->assertIsBool(chown($this->file, get_current_user())); // @phpstan-ignore-line

        // chgrp
        $group = filegroup($this->file);
        $this->assertIsInt($group);
        $this->assertIsBool(chgrp($this->file, $group)); // @phpstan-ignore-line

        unlink($this->file);  // Reset state
    }

    /**
     * Test on a file that already exist.
     * @depends testSimpleFile
     */
    public function testFOpen(): void
    {
        touch($this->file);
        $this->assertTrue(file_exists($this->file)); // Make sure file exist, it's the point of the test
        $this->assertIsResource(fopen($this->file, 'w'));
        unlink($this->file);
    }

    /**
     * Make sure fopen create the file if it doesn't exist.
     * @depends testSimpleFile
     */
    public function testFOpenFileNotExistButWillCreate(): void
    {
        $this->assertFalse(file_exists($this->file));
        $this->assertIsResource(fopen($this->file, 'w'));
        $this->assertTrue(file_exists($this->file));
        unlink($this->file);
    }

    /**
     * Make sure fopen will return false if the file doesn't exist in readonly
     * mode. Even if locator return a resource path (with all flag).
     *
     * @depends testSimpleFile
     */
    public function testFOpenFileNotExist(): void
    {
        // stream_open will trigger an error even if the stream return false.
        // fopen(bar://test.txt): Failed to open stream: "UserFrosting\UniformResourceLocator\StreamWrapper\Stream::stream_open" call failed
        // This suppress this error, and allow us to test the return value.
        set_error_handler(function ($no, $str, $file, $line) { // @phpstan-ignore-line
        });

        $this->assertFalse(file_exists($this->file));
        $this->assertFalse(fopen($this->file, 'r'));
        $this->assertFalse(file_exists($this->file));
    }

    /**
     * @depends testSimpleFile
     */
    public function testFOpenInvalidPath(): void
    {
        // Catch exception
        set_error_handler(function ($no, $str, $file, $line) { // @phpstan-ignore-line
        });

        $this->assertFalse(fopen('bar://test.txt', 'w'));
    }

    /**
     * fopen will return false if mode is 'x' and file already exist.
     * @depends testSimpleFile
     */
    public function testFOpenFalseReturn(): void
    {
        // Catch exception
        set_error_handler(function ($no, $str, $file, $line) { // @phpstan-ignore-line
        });

        touch($this->file);
        $this->assertFalse(fopen($this->file, 'x'));
        unlink($this->file);
    }

    /**
     * @depends testSimpleFile
     */
    public function testFSeek(): void
    {
        // fseek returns 0 on success and -1 on a failure.
        touch($this->file); // Touch basic file
        $this->assertSame(0, fseek(fopen($this->file, 'r'), 0)); // @phpstan-ignore-line
        unlink($this->file); // Reset state
    }

    public function testFLock(): void
    {
        touch($this->file); // Touch basic file
        $fp = fopen($this->file, 'r+');
        $this->assertTrue(flock($fp, LOCK_EX)); // @phpstan-ignore-line
        unlink($this->file); // Reset state
    }

    public function testFLockRejectsOutOfRangeOperation(): void
    {
        $stream = new Stream();

        $this->assertFalse($stream->stream_lock(-1));
        $this->assertFalse($stream->stream_lock(8));
    }

    /**
     * Test when a stream exist, but the resource doesn't
     *
     * @depends testSimpleFile
     */
    public function testLocatorResourceIsFalse(): void
    {
        // Test making dir on non-existing stream
        // Test getPath return the proper false for both options.
        $this->assertFalse(mkdir('bar://'));
        $this->assertFalse(mkdir('bar://foo'));

        // Test touch will fail when resource stream doesn't exist.
        $this->assertFalse(touch('bar://'));
    }

    /**
     * @depends testSimpleFile
     */
    public function testOpenDirOnFile(): void
    {
        // dir_opendir will trigger an error even if the stream return false.
        set_error_handler(function ($no, $str, $file, $line) { // @phpstan-ignore-line
        });

        touch($this->file); // Touch basic file
        $this->assertFalse(opendir($this->file));
        unlink($this->file);  // Reset state
    }

    public function testDoesNotExist(): void
    {
        $this->assertFalse(file_exists($this->bad_file));
        $this->assertFalse(file_exists($this->bad_dir));
        $this->assertFalse(chmod($this->bad_file, 0755));
        $this->assertFalse(unlink($this->bad_file));
        $this->assertFalse(is_dir($this->bad_dir));
        $this->assertFalse(rename($this->bad_file, $this->bad_file));
        $this->assertFalse(rmdir($this->bad_dir));
    }

    public function testDoesNotExistWithException(): void
    {
        // stream_open will trigger an error even if the stream return false.
        set_error_handler(function ($no, $str, $file, $line) { // @phpstan-ignore-line
        });

        $this->assertFalse(opendir($this->bad_dir));
        $this->assertFalse(file_get_contents($this->bad_file));
        $this->assertFalse(mkdir($this->bad_dir));
    }

    public function testChgrpWithString(): void
    {
        // stream_open will trigger an error even if the stream return false.
        set_error_handler(function ($no, $str, $file, $line) { // @phpstan-ignore-line
        });

        touch($this->file); // Touch basic file
        $this->assertFalse(chgrp($this->file, 'root'));
        unlink($this->file);  // Reset state
    }

    /**
     * include() will throw error if `stream_set_option` is not implemented.
     * @runInSeparateProcess
     */
    public function testInclude(): void
    {
        $locator = new ResourceLocator(__DIR__);
        $locator->addStream(new ResourceStream('extra', shared: true));

        $array = include 'extra://adjectives.php'; // @phpstan-ignore-line
        $this->assertSame([
            'able',
            'above',
        ], $array);
    }
}
