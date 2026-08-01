<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Twig;

use Exception;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFunction;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;

/**
 * Extends Twig functionality for the Account sprinkle.
 */
class AccountExtension extends AbstractExtension implements GlobalsInterface
{
    /**
     * @param Authenticator $authenticator
     */
    public function __construct(
        protected Authenticator $authenticator,
    ) {
    }

    /**
     * Adds Twig functions.
     *
     * @return TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            // Add Twig function for checking permissions during dynamic menu rendering
            new TwigFunction('checkAccess', function (string $slug, array $params = []) {
                return $this->authenticator->checkAccess($slug, $params);
            }),
            new TwigFunction('checkAuthenticated', function () {
                return $this->authenticator->check();
            }),
        ];
    }

    /**
     * Adds Twig global variables `site`.
     *
     * @return array<string, mixed>
     */
    public function getGlobals(): array
    {
        try {
            $currentUser = $this->authenticator->user();
        } catch (Exception $e) {
            $currentUser = null;
        }

        return [
            'current_user' => $currentUser,
        ];
    }
}
