<?php

declare(strict_types=1);

/*
 * UserFrosting Framework (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/framework
 * @copyright Copyright (c) 2013-2024 Alexander Weissman, Louis Charette, Jordan Mele
 * @license   https://github.com/userfrosting/framework/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\UniformResourceLocator;

use Stringable;

/**
 * The representation of a resource.
 */
interface ResourceInterface extends Stringable
{
    /**
     * Get Resource URI.
     *
     * @return string
     */
    public function getUri(): string;

    /**
     * Get Resource directory URI.
     * Represents the directory path of the resource, indicating
     * where it is located. Applies whether the resource is a file
     * or a directory.
     *
     * @return string
     */
    public function getDirUri(): string;

    /**
     * Get the resource base path, aka the path that comes after the `://`.
     *
     * @return string
     */
    public function getBasePath(): string;

    /**
     * Extract the resource filename (e.g. /location/stream/foo/test.txt -> test).
     * If resource is a directory, return empty string.
     *
     * @return string
     */
    public function getFilename(): string;

    /**
     * Extract the trailing name component (e.g. /location/stream/foo/test.txt -> test.txt).
     * If resource is a directory, return empty string.
     *
     * @return string
     */
    public function getBasename(): string;

    /**
     * Extract the resource extension (e.g. /location/stream/test.txt -> txt).
     * If resource is a directory, return empty string.
     *
     * @return string
     */
    public function getExtension(): string;

    /**
     * Extract the resource dirname, relative to the locator
     * base path. This returns the path where the resource is
     * located, whether it is a file or a directory.
     * e.g. `/var/www/site/location/stream/foo/test.txt' -> `/location/stream/foo`
     * e.g. `/var/www/site/location/stream/foo/bar' -> `/location/stream/foo`
     *
     * @return string
     */
    public function getRelativeDirname(): string;

    /**
     * Extract the absolute directory path of the resource.
     * Returns the path where the resource is located, whether
     * it is a file or a directory.
     * e.g. `/var/www/site/location/stream/foo/test.txt' -> `/var/www/site/location/stream/foo`
     *
     * @return string
     */
    public function getAbsoluteDirname(): string;

    /**
     * Extract the resource dirname, relative to the stream URI.
     * Returns the path where the resource is located, whether it
     * is a file or a directory.
     * e.g. `/location/stream/foo/test.txt' -> `foo`
     *
     * @return string
     */
    public function getDirname(): string;

    /**
     * Check if the resource is a directory.
     *
     * @return bool
     */
    public function isDir(): bool;

    /**
     * @return ResourceLocationInterface|null
     */
    public function getLocation(): ?ResourceLocationInterface;

    /**
     * Return the absolute path to the resource on the filesystem.
     *
     * @return string
     */
    public function getAbsolutePath(): string;

    /**
     * Resource path, relative to the locator base path, and containing the stream and location path
     *
     * @return string
     */
    public function getPath(): string;

    /**
     * Locator base Path
     *
     * @return string
     */
    public function getLocatorBasePath(): string;

    /**
     * @return ResourceStreamInterface
     */
    public function getStream(): ResourceStreamInterface;
}
