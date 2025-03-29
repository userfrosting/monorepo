<?php

declare(strict_types=1);

/*
 * UserFrosting Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-admin
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Admin\Controller\User;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UserFrosting\I18n\Locale;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Exceptions\ForbiddenException;
use UserFrosting\Sprinkle\Core\I18n\SiteLocaleInterface;

/**
 * Renders a page displaying a user's information, in read-only mode.
 *
 * This checks that the currently logged-in user has permission to view the requested user's info.
 * It checks each field individually, showing only those that you have permission to view.
 * This will also try to show buttons for activating, disabling/enabling, deleting, and editing the user.
 *
 * This page requires authentication.
 * Request type: GET
 */
// TODO : Eventually this class could be moved to the Account sprinkle.
class UserApi
{
    /**
     * Inject dependencies.
     */
    public function __construct(
        protected Authenticator $authenticator,
        protected SiteLocaleInterface $siteLocale,
    ) {
    }

    /**
     * Receive the request, dispatch to the handler, and return the payload to
     * the response.
     *
     * @param UserInterface $user     The user to display, injected by the middleware.
     * @param Response      $response
     */
    public function __invoke(UserInterface $user, Response $response): Response
    {
        $this->validateAccess($user);
        $user = $this->handle($user);
        $payload = json_encode($user, JSON_THROW_ON_ERROR);
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Validate access to the api.
     *
     * @throws ForbiddenException
     */
    protected function validateAccess(UserInterface $user): void
    {
        // Has access to all users
        if ($this->authenticator->checkAccess('uri_user')) {
            return;
        }

        // The user can view itself
        if ($this->authenticator->user()?->id === $user->id) {
            return;
        }

        // The user can view users in the same group
        if ($this->authenticator->checkAccess('uri_user_in_group')
            && $this->authenticator->user()?->group_id === $user->group_id) {
            return;
        }

        throw new ForbiddenException();
    }

    /**
     *  Add or remove fields from the user object before returning it.
     * TIP : When extending this class, you can use this method to add your own fields.
     *
     * @param UserInterface $user
     *
     * @return UserInterface
     */
    protected function handle(UserInterface $user): UserInterface
    {
        // Add the locale name to the user object
        $user->locale_name = (new Locale($user->locale))->getName();

        // Add the group object to the user object
        $user->load('group');

        return $user;
    }
}
