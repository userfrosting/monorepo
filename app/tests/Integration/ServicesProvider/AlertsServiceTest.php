<?php

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\Integration\ServicesProvider;

use UserFrosting\Sprinkle\Account\Authenticate\AuthGuard;
use UserFrosting\Tests\TestCase;

/**
 * Integration tests for `authGuard` service.
 * Check to see if service returns what it's supposed to return
 */
class AlertsServiceTest extends TestCase
{
    public function testService()
    {
        $this->assertInstanceOf(AuthGuard::class, $this->ci->authGuard);
    }
}
