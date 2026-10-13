<?php

declare(strict_types=1);

/*
 * UserFrosting Framework (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/framework
 * @copyright Copyright (c) 2013-2024 Alexander Weissman, Louis Charette, Jordan Mele
 * @license   https://github.com/userfrosting/framework/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Cache\Driver;

use Illuminate\Cache\FileLock;
use Illuminate\Cache\FileStore;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

/**
 * Custom file based cache driver with supports for Tags
 * Inspired by unikent/taggedFileCache.
 */
class TaggableFileStore extends FileStore
{
    /**
     * @var string The separator when creating a tagged directory.
     */
    public string $separator;

    /**
     * @var string The directory where the tags list is stored
     */
    public string $tagRepository = 'cache_tags';

    /**
     * @var string Directory separator.
     */
    public string $ds = '/';

    /**
     * Create a new file cache store instance.
     *
     * @param Filesystem $files
     * @param string     $directory
     * @param string     $separator
     */
    public function __construct(Filesystem $files, string $directory, string $separator = '~#~')
    {
        $this->separator = $separator;
        parent::__construct($files, $directory);
    }

    /**
     * {@inheritDoc}
     */
    public function lock($name, $seconds = 0, $owner = null)
    {
        $directory = $this->lockDirectory ?? $this->directory;
        $this->ensureCacheDirectoryExists($directory);

        return new FileLock(new TaggableFileStore($this->files, $directory, $this->separator), $name, $seconds, $owner);
    }

    /**
     * {@inheritDoc}
     */
    public function path($key)
    {
        $isTag = false;
        $split = explode($this->separator, $key); //@phpstan-ignore-line

        if (count($split) > 1) {
            $folder = reset($split) . $this->ds;

            if ($folder === $this->tagRepository . $this->ds) {
                $isTag = true;
            }
            $key = end($split);
        } else {
            $key = reset($split);
            $folder = '';
        }

        if ($isTag) {
            $hash = $key;
            $parts = [];
        } else {
            $parts = array_slice(str_split($hash = sha1($key), 2), 0, 2);
        }

        return $this->directory . $this->ds . $folder . (count($parts) > 0 ? implode($this->ds, $parts) . $this->ds : '') . $hash;
    }

    /**
     * Begin executing a new tags operation.
     *
     * @param array|mixed $names
     *
     * @return TaggedFileCache
     */
    public function tags($names)
    {
        return new TaggedFileCache($this, new FileTagSet($this, is_array($names) ? $names : func_get_args()));
    }

    /**
     * Flush old tags path when a tag is flushed.
     *
     * @param string $tagId
     */
    public function flushOldTag($tagId): void
    {
        foreach ($this->files->directories($this->directory) as $directory) {
            if (Str::contains(basename($directory), $tagId)) {
                $this->files->deleteDirectory($directory);
            }
        }
    }
}
