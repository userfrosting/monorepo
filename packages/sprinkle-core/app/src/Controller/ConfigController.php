<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use UserFrosting\Config\Config;
use UserFrosting\I18n\Translator;
use UserFrosting\Sprinkle\Core\I18n\SiteLocaleInterface;

/**
 * Return the config variables to use in the frontend.
 *
 * Middleware: none
 * Route: /api/config
 * Route Name: api.config
 * Request type: GET
 */
class ConfigController
{
    /**
     * @param Config              $config
     * @param SiteLocaleInterface $locale
     * @param Translator          $translator
     */
    public function __construct(
        protected Config $config,
        protected SiteLocaleInterface $locale,
        protected Translator $translator
    ) {
    }

    /**
     * @param Response $response
     */
    public function __invoke(Response $response): Response
    {
        $payload = json_encode($this->getData(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
        $response->getBody()->write($payload);

        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    /**
     * @return mixed[]
     */
    public function getData(): array
    {
        $data = [
            'site'    => $this->config->get('site'),
            'locales' => [
                'available' => $this->locale->getAvailableOptions(),
                'current'   => $this->translator->getLocale()->getIdentifier(),
            ],
            'csrf'    => [
                'enabled' => $this->config->getBool('csrf.enabled'),
                'name'    => $this->config->getString('csrf.name'),
            ],
        ];

        return $data;
    }
}
