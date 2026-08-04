<?php

declare(strict_types=1);

/*
 * UserFrosting Framework (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/framework
 * @copyright Copyright (c) 2013-2024 Alexander Weissman, Louis Charette, Jordan Mele
 * @license   https://github.com/userfrosting/framework/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\I18n\PluralRules;

/**
 * Families: Celtic (Scottish Gaelic)
 * 1 - is 1 or 11: 1, 11
 * 2 - is 2 or 12: 2, 12
 * 3 - others between 3 and 19: 3, 4, ... 10, 13, ... 18, 19
 * 4 - everything else: 0, 20, 21, ...
 *
 * @see https://developer.mozilla.org/en-US/docs/Mozilla/Localization/Localization_and_Plurals#Plural_rule_4_(4_forms)
 */
final class Rule4 implements RuleInterface
{
    public static function selectPluralForm(int $number): int
    {
        if ($number === 1 || $number === 11) {
            return 1;
        }

        if ($number === 2 || $number === 12) {
            return 2;
        }

        if ($number >= 3 && $number <= 19) {
            return 3;
        }

        return 4;
    }
}
