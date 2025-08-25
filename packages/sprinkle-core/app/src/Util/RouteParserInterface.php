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

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;
use RuntimeException;
use Slim\Interfaces\RouteParserInterface as SlimRouteParserInterface;

/**
 * Add fallback route to Slim's RouteParserInterface.
 */
interface RouteParserInterface extends SlimRouteParserInterface
{
    /**
     * Build the path for a named route excluding the base path.
     *
     * @param string                                           $routeName     Route name
     * @param array<string, string>                            $data          Named argument replacement data
     * @param array<string, string | array<array-key, string>> $queryParams   Optional query string parameters
     * @param string|null                                      $fallbackRoute Optional fallback route (the actual route, not the route name)
     *
     * @throws RuntimeException         If named route does not exist
     * @throws InvalidArgumentException If required data not provided
     */
    public function relativeUrlFor(
        string $routeName,
        array $data = [],
        array $queryParams = [],
        ?string $fallbackRoute = null,
    ): string;

    /**
     * Build the path for a named route including the base path.
     *
     * @param string                                           $routeName     Route name
     * @param array<string, string>                            $data          Named argument replacement data
     * @param array<string, string | array<array-key, string>> $queryParams   Optional query string parameters
     * @param string|null                                      $fallbackRoute Optional fallback route (the actual route, not the route name)
     *
     * @throws RuntimeException         If named route does not exist
     * @throws InvalidArgumentException If required data not provided
     */
    public function urlFor(
        string $routeName,
        array $data = [],
        array $queryParams = [],
        ?string $fallbackRoute = null,
    ): string;

    /**
     * Get fully qualified URL for named route.
     *
     * @param UriInterface                                     $uri
     * @param string                                           $routeName     Route name
     * @param array<string, string>                            $data          Named argument replacement data
     * @param array<string, string | array<array-key, string>> $queryParams   Optional query string parameters
     * @param string|null                                      $fallbackRoute Optional fallback route (the actual route, not the route name)
     */
    public function fullUrlFor(
        UriInterface $uri,
        string $routeName,
        array $data = [],
        array $queryParams = [],
        ?string $fallbackRoute = null,
    ): string;
}
