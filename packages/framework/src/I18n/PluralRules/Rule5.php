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
 * Families: Romanic (Romanian)
 * 1 - 1
 * 2 - is 0 or ends in 01-19: 0, 2, 3, ... 19, 101, 102, ... 119, 201, ...
 * 3 - everything else: 20, 21, ...
 *
 * @see https://developer.mozilla.org/en-US/docs/Mozilla/Localization/Localization_and_Plurals#Plural_rule_5_(3_forms)
 */
final class Rule5 implements RuleInterface
{
    public static function selectPluralForm(int $number): int
    {
        if ($number === 1) {
            return 1;
        }

        if ($number === 0 || (($number % 100 > 0) && ($number % 100 < 20))) {
            return 2;
        }

        return 3;
    }
}
