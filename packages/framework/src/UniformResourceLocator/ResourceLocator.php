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

use BadMethodCallException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use SplFileInfo;
use UserFrosting\UniformResourceLocator\Exception\LocationNotFoundException;
use UserFrosting\UniformResourceLocator\Exception\StreamNotFoundException;
use UserFrosting\UniformResourceLocator\StreamWrapper\ReadOnlyStream;
use UserFrosting\UniformResourceLocator\StreamWrapper\Stream;
use UserFrosting\UniformResourceLocator\StreamWrapper\StreamBuilder;

/**
 * The locator is used to find resources.
 */
class ResourceLocator implements ResourceLocatorInterface
{
    /**
     * @var ResourceStreamInterface[][] The list of registered streams
     */
    protected array $streams = [];

    /**
     * @var ResourceLocationInterface[] The list of registered locations
     */
    protected array $locations = [];

    /**
     * @var string The location base path
     */
    protected string $basePath;

    /**
     * @var Filesystem
     */
    protected Filesystem $filesystem;

    /**
     * @var string Directory separator.
     *             N.B.: Will always be `/` regardless of the OS, as they are all added after normalization.
     */
    protected string $separator = '/';

    /**
     * @var StreamBuilder
     */
    protected StreamBuilder $streamBuilder;

    /**
     * @var string[] List of system reserved streams
     */
    protected array $reservedSchemes = ['file'];

    /**
     * @param string             $basePath
     * @param Filesystem|null    $filesystem
     * @param StreamBuilder|null $streamBuilder
     */
    public function __construct(
        string $basePath = '',
        ?Filesystem $filesystem = null,
        ?StreamBuilder $streamBuilder = null,
    ) {
        $this->basePath = $basePath;
        $this->filesystem = $filesystem ?? new Filesystem();
        $this->streamBuilder = $streamBuilder ?? new StreamBuilder();
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(string $uri): ?string
    {
        return $this->getResource($uri)?->getAbsolutePath();
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException If stream is reserved scheme
     *
     * @todo Separate Normal and Shared stream as two different class. Same for Readonly stream?
     */
    public function addStream(ResourceStreamInterface $stream): static
    {
        if (in_array($stream->getScheme(), $this->reservedSchemes, true)) {
            throw new InvalidArgumentException("Can't add restricted stream scheme {$stream->getScheme()}.");
        }

        $this->streams[$stream->getScheme()][] = $stream;
        $this->setupStreamWrapper($stream->getScheme(), $stream->isReadonly());

        return $this;
    }

    /**
     * Register the scheme as a php stream wrapper.
     *
     * @param string $scheme   The stream scheme
     * @param bool   $readonly Should the stream be instantiate as readonly
     */
    protected function setupStreamWrapper(string $scheme, bool $readonly = false): void
    {
        // First unset the scheme. Prevent issue if someone else already registered it
        $this->unsetStreamWrapper($scheme);

        // Select stream based on readonly status
        $stream = ($readonly) ? ReadOnlyStream::class : Stream::class;

        // register the scheme as a stream wrapper
        $this->streamBuilder->add($scheme, $stream);

        // Setup stream
        $stream::setLocator($this);
    }

    /**
     * Unset a php stream wrapper.
     *
     * @param string $scheme The stream scheme
     */
    protected function unsetStreamWrapper(string $scheme): void
    {
        $this->streamBuilder->remove($scheme);
    }

    /**
     * {@inheritdoc}
     */
    public function registerStream(
        string $scheme,
        string|array|null $paths = null,
        bool $shared = false,
        bool $readonly = false
    ): static {
        // Handle string or null argument
        if (!is_array($paths)) {
            $paths = [$paths];
        }

        // Invert arrays list. Last path has priority
        foreach (array_reverse($paths) as $path) {
            $stream = new ResourceStream($scheme, $path, $shared, $readonly);
            $this->addStream($stream);
        }

        return $this;
    }

    /**
     * Register a new shared stream.
     * Shortcut for registerStream with $shared flag set to true.
     *
     * @param string               $scheme
     * @param string|string[]|null $paths    (default null). When using null path, the scheme will be used as a path
     * @param bool                 $readonly
     *
     * @return static
     *
     * @deprecated Use `addStream` instead
     */
    public function registerSharedStream(
        string $scheme,
        string|array|null $paths = null,
        bool $readonly = false
    ): static {
        $this->registerStream($scheme, $paths, true, $readonly);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeStream(string $scheme): static
    {
        if (isset($this->streams[$scheme])) {
            $this->unsetStreamWrapper($scheme);
            unset($this->streams[$scheme]);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStream(string $scheme): array
    {
        if ($this->schemeExists($scheme)) {
            return $this->streams[$scheme];
        } else {
            throw new StreamNotFoundException();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getStreams(): array
    {
        return $this->streams;
    }

    /**
     * {@inheritdoc}
     */
    public function listSchemes(): array
    {
        return array_keys($this->streams);
    }

    /**
     * {@inheritdoc}
     */
    public function schemeExists(string $scheme): bool
    {
        return isset($this->streams[$scheme]);
    }

    /**
     * {@inheritdoc}
     */
    public function addLocation(ResourceLocationInterface $location): static
    {
        // Make sure name doesn't already exist
        $name = $location->getName();
        if ($this->locationExist($name)) {
            throw new InvalidArgumentException("Location with name {$name} is already registered.");
        }

        $this->locations[$name] = $location;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function registerLocation(string $name, ?string $path = null): static
    {
        $location = new ResourceLocation($name, $path);
        $this->addLocation($location);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeLocation(string $name): static
    {
        unset($this->locations[$name]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocation(string $name): ResourceLocationInterface
    {
        if (!$this->locationExist($name)) {
            throw new LocationNotFoundException();
        }

        return $this->locations[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function getLocations(): array
    {
        return array_reverse($this->locations);
    }

    /**
     * {@inheritdoc}
     */
    public function listLocations(): array
    {
        return array_keys(array_reverse($this->locations));
    }

    /**
     * {@inheritdoc}
     */
    public function locationExist(string $name): bool
    {
        return isset($this->locations[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getResource(string $uri, bool $all = false): ?ResourceInterface
    {
        try {
            list($scheme, $file) = Normalizer::normalizeParts($uri);
            $resources = $this->find($scheme, $file, $all);
        } catch (BadMethodCallException $e) {
            return null;
        }

        return (count($resources) === 0) ? null : $resources[0];
    }

    /**
     * {@inheritdoc}
     */
    public function getResources(string $uri, bool $all = false): array
    {
        try {
            list($scheme, $file) = Normalizer::normalizeParts($uri);

            return $this->find($scheme, $file, $all);
        } catch (BadMethodCallException $e) {
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function listResources(string $uri, bool $all = false, bool $sort = true): array
    {
        $list = [];

        // Get all directory where we can find this resource. Will be returned with the priority order
        foreach ($this->getResources($uri) as $directory) {
            // Use Filesystem to list all file in the directory
            $files = $this->filesystem->allFiles($directory->getAbsolutePath());

            // Sort files. Filesystem can return inconsistent order sometime
            // Files will be sorted alphabetically inside a location even if don't resort later across all sprinkles
            $files = Arr::sort($files, function (SplFileInfo $resource) {
                return $resource->getRealPath();
            });

            foreach ($files as $file) {
                // Calculate the relative path
                $basePath = rtrim($this->getBasePath(), $this->separator) . $this->separator;
                $fullPath = $file->getPathname();
                $relPath = str_replace($basePath, '', $fullPath);

                // Create the resource and add it to the list
                // Handle relPath that is an absolute outside the basePath
                // This can happen when the location has an absolute path outside the locator base path.
                if ($fullPath === $relPath) {
                    $resource = new Resource($directory->getStream(), $directory->getLocation(), $fullPath);
                } else {
                    $resource = new Resource($directory->getStream(), $directory->getLocation(), $relPath, $basePath);
                }

                if ($all) {
                    // Add all files to the list
                    $list[] = $resource;
                } else {
                    // Add file to the list it it's not already there from an higher priority location
                    if (!isset($list[$resource->getUri()])) {
                        $list[$resource->getUri()] = $resource;
                    }
                }
            }
        }

        // Apply global sorting if required. This will return all resources sorted
        // alphabetically instead of by priority
        if ($sort) {
            $list = Arr::sort($list, function (ResourceInterface $resource) {
                return $resource->getAbsolutePath();
            });
        }

        return array_values($list);
    }

    /**
     * Reset locator by removing all the registered streams and locations.
     *
     * @return static
     */
    public function reset(): static
    {
        $this->streams = [];
        $this->locations = [];

        return $this;
    }

    /**
     * Returns true if uri is resolvable by using locator.
     *
     * @param string $uri URI to test
     *
     * @return bool True if is resolvable
     */
    public function isStream(string $uri): bool
    {
        try {
            list($scheme) = Normalizer::normalizeParts($uri);
        } catch (\Exception $e) {
            return false;
        }

        return $this->schemeExists($scheme);
    }

    /**
     * {@inheritdoc}
     */
    public function findResource(string $uri, bool $absolute = true, bool $all = false): ?string
    {
        $resource = $this->getResource($uri, $all);

        return ($absolute) ? $resource?->getAbsolutePath() : $resource?->getPath();
    }

    /**
     * {@inheritdoc}
     */
    public function findResources(string $uri, bool $absolute = true, bool $all = false): array
    {
        $resources = $this->getResources($uri, $all);

        if ($absolute) {
            $callback = fn (ResourceInterface $resource): string => $resource->getAbsolutePath();
        } else {
            $callback = fn (ResourceInterface $resource): string => $resource->getPath();
        }

        return array_map($callback, $resources);
    }

    /**
     * Build the search path out of the defined stream and locations.
     * If the scheme is shared, we don't need to involve locations and can return it's path directly.
     *
     * @param ResourceStreamInterface $stream The stream to search for
     *
     * @return array<string,ResourceLocationInterface|null> The search paths based on this stream and all available locations
     */
    protected function searchPaths(ResourceStreamInterface $stream): array
    {
        // Stream is shared. We return it's value
        if ($stream->isShared()) {
            return [$stream->getPath() => null];
        }

        $list = [];
        foreach ($this->getLocations() as $location) {
            // Get location and stream path
            $parts = [];
            $parts[] = rtrim($location->getPath(), $this->separator);
            $parts[] = trim($stream->getPath(), $this->separator);

            // Merge both paths. Array_filter will take
            $parts = array_filter($parts, static fn (string $part): bool => $part !== '');
            $path = implode($this->separator, $parts);

            $list[$path] = $location;
        }

        return $list;
    }

    /**
     * Returns all Resource for given scheme and file.
     *
     * @param string $scheme The scheme to search in
     * @param string $file   The file to search for
     * @param bool   $all    Whether to return all paths even if they don't exist.
     *
     * @throws InvalidArgumentException
     *
     * @return ResourceInterface[]
     */
    protected function find(string $scheme, string $file, bool $all): array
    {
        // Make sure stream exist
        if (!$this->schemeExists($scheme)) {
            throw new InvalidArgumentException("Scheme {$scheme}:// doesn't exist.");
        }

        // Prepare result depending on $array parameter
        $results = [];

        foreach ($this->streams[$scheme] as $stream) {
            // Get all search paths using all locations
            $paths = $this->searchPaths($stream);

            // Get filename
            // Remove prefix from filename.
            // $filename = $this->separator . trim(substr($file, strlen($prefix)), '\/');
            $filename = $this->separator . trim($file, '\/');

            // Pass each search paths
            foreach ($paths as $path => $location) {
                $basePath = rtrim($this->getBasePath(), $this->separator) . $this->separator;

                // Check if path from the ResourceStream is absolute or relative
                // for both unix and windows
                if (preg_match('`^/|\w+:`', $path) === 0) {
                    // Handle relative path lookup.
                    $relPath = trim($path . $filename, $this->separator);
                    $fullPath = $basePath . $relPath;
                } else {
                    // Handle absolute path lookup.
                    $fullPath = rtrim($path . $filename, $this->separator);
                    $relPath = str_replace($basePath, '', $fullPath);
                }

                // Add the result to the list if the path exist, unless we want all results
                if ($all || $this->filesystem->exists($fullPath)) {
                    // Handle relative path that is an absolute outside the basePath
                    // This can happen when the location has an absolute path outside the locator base path.
                    if ($fullPath === $relPath) {
                        $currentResource = new Resource($stream, $location, $fullPath);
                    } else {
                        $currentResource = new Resource($stream, $location, $relPath, $basePath);
                    }

                    $results[] = $currentResource;
                }
            }
        }

        return $results;
    }

    /**
     * @return StreamBuilder
     */
    public function getStreamBuilder(): StreamBuilder
    {
        return $this->streamBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getBasePath(): string
    {
        return Normalizer::normalizePath($this->basePath);
    }
}
