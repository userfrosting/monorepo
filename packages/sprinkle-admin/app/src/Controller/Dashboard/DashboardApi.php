<?php

declare(strict_types=1);

/*
 * UserFrosting Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-admin
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Admin\Controller\Dashboard;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\GroupInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\RoleInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Exceptions\ForbiddenException;

/**
 * Api for /dashboard URL. Handles admin-related activities.
 */
class DashboardApi
{
    /**
     * Inject dependencies.
     */
    public function __construct(
        protected Authenticator $authenticator,
        protected Config $config,
        protected UserInterface $userModel,
        protected RoleInterface $roleModel,
        protected GroupInterface $groupModel,
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
        return [
            'counter'   => [
                'users'  => $this->userModel::count(),
                'roles'  => $this->roleModel::count(),
                'groups' => $this->groupModel::count(),
            ],
            'users'     => $this->getLatestUsers(),
        ];
    }

    /**
     * Get the latest users.
     *
     * @return UserInterface[]
     */
    protected function getLatestUsers(): array
    {
        // Probably a better way to do this
        // TODO : User config for number of users to display
        $users = $this->userModel::orderBy('created_at', 'desc')
                 ->take(8)
                 ->get();

        return $users->toArray();
    }
}
