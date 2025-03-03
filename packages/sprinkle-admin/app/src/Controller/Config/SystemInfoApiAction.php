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
use Illuminate\Database\Connection;
use PDO;
use PDOException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Exceptions\ForbiddenException;
use UserFrosting\Sprinkle\SprinkleManager;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

/**
 * Api for /dashboard URL. Handles admin-related activities.
 */
class SystemInfoApiAction
{
    /**
     * Inject dependencies.
     */
    public function __construct(
        protected Authenticator $authenticator,
        protected Cache $cache,
        protected Config $config,
        protected Connection $dbConnection,
        protected SprinkleManager $sprinkleManager,
        protected ResourceLocatorInterface $locator,
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
        $payload = $this->handle($request);

        // Write json response
        $payload = json_encode($payload, JSON_THROW_ON_ERROR);
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
        // TODO : Create a dedicated permission for this
        if (!$this->authenticator->checkAccess('uri_dashboard')) {
            throw new ForbiddenException();
        }
    }

    /**
     * Handle the request and return the payload.
     *
     * @param Request $request
     *
     * @return mixed[]
     */
    protected function handle(Request $request): array
    {
        return $this->cache->rememberForever('system_info', function () {
            return [
                'frameworkVersion' => (string) \Composer\InstalledVersions::getPrettyVersion('userfrosting/framework'),
                'phpVersion'       => phpversion(),
                'database'         => $this->getDatabaseInfo(),
                'server'           => $_SERVER['SERVER_SOFTWARE'] ?? '',
                'projectPath'      => $this->locator->getBasePath(),
                'sprinkles'        => $this->sprinkleManager->getSprinklesNames(),
            ];
        });
    }

    /**
     * Returns database information.
     *
     * @return array{connection: string, name: string, type: string, version: string}
     */
    protected function getDatabaseInfo(): array
    {
        $database = $this->config->getString('db.default', '');
        $pdo = $this->dbConnection->getPdo();
        $results = [
            'connection' => $database,
            'name'       => $this->dbConnection->getDatabaseName(),
        ];

        try {
            $results['type'] = strval($pdo->getAttribute(PDO::ATTR_DRIVER_NAME));
        } catch (PDOException $e) {
            $results['type'] = 'Unknown';
        }

        try {
            $results['version'] = strval($pdo->getAttribute(PDO::ATTR_SERVER_VERSION));
        } catch (PDOException $e) {
            $results['version'] = '';
        }

        return $results;
    }
}
