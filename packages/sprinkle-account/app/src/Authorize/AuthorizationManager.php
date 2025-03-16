<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Authorize;

use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Log\AuthLoggerInterface;

/**
 * Manages a collection of access condition callbacks, and uses them to perform
 * access control checks on user objects.
 */
class AuthorizationManager implements AuthorizationManagerInterface
{
    /**
     * Create a new AuthorizationManager object.
     *
     * @param Config                   $config
     * @param AuthLoggerInterface      $logger
     * @param AccessConditionEvaluator $ace
     */
    public function __construct(
        protected Config $config,
        protected AuthLoggerInterface $logger,
        protected AccessConditionEvaluator $ace,
    ) {
    }

    /**
     * Checks whether or not a user has access on a particular permission slug.
     *
     * Determine if this user has access to the given $slug under the given $params.
     *
     * @param UserInterface|null $user
     * @param string             $slug   The permission slug to check for access.
     * @param mixed[]            $params An array of field names => values, specifying any additional data to provide the authorization module
     *                                   when determining whether or not this user has access.
     *
     * @return bool True if the user has access, false otherwise.
     */
    public function checkAccess(?UserInterface $user, string $slug, array $params = []): bool
    {
        // Trace debug information
        $trace = array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3), 1);
        $this->debugAuth('Authorization check requested at: ', $trace);

        // Deny access if no user is defined.
        if ($user === null) {
            $this->debugAuth('No user defined. Access denied.');

            return false;
        }

        $this->debugAuth("Checking authorization for user {$user->id} ('{$user->user_name}') on permission '$slug'...");

        // The master (root) account has access to everything.
        // Need to use loose comparison for now, because some DBs return `id` as a string.
        if ($user->id === $this->config->getInt('reserved_user_ids.master')) {
            $this->debugAuth('User is the master (root) user. Access granted.');

            return true;
        }

        // Find all permissions that apply to this user (via roles), and check if any evaluate to true.
        $permissions = $user->getCachedPermissions();
        if (count($permissions) === 0 || !isset($permissions[$slug])) {
            $this->debugAuth('No permissions found. Access denied.');

            return false;
        }

        // Find matching permission conditions
        $conditions = $permissions[$slug];
        $this->debugAuth("Found matching permissions conditions: \n" . print_r($conditions, true));
        foreach ($conditions as $condition) {
            $pass = $this->ace->evaluate($condition, $params, $user);
            if ($pass) {
                $this->debugAuth("User passed conditions '{$condition}'. Access granted.");

                return true;
            }
        }

        $this->debugAuth('User failed to pass any of the matched permissions. Access denied.');

        return false;
    }

    /**
     * Send a debug message to the logger if debug.auth is enabled.
     *
     * @param string  $message
     * @param mixed[] $payload
     */
    protected function debugAuth(string $message, array $payload = []): void
    {
        if ($this->config->getBool('debug.auth', false)) {
            $this->logger->debug($message, $payload);
        }
    }
}
