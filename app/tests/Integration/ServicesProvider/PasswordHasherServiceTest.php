<?php

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\Integration\ServicesProvider;

use UserFrosting\Sprinkle\Account\Authenticate\Hasher;
use UserFrosting\Tests\TestCase;

/**
 * Integration tests for `passwordHasher` service.
 * Check to see if service returns what it's supposed to return
 */
class PasswordHasherServiceTest extends TestCase
{
    public function testService()
    {
        $this->assertInstanceOf(Hasher::class, $this->ci->passwordHasher);
    }
}
