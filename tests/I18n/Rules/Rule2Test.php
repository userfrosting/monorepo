<?php

declare(strict_types=1);

/*
 * UserFrosting Framework (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/framework
 * @copyright Copyright (c) 2013-2024 Alexander Weissman, Louis Charette, Jordan Mele
 * @license   https://github.com/userfrosting/framework/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests\I18n\Rules;

use UserFrosting\I18n\PluralRules\Rule2;

class Rule2Test extends RuleBase
{
    protected string $ruleToTest = Rule2::class;

    /**
     * Families: Romanic (French, Brazilian Portuguese)
     * 1 - 0, 1
     * 2 - everything else: 2, 3, ...
     *
     * {@inheritDoc}
     */
    public static function ruleProvider(): array
    {
        return [
            [0, 1],
            [1, 1],
            [2, 2],
            [-2, 2],
            [128, 2],
        ];
    }
}
