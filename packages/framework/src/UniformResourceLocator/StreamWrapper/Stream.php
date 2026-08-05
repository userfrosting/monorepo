<?php

declare(strict_types=1);

/*
 * UserFrosting Framework (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/framework
 * @copyright Copyright (c) 2013-2024 Alexander Weissman, Louis Charette, Jordan Mele
 * @license   https://github.com/userfrosting/framework/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\UniformResourceLocator\StreamWrapper;

use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

/**
 * Implements Read/Write Streams.
 */
class Stream implements StreamInterface
{
    /** @var resource */
    public $context;

    /** @var resource A generic resource handle. */
    protected $handle;

    /** @var ResourceLocatorInterface|null */
    protected static ?ResourceLocatorInterface $locator;

    /**
     * @param ResourceLocatorInterface $locator
     */
    public static function setLocator(ResourceLocatorInterface $locator): void
    {
        static::$locator = $locator;
    }

    /**
     * {@inheritDoc}
     */
    public function stream_open(string $uri, string $mode, int $options, ?string &$opened_path): bool
    {
        // In write mode, we want to write to the first existing path (should
        // be a shared location) because fopen will attempts to create the file.
        // Otherwise, we need to find the first found path, across location.
        if (in_array($mode, ['w', 'w+', 'a', 'a+', 'x', 'x+'], true)) {
            $path = $this->findPath($uri, true);
        } else {
            $path = $this->findPath($uri);
        }

        if ($path === null) {
            return false;
        }

        $handle = @fopen($path, $mode);

        // fopen will return false if file is not found or if mode is 'x' and
        // file already exist. See : https://www.php.net/manual/en/function.fopen
        if ($handle === false) {
            return false;
        }

        $this->handle = $handle;

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function stream_close(): void
    {
        fclose($this->handle);
    }

    /**
     * {@inheritDoc}
     */
    public function stream_lock(int $operation): bool
    {
        if ($operation < 0 || $operation > 7) {
            return false;
        }

        return flock($this->handle, $operation);
    }

    /**
     * {@inheritDoc}
     */
    public function stream_metadata(string $uri, int $option, mixed $value): bool
    {
        // For touch, we need the path even if it doesn't exist.
        $path = ($option === STREAM_META_TOUCH) ?
            $this->findPath($uri, true) :
            $this->findPath($uri);

        if ($path !== null) {
            switch ($option) {
                case STREAM_META_TOUCH:
                    $currentTime = \time();

                    return touch(
                        $path,
                        is_array($value) && array_key_exists(0, $value) ? $value[0] : $currentTime,
                        is_array($value) && array_key_exists(1, $value) ? $value[1] : $currentTime
                    );

                case STREAM_META_OWNER_NAME:
                    return chown($path, strval($value));

                case STREAM_META_OWNER:
                    return chown($path, intval($value));

                case STREAM_META_GROUP_NAME:
                    return chgrp($path, strval($value));

                case STREAM_META_GROUP:
                    return chgrp($path, intval($value));

                case STREAM_META_ACCESS:
                    return chmod($path, intval($value));
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function stream_read(int $count): string|false
    {
        $bytes = fread($this->handle, $count);

        return ($bytes === false) ? '' : $bytes;
    }

    /**
     * {@inheritDoc}
     */
    public function stream_write(string $data): int
    {
        $bytes = fwrite($this->handle, $data);

        return ($bytes === false) ? 0 : $bytes;
    }

    /**
     * {@inheritDoc}
     */
    public function stream_eof(): bool
    {
        return feof($this->handle);
    }

    /**
     * {@inheritDoc}
     */
    public function stream_seek(int $offset, int $whence = SEEK_SET): bool
    {
        // fseek returns 0 on success and -1 on a failure.
        return (fseek($this->handle, $offset, $whence) === 0) ? true : false;
    }

    /**
     * {@inheritDoc}
     */
    public function stream_flush(): bool
    {
        return fflush($this->handle);
    }

    /**
     * {@inheritDoc}
     */
    public function stream_tell(): int
    {
        // ftell return false if an error occurs.
        $position = ftell($this->handle);

        return ($position === false) ? 0 : $position;
    }

    /**
     * {@inheritDoc}
     */
    public function stream_stat(): array|false
    {
        return fstat($this->handle);
    }

    /**
     * {@inheritDoc}
     */
    public function unlink(string $uri): bool
    {
        $path = $this->findPath($uri);

        if ($path === null) {
            return false;
        }

        return unlink($path);
    }

    /**
     * {@inheritDoc}
     */
    public function rename(string $path_from, string $path_to): bool
    {
        $fromPath = $this->findPath($path_from);
        $toPath = $this->findPath($path_to, true);

        if ($fromPath === null || $toPath === null) {
            return false;
        }

        return rename($fromPath, $toPath);
    }

    /**
     * {@inheritDoc}
     */
    public function mkdir(string $path, int $mode, int $options): bool
    {
        $recursive = (bool) ($options & STREAM_MKDIR_RECURSIVE);
        $path = $this->findPath($path, true);

        if ($path === null) {
            return false;
        }

        return mkdir($path, $mode, $recursive);
    }

    /**
     * {@inheritDoc}
     */
    public function rmdir(string $path, int $options): bool
    {
        $path = $this->findPath($path);

        if ($path === null) {
            return false;
        }

        return rmdir($path);
    }

    /**
     * {@inheritDoc}
     */
    public function url_stat(string $path, int $flags): array|false
    {
        $path = $this->findPath($path);

        if ($path === null) {
            return false;
        }

        // Suppress warnings if requested or if the file or directory does not
        // exist. This is consistent with PHPs plain filesystem stream wrapper.
        return ($flags === STREAM_URL_STAT_QUIET || file_exists($path) === false) ? @stat($path) : stat($path);
    }

    /**
     * {@inheritDoc}
     */
    public function dir_opendir(string $path, int $options): bool
    {
        $path = $this->findPath($path);

        if ($path === null) {
            return false;
        }

        $handle = opendir($path);

        // opendir can return false when trying to open a file for example
        if ($handle === false) {
            return false;
        }

        $this->handle = $handle;

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function dir_readdir(): string
    {
        $filename = readdir($this->handle);

        if ($filename === false) {
            return '';
        }

        return $filename;
    }

    /**
     * {@inheritDoc}
     */
    public function dir_rewinddir(): bool
    {
        rewinddir($this->handle);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function dir_closedir(): bool
    {
        closedir($this->handle);

        return true;
    }

    /**
     * @param string $uri
     * @param bool   $all
     *
     * @return string|null
     */
    protected function findPath(string $uri, bool $all = false): ?string
    {
        if (!is_null(static::$locator) && static::$locator->isStream($uri)) {
            return static::$locator->getResource($uri, $all)?->getAbsolutePath();
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function stream_set_option(int $option, int $arg1, int $arg2)
    {
        return false;
    }
}
