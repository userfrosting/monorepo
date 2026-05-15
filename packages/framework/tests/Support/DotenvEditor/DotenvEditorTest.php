<?php

declare(strict_types=1);

/*
 * UserFrosting Framework (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/framework
 * @copyright Copyright (c) 2013-2024 Alexander Weissman, Louis Charette, Jordan Mele
 * @license   https://github.com/userfrosting/framework/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests\Support\DotenvEditor;

use PHPUnit\Framework\TestCase;
use UserFrosting\Support\DotenvEditor\DotenvEditor;

class DotenvEditorTest extends TestCase
{
    protected string $basePath = __DIR__ . '/data/';

    public function testLoad(): void
    {
        $editor = new DotenvEditor();
        $editor->load($this->basePath . '.env');
        $this->assertEquals('dbpass', $editor->getValue('DB_PASSWORD'));
    }

    public function testLoadPathNotExist(): void
    {
        $editor = new DotenvEditor();
        $result = $editor->load($this->basePath . '.fakeEnv');
        $this->assertEquals($editor, $result);
    }

    public function testLoadPathNotExistAndRestore(): void
    {
        $editor = new DotenvEditor();
        $editor->load($this->basePath . '.env');

        $result = $editor->load($this->basePath . '.fakeEnv');
        $this->assertEquals($editor, $result);

        // Cleanup if test created a file
        if (file_exists($this->basePath . '.fakeEnv')) {
            unlink($this->basePath . '.fakeEnv');
        }
    }

    public function testSaveWithoutLoadThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        $editor = new DotenvEditor();
        $editor->save();
    }

    public function testSetNewKeyUpdateAndSave(): void
    {
        // Use temp file to avoid modifying provided .env
        $src = $this->basePath . '.env';
        $tmp = sys_get_temp_dir() . '/uf_env_' . uniqid();
        copy($src, $tmp);

        $editor = new DotenvEditor();
        $editor->load($tmp);

        $this->assertFalse($editor->keyExists('NEW_KEY'));
        $this->assertTrue($editor->keyExists('DB_PASSWORD'));

        $editor->setKey('NEW_KEY', 'some value', 'a comment');
        $this->assertTrue($editor->keyExists('NEW_KEY'));
        $this->assertSame('some value', $editor->getValue('NEW_KEY'));
        $this->assertTrue($editor->hasChanged());

        $editor->save();

        $content = file_get_contents($tmp);
        $this->assertStringContainsString('NEW_KEY="some value" # a comment', $content); // @phpstan-ignore-line

        // Update existing key
        $editor->setKey('DB_PASSWORD', 'newpass');
        $this->assertSame('newpass', $editor->getValue('DB_PASSWORD'));
        $editor->save();

        $editor2 = new DotenvEditor();
        $editor2->load($tmp);
        $this->assertSame('newpass', $editor2->getValue('DB_PASSWORD'));

        unlink($tmp);
    }

    public function testDeleteKeysAndGetters(): void
    {
        // Use temp file to avoid modifying provided .env
        $src = $this->basePath . '.env';
        $tmp = sys_get_temp_dir() . '/uf_env_' . uniqid();
        copy($src, $tmp);

        $editor = new DotenvEditor();
        $editor->load($tmp);

        $this->assertTrue($editor->keyExists('DB_PASSWORD'));
        $editor->deleteKey('DB_PASSWORD');
        $this->assertFalse($editor->keyExists('DB_PASSWORD'));

        // delete multiple
        $editor->setKey('A', '1');
        $editor->setKey('B', '2');
        $this->assertTrue($editor->keyExists('A'));
        $this->assertTrue($editor->keyExists('B'));
        $editor->deleteKeys(['A', 'B']);
        $this->assertFalse($editor->keyExists('A'));
        $this->assertFalse($editor->keyExists('B'));

        $entries = $editor->getEntries();
        $this->assertStringContainsString('SMTP_HOST', implode("\n", $entries));
        $this->assertStringContainsString('SMTP_HOST', $editor->getContent());

        unlink($tmp);
    }

    public function testQuotedValueRoundtrip(): void
    {
        // Use temp file to avoid modifying provided .env
        $src = $this->basePath . '.env';
        $tmp = sys_get_temp_dir() . '/uf_env_' . uniqid();
        copy($src, $tmp);

        $editor = new DotenvEditor();
        $editor->load($tmp);

        $value = 'say "hello" world';
        $editor->setKey('QUOTED', $value);
        $this->assertSame('say "hello" world', $editor->getValue('QUOTED'));
        $editor->save();

        $raw = file_get_contents($tmp);
        $this->assertStringContainsString('QUOTED="say \\"hello\\" world"', $raw); // @phpstan-ignore-line

        $editor2 = new DotenvEditor();
        $editor2->load($tmp);
        $this->assertSame('say "hello" world', $editor2->getValue('QUOTED'));

        unlink($tmp);
    }

    public function testSaveTempFileFailureThrows(): void
    {
        // Use temp file to avoid modifying provided .env
        $src = $this->basePath . '.env';
        $tmp = sys_get_temp_dir() . '/uf_env_' . uniqid();
        copy($src, $tmp);

        // Create a small subclass to override temp dir to a non-writable dir
        $badDir = sys_get_temp_dir() . '/uf_bad_' . uniqid();
        mkdir($badDir);
        chmod($badDir, 0444);

        $editor = new class($badDir) extends DotenvEditor {
            private string $dir;

            public function __construct(string $dir)
            {
                $this->dir = $dir;
            }

            protected function getTempDir(): string
            {
                return $this->dir;
            }
        };

        $editor->load($tmp);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to create temp file for atomic save');
        try {
            $editor->save();
        } finally {
            // Cleanup
            chmod($badDir, 0755);
            rmdir($badDir);
            unlink($tmp);
        }
    }

    public function testSaveTempnamFailureThrows(): void
    {
        // Use temp file to avoid modifying provided .env
        $src = $this->basePath . '.env';
        $tmp = sys_get_temp_dir() . '/uf_env_' . uniqid();
        copy($src, $tmp);

        $editor = new class() extends DotenvEditor {
            protected function createTempFile(string $dir): string|false
            {
                return false;
            }
        };

        $editor->load($tmp);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to create temp file for atomic save');
        try {
            $editor->save();
        } finally {
            unlink($tmp);
        }
    }

    public function testProtectedHelpers(): void
    {
        $editor = new class() extends DotenvEditor {
            public function callNormalize(?string $v): string
            {
                return $this->normalizeValueForWriting($v);
            }

            // @phpstan-ignore-next-line
            public function callFind(array $buffer, string $key): ?int
            {
                $this->buffer = $buffer;

                return $this->findLineIndex($key);
            }

            public function callGetTempDir(): string
            {
                return $this->getTempDir();
            }
        };

        $this->assertSame('""', $editor->callNormalize(null));
        $this->assertSame('simple', $editor->callNormalize('simple'));
        $this->assertSame('"with space"', $editor->callNormalize('with space'));

        $idx = $editor->callFind(['A=1', 'B=2'], 'B');
        $this->assertSame(1, $idx);

        $this->assertIsString($editor->callGetTempDir()); // @phpstan-ignore-line

        // Leading-space key matching and exported prefix should not match (export removed)
        $idx2 = $editor->callFind(['   KEY=1', 'export OTHER=2'], 'KEY');
        $this->assertSame(0, $idx2);

        $idx3 = $editor->callFind(['   KEY=1', 'export OTHER=2'], 'OTHER');
        $this->assertNull($idx3);
    }

    public function testSaveCreatesFileWhenNotExist(): void
    {
        $tmp = sys_get_temp_dir() . '/uf_env_new_' . uniqid();

        // Ensure file does not exist
        if (file_exists($tmp)) {
            unlink($tmp);
        }

        $editor = new DotenvEditor();
        // Load sets filePath even if file missing
        $editor->load($tmp);
        $this->assertFalse(file_exists($tmp));

        $editor->setKey('CREATED', 'yes');
        $this->assertTrue($editor->hasChanged());
        $editor->save();

        $this->assertFileExists($tmp);
        $this->assertStringContainsString('CREATED=yes', file_get_contents($tmp)); // @phpstan-ignore-line

        unlink($tmp);
    }

    public function testGetValueMissingKeyReturnsNull(): void
    {
        $editor = new DotenvEditor();
        // populate buffer with a different key
        $editor->load($this->basePath . '.env');

        $this->assertNull($editor->getValue('THIS_KEY_DOES_NOT_EXIST'));
    }

    public function testEmptyValueIsQuotedWhenWritten(): void
    {
        $tmp = sys_get_temp_dir() . '/uf_env_empty_' . uniqid();
        if (file_exists($tmp)) {
            unlink($tmp);
        }

        $editor = new DotenvEditor();
        $editor->load($tmp);
        $editor->setKey('EMPTYVAL', '');
        $this->assertSame('', $editor->getValue('EMPTYVAL'));
        $editor->save();

        $raw = file_get_contents($tmp);
        $this->assertStringContainsString('EMPTYVAL=""', $raw); // @phpstan-ignore-line

        unlink($tmp);
    }

    public function testSavePreservesExistingPermissions(): void
    {
        if (posix_geteuid() === 0) {
            $this->markTestSkipped('Cannot test file permissions as root.');
        }

        $tmp = sys_get_temp_dir() . '/uf_env_perms_' . uniqid();
        file_put_contents($tmp, "FOO=bar\n");
        chmod($tmp, 0640);

        try {
            $editor = new DotenvEditor();
            $editor->load($tmp);
            $editor->setKey('BAR', 'baz');
            $editor->save();

            $this->assertSame(0640, fileperms($tmp) & 0777);
        } finally {
            if (file_exists($tmp)) {
                unlink($tmp);
            }
        }
    }

    public function testSaveNewFileHasDefaultPermissions(): void
    {
        if (posix_geteuid() === 0) {
            $this->markTestSkipped('Cannot test file permissions as root.');
        }

        $tmp = sys_get_temp_dir() . '/uf_env_newperms_' . uniqid();
        if (file_exists($tmp)) {
            unlink($tmp);
        }

        try {
            $editor = new DotenvEditor();
            $editor->load($tmp);
            $editor->setKey('NEW', 'value');
            $editor->save();

            $this->assertFileExists($tmp);
            $this->assertSame(0644, fileperms($tmp) & 0777);
        } finally {
            if (file_exists($tmp)) {
                unlink($tmp);
            }
        }
    }
}
