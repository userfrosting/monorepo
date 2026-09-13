<?php

declare(strict_types=1);

/*
 * UserFrosting Framework (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/framework
 * @copyright Copyright (c) 2013-2024 Alexander Weissman, Louis Charette, Jordan Mele
 * @license   https://github.com/userfrosting/framework/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests\Support\Util;

use PHPUnit\Framework\TestCase;
use UserFrosting\Support\Util\Util;

class UtilTest extends TestCase
{
    public function testStringMatchesSuccess(): void
    {
        $str = 'assets-raw/admin/assets/local/widgets/js/users.js';

        $patterns = [
            '^assets-raw',
            '^assets-raw/(.*)',
            '^api/owls',
            '^test/assets-raw',
        ];

        $matches = [];
        $this->assertTrue(Util::stringMatches($patterns, $str, $matches));

        $this->assertEquals([
            '^assets-raw' => [
                'assets-raw',
            ],
            '^assets-raw/(.*)' => [
                'assets-raw/admin/assets/local/widgets/js/users.js',
                'admin/assets/local/widgets/js/users.js',
            ],
        ], $matches);
    }

    public function testStringMatchesFail(): void
    {
        $str = 'admin/owls/voles';

        $patterns = [
            '^assets-raw',
            '^owls',
            '^api/owls',
        ];

        $this->assertFalse(Util::stringMatches($patterns, $str));
    }

    public function testArrayFilterRecursiveUsesDefaultCallback(): void
    {
        $this->assertSame([
            'nested' => [
                'value' => 'kept',
            ],
        ], Util::arrayFilterRecursive([
            'zero'   => 0,
            'empty'  => '',
            'nested' => [
                'value' => 'kept',
                'false' => false,
            ],
        ]));
    }

    /**
     * @param string $prefix
     * @param string $expectedResult
     *
     * @testWith ["", "owls::voles"]
     *           ["::", "owls::voles"]
     *           ["owls", "::voles"]
     *           ["owls::", "voles"]
     *           ["owls::voles", ""]
     */
    public function testStripPrefix($prefix, $expectedResult): void
    {
        $str = 'owls::voles';
        $this->assertSame($expectedResult, Util::stripPrefix($str, $prefix));
    }
}
