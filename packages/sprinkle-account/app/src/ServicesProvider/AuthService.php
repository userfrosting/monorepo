<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\ServicesProvider;

use mober\Rememberme\Storage\AbstractStorage;
use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Authenticate\Hasher;
use UserFrosting\Sprinkle\Account\Authenticate\Interfaces\HasherInterface;
use UserFrosting\Sprinkle\Account\Rememberme\PDOStorage;

/**
 * Authenticator related Service.
 */
class AuthService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            AbstractStorage::class => \DI\autowire(PDOStorage::class),
            HasherInterface::class => \DI\autowire(Hasher::class),
        ];
    }
}
