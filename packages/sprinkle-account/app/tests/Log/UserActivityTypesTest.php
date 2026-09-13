<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\Repository;

use PHPUnit\Framework\TestCase;
use UserFrosting\Sprinkle\Account\Log\UserActivityTypes;

final class UserActivityTypesTest extends TestCase
{
    public function testCasesExposeExpectedValues(): void
    {
        $this->assertSame([
            'REGISTER'          => 'sign_up',
            'VERIFIED'          => 'verified',
            'PASSWORD_RESET'    => 'password_reset',
            'LOGGED_IN'         => 'sign_in',
            'LOGGED_OUT'        => 'sign_out',
            'PASSWORD_UPGRADED' => 'password_upgraded',
        ], array_column(UserActivityTypes::cases(), 'value', 'name'));
    }
}
