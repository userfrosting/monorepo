<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Csrf;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\App;
use Slim\Csrf\Guard;
use UserFrosting\Config\Config;
use UserFrosting\Session\Session;
use UserFrosting\Sprinkle\Core\Exceptions\CsrfMissingException;

/**
 * Custom Slim Csrf Provider implementation.
 *
 * Allow injection of the Slim App, use of our session storage, custom error
 * handling, blacklist from the config service, etc.
 */
class CsrfGuard extends Guard
{
    /**
     * Overwrites the default constructor to inject dependencies.
     *
     * @param Config             $config
     * @param App<\DI\Container> $app
     */
    public function __construct(
        protected Config $config,
        protected Session $session,
        App $app,
    ) {
        // Use default session storage, but make sure it's active first.ù
        $session->start();
        $sessionStorage = null;

        // Define onFailure callback to throw a CsrfMissingException
        $onFailure = function ($request, $response) {
            throw new CsrfMissingException('The CSRF code was invalid or not provided.');
        };

        parent::__construct(
            $app->getResponseFactory(),
            $config->getString('csrf.name', 'csrf'),
            $sessionStorage,
            $onFailure,
            $config->getInt('csrf.storage_limit', 200),
            $config->getInt('csrf.strength', 16),
            $config->getBool('csrf.persistent_token', true)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();
        $method = $request->getMethod();

        // Normalize path to always have a leading slash
        $path = '/' . ltrim($path, '/');

        // Normalize method to uppercase.
        $method = strtoupper($method);

        /** @var array<string,string[]> */
        $csrfBlacklist = $this->config->getArray('csrf.blacklist');
        $isBlacklisted = false;

        // Go through the blacklist and determine if the path and method match any of the blacklist entries.
        foreach ($csrfBlacklist as $pattern => $methods) {
            $methods = array_map('strtoupper', $methods);
            if (in_array($method, $methods, true) && $pattern !== '' && preg_match('~' . $pattern . '~', $path) === 1) {
                $isBlacklisted = true;
                break;
            }
        }

        if ($isBlacklisted === false) {
            return parent::process($request, $handler);
        }

        return $handler->handle($request);
    }
}
