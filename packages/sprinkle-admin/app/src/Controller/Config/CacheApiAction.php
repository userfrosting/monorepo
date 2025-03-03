<?php

declare(strict_types=1);

/*
 * UserFrosting Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-admin
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Admin\Controller\Config;

use Illuminate\Cache\Repository as Cache;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UserFrosting\I18n\Translator;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Exceptions\ForbiddenException;
use UserFrosting\Sprinkle\Core\Bakery\ClearCacheCommand;

/**
 * Controller class for clear cache api.
 */
class CacheApiAction
{
    /**
     * Inject dependencies.
     */
    public function __construct(
        protected Translator $translator,
        protected Authenticator $authenticator,
        protected ClearCacheCommand $clearCacheCommand,
    ) {
    }

    /**
     * Receive the request, dispatch to the handler, and return the payload to
     * the response.
     *
     * @param Request  $request
     * @param Response $response
     */
    public function __invoke(Request $request, Response $response): Response
    {
        $this->validateAccess();
        $this->clearCacheCommand->clearIlluminateCache();
        $this->clearCacheCommand->clearTwigCache();
        $this->clearCacheCommand->clearRouterCache();

        // Message
        $message = $this->translator->translate('SITE_CONFIG.CACHE.CLEARED');

        // Write empty response
        $payload = json_encode([
            'message' => $message,
        ], JSON_THROW_ON_ERROR);
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Validate access to the page.
     *
     * @throws ForbiddenException
     */
    protected function validateAccess(): void
    {
        if (!$this->authenticator->checkAccess('clear_cache')) {
            throw new ForbiddenException();
        }
    }
}
