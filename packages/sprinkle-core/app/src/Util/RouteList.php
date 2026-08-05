<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Util;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Slim\App;
use Slim\Interfaces\RouteInterface;

/**
 * Helper class for route:list Bakery Command.
 * Separate from the actual command for easier testing.
 */
class RouteList
{
    /**
     * Inject dependencies.
     *
     * @param App<\DI\Container> $app
     */
    public function __construct(protected App $app)
    {
    }

    /**
     * Get the route list from Slim App and generate an associative array to
     * display a table in the Bakery command.
     *
     * @param string|null $filterMethod
     * @param string|null $filterName
     * @param string|null $filterUri
     * @param bool        $reverse
     * @param string|null $sortBy
     *
     * @return string[][]
     */
    public function get(
        ?string $filterMethod = null,
        ?string $filterName = null,
        ?string $filterUri = null,
        ?string $filterAction = null,
        bool $reverse = false,
        ?string $sortBy = null
    ): array {
        // Get routes list from Slim App
        $routes = $this->app->getRouteCollector()->getRoutes();

        // If not route, don't go further
        if (count($routes) === 0) {
            return [];
        }

        // Compile the routes into a displayable format
        $routes = array_map(function ($route) use ($filterMethod, $filterName, $filterUri, $filterAction) {
            $route = $this->getInformation($route);
            $route = $this->filter($route, $filterMethod, $filterName, $filterUri, $filterAction);

            return $route;
        }, $routes);

        // Remove nulls
        $routes = array_filter($routes, fn ($route) => $route !== null);

        // Apply sort
        if (!is_null($sortBy)) {
            // Normalize case
            $sortBy = strtolower($sortBy);

            // Stop if sort is not right
            if (!in_array($sortBy, ['method', 'uri', 'name', 'action'], true)) {
                throw new \Exception('Sort option must be one of method, uri, name, action.');
            }

            $routes = $this->sort($sortBy, $routes);
        }

        // Apply reverse if required
        if ($reverse) {
            $routes = array_reverse($routes);
        }

        // Return routes, resetting keys
        return array_values($routes);
    }

    /**
     * Returns the route information for the display table.
     *
     * @param RouteInterface $route
     *
     * @return string[]
     */
    protected function getInformation(RouteInterface $route): array
    {
        $callable = is_string($route->getCallable()) ? $route->getCallable() : 'Callable';

        return [
            'method' => implode('|', $route->getMethods()),
            'uri'    => $route->getPattern(),
            'name'   => (string) $route->getName(),
            'action' => $callable,
        ];
    }

    /**
     * Sort the routes by a given element.
     *
     * @param string     $sort
     * @param string[][] $routes
     *
     * @return string[][]
     */
    protected function sort(string $sort, array $routes): array
    {
        return Arr::sort($routes, function ($route) use ($sort) {
            return $route[$sort];
        });
    }

    /**
     * Filter the route information.
     *
     * @param string[]    $route
     * @param string|null $method
     * @param string|null $name
     * @param string|null $uri
     * @param string|null $action
     *
     * @return string[]|null
     */
    protected function filter(array $route, ?string $method, ?string $name, ?string $uri, ?string $action): ?array
    {
        if (!$this->filterParam($route, 'name', $name) ||
            !$this->filterParam($route, 'uri', $uri) ||
            !$this->filterParam($route, 'method', $method) ||
            !$this->filterParam($route, 'action', $action)
        ) {
            return null;
        }

        return $route;
    }

    /**
     * Filter a specific param.
     *
     * @param string[]    $route The route information
     * @param string      $param The param to filter
     * @param string|null $value The value to filter against. If null, we allow as if a good match.
     *
     * @return bool True if value is found in route param
     */
    protected function filterParam(array $route, string $param, ?string $value): bool
    {
        if (is_null($value)) {
            return true;
        }

        return Str::contains(strtolower($route[$param]), strtolower($value));
    }
}
