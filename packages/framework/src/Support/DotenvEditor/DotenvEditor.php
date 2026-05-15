<?php

declare(strict_types=1);

/*
 * UserFrosting Framework (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/framework
 * @copyright Copyright (c) 2013-2024 Alexander Weissman, Louis Charette, Jordan Mele
 * @license   https://github.com/userfrosting/framework/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Support\DotenvEditor;

use RuntimeException;

/**
 * Minimal .env editor implementation.
 *
 * Provides a small subset of the original API used by UserFrosting:
 *  - load($path)
 *  - save()
 *  - setKey($k,$v,...)
 *  - getValue($k)
 *  - keyExists($k)
 */
class DotenvEditor
{
    /** @var string Path to the .env file */
    protected string $filePath = '';

    /** @var string[] The lines of the .env file */
    protected array $buffer = [];

    /** @var string Original content of the .env file */
    protected string $originalContent = '';

    /**
     * Load file for working.
     *
     * @param string $filePath The file path
     *
     * @return $this
     */
    public function load(string $filePath): static
    {
        $this->filePath = $filePath;

        if (file_exists($filePath) && is_readable($filePath)) {
            $lines = file($filePath, FILE_IGNORE_NEW_LINES);
            $this->buffer = $lines === false ? [] : $lines;
            $this->originalContent = implode("\n", $this->buffer);

            return $this;
        }

        // File missing: leave empty buffer and return instance
        $this->buffer = [];
        $this->originalContent = '';

        return $this;
    }

    /**
     * Check if the loaded file has been modified and needs saving.
     *
     * @return bool True if modified, false otherwise
     */
    public function hasChanged(): bool
    {
        return implode("\n", $this->buffer) !== $this->originalContent;
    }

    /**
     * Get the current content of the loaded file.
     *
     * @return string The content as a string
     */
    public function getContent(): string
    {
        return implode("\n", $this->buffer);
    }

    /**
     * Save changes to the loaded file.
     *
     * @param bool $rebuildBuffer Whether to rebuild the buffer before saving (not used in this implementation)
     *
     * @throws RuntimeException If no file is loaded
     *
     * @return $this
     */
    public function save(bool $rebuildBuffer = true): static
    {
        if ($this->filePath === '') {
            throw new RuntimeException('No file loaded to save');
        }

        $content = $this->getContent();

        // Preserve existing file permissions, or use 0644 for new files.
        // tempnam() creates files with 0600; without this chmod, the web server
        // process (e.g. www-data) would be unable to read a newly written .env.
        $filePerms = file_exists($this->filePath) ? (fileperms($this->filePath) & 0777) : 0644;

        // Use atomic write to avoid file corruption
        // Create temp file in system temp dir
        $tmpDir = $this->getTempDir();
        if (!is_dir($tmpDir) || !is_writable($tmpDir)) {
            throw new RuntimeException('Unable to create temp file for atomic save');
        }

        $tmpFile = $this->createTempFile($tmpDir);
        if ($tmpFile === false) {
            throw new RuntimeException('Unable to create temp file for atomic save');
        }
        file_put_contents($tmpFile, $content . PHP_EOL, LOCK_EX);
        rename($tmpFile, $this->filePath);
        chmod($this->filePath, $filePerms);

        $this->originalContent = $content;

        return $this;
    }

    /**
     * Return the temporary directory used by `save()`.
     *
     * Protected so tests can override to simulate failures.
     */
    protected function getTempDir(): string
    {
        return sys_get_temp_dir();
    }

    /**
     * Create a temporary file in the given directory. Separated for testability.
     *
     * @return string|false Path to temp file or false on failure
     */
    protected function createTempFile(string $dir): string|false
    {
        return tempnam($dir, 'env');
    }

    /**
     * Set or add a key-value pair.
     *
     * @param string      $key     The key to set
     * @param string|null $value   The value to set
     * @param string|null $comment An optional comment
     *
     * @return $this
     */
    public function setKey(string $key, ?string $value = null, ?string $comment = null): static
    {
        $val = $this->normalizeValueForWriting($value);
        $newLine = $key . '=' . $val;
        if ($comment !== null && $comment !== '') {
            $newLine .= ' # ' . $comment;
        }

        $index = $this->findLineIndex($key);
        if ($index !== null) {
            $this->buffer[$index] = $newLine;
        } else {
            $this->buffer[] = $newLine;
        }

        return $this;
    }

    /**
     * Get all entries from the loaded file.
     *
     * @return string[]
     */
    public function getEntries(): array
    {
        return $this->buffer;
    }

    /**
     * Check if a key exists.
     *
     * @param string $key The key to check
     *
     * @return bool True if the key exists, false otherwise
     */
    public function keyExists(string $key): bool
    {
        return $this->findLineIndex($key) !== null;
    }

    /**
     * Get the value of a key.
     *
     * @param string $key The key to get
     *
     * @return string|null The value of the key, or null if not found
     */
    public function getValue(string $key): ?string
    {
        $index = $this->findLineIndex($key);
        if ($index === null) {
            return null;
        }

        $line = $this->buffer[$index];

        // We already located the line with `findLineIndex()` so a full-pattern
        // match should succeed. Parse the value; fall back to empty string
        // if capture not set (defensive but avoids an unreachable branch).
        preg_match('/^\s*' . preg_quote($key, '/') . '\s*=\s*(.*)$/', $line, $m);
        $raw = isset($m[1]) ? trim($m[1]) : '';

        // Remove inline comment
        if (strpos($raw, ' # ') !== false) {
            $parts = explode(' # ', $raw, 2);
            $raw = trim($parts[0]);
        }

        // Strip surrounding quotes
        $raw = preg_replace('/^"(.*)"$/s', '$1', $raw) ?? $raw;
        $raw = str_replace('\\"', '"', $raw);

        return $raw;
    }

    /**
     * Delete a key.
     *
     * @param string $key The key to delete
     *
     * @return $this
     */
    public function deleteKey(string $key): static
    {
        $index = $this->findLineIndex($key);
        if ($index !== null) {
            array_splice($this->buffer, $index, 1);
        }

        return $this;
    }

    /**
     * Delete multiple keys.
     *
     * @param string[] $keys
     */
    public function deleteKeys(array $keys = []): static
    {
        foreach ($keys as $k) {
            $this->deleteKey($k);
        }

        return $this;
    }

    /**
     * Normalize a value for writing to the .env file.
     *
     * @param string|null $value The value to normalize
     *
     * @return string The normalized value
     */
    protected function normalizeValueForWriting(?string $value): string
    {
        $value = $value ?? '';
        if ($value === '' || preg_match('/\s|#|"|\$/', $value) === 1) {
            // Escape double quotes
            $escaped = str_replace('"', '\\"', $value);

            return '"' . $escaped . '"';
        }

        return $value;
    }

    /**
     * Find the index of the buffer line that defines the given key.
     *
     * @param string $key
     *
     * @return int|null The index in `$this->buffer` or null if not found
     */
    protected function findLineIndex(string $key): ?int
    {
        foreach ($this->buffer as $i => $line) {
            if (preg_match('/^\s*' . preg_quote($key, '/') . '\s*=/', $line) === 1) {
                return $i;
            }
        }

        return null;
    }
}
