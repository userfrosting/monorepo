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
 * Families: Icelandic
 * 1 - ends in 1, not 11: 1, 21, 31, ... 101, 121, 131, ...
 * 2 - everything else: 0, 2, 3, ... 10, 11, 12, ... 20, 22, ...
 *
 * @see https://developer.mozilla.org/en-US/docs/Mozilla/Localization/Localization_and_Plurals#Plural_rule_15_(2_forms)
 */
final class Rule15 implements RuleInterface
{
    public static function selectPluralForm(int $number): int
    {
        if (($number % 10 === 1) && ($number % 100 !== 11)) {
            return 1;
        }

        return 2;
    }
}
