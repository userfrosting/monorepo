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
use UserFrosting\UniformResourceLocator\StreamWrapper\StreamBuilder;

/**
 * Tests for Stream.
 */
class ReadOnlyStreamTest extends TestCase
{
    protected string $stream;
    protected string $file;
    protected string $dir;
    protected StreamBuilder $builder;
    protected ResourceLocator $locator;

    public function setUp(): void
    {
        parent::setUp();

        // Remove streams that would have been registered by another test
        @stream_wrapper_unregister('foo');

        // Setup test strings
        $this->stream = 'foo://';
        $this->file = $this->stream . 'test.txt';
        $this->dir = $this->stream . 'bar';

        // Setup builder. The 'foo' stream will be setup by locator
        $this->builder = new StreamBuilder();

        // Setup test locator
        $this->locator = new ResourceLocator(__DIR__ . '/data', streamBuilder: $this->builder);
        $this->locator->addStream(new ResourceStream('foo', '', true, true));

        // Create test file and dir
        @touch(__DIR__ . '/data/test.txt');
        @mkdir(__DIR__ . '/data/bar');
    }

    public function tearDown(): void
    {
        parent::tearDown();

        // Reset dir using normal method
        @unlink(__DIR__ . '/data/test.txt');
        @rmdir(__DIR__ . '/data/bar');

        // Call stream_wrapper_unregister for later tests
        $this->builder->remove('foo');
    }

    public function testReadonly(): void
    {
        // Catch all `triggerError`
        set_error_handler(function ($no, $str, $file, $line) { // @phpstan-ignore-line
        });

        $this->assertFalse(rmdir($this->dir));
        $this->assertFalse(mkdir($this->dir . '/foorbar'));
        $this->assertFalse(rename($this->file, $this->file));
        $this->assertFalse(unlink($this->file));
        $this->assertFalse(touch($this->file));
        $this->assertFalse(chmod($this->file, 0755));
        $this->assertFalse(chown($this->file, 'root'));
        $this->assertFalse(chgrp($this->file, 'root'));
        $this->assertFalse(fopen($this->file, 'a'));
        $this->assertFalse(file_put_contents($this->file, 'bar'));
        $this->assertFalse(flock(fopen($this->file, 'r'), LOCK_EX)); // @phpstan-ignore-line
        $this->assertSame(0, fwrite(fopen($this->file, 'r'), 'foo')); // @phpstan-ignore-line
    }

    public function testFileCanBeRead(): void
    {
        // File
        $this->assertFileExists(__DIR__ . '/data/test.txt');
        $this->assertTrue(file_exists($this->file));
        $this->assertTrue(is_readable($this->file));
        $fp = fopen($this->file, 'r');
        $this->assertIsResource($fp);
        $this->assertSame('', file_get_contents($this->file));

        // Dir
        $this->assertTrue(is_dir($this->dir));
        $dir = opendir($this->dir);
        $this->assertIsResource($dir);
        $entries = [];
        while ($entry = readdir($dir)) {
            $entries[] = $entry;
        }
        // Sort results, as order might differ depending of OS
        $expected = ['..', '.'];
        sort($expected);
        sort($entries);
        $this->assertSame($expected, $entries);
        closedir($dir); // Close dir to remove lock on Windows

        // Permissions
        $stat = stat($this->file);
        $this->assertIsArray($stat);
        $this->assertIsInt(fileperms($this->file));
        $this->assertIsInt(fileowner($this->file));
        $this->assertIsInt(filegroup($this->file));

        // File can have a shared lock (reader)
        $this->assertTrue(flock(fopen($this->file, 'r'), LOCK_SH)); // @phpstan-ignore-line
    }

    /**
     * include() will throw error if `stream_set_option` is not implemented.
     * @runInSeparateProcess
     */
    public function testInclude(): void
    {
        $locator = new ResourceLocator(__DIR__);
        $locator->addStream(new ResourceStream('extra', shared: true, readonly: true));

        $array = include 'extra://adjectives.php'; // @phpstan-ignore-line
        $this->assertSame([
            'able',
            'above',
        ], $array);
    }
}
