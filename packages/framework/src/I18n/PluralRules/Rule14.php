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
 * Families: Slavic (Macedonian)
 * 1 - ends in 1: 1, 11, 21, ...
 * 2 - ends in 2: 2, 12, 22, ...
 * 3 - everything else: 0, 3, 4, ... 10, 13, 14, ... 20, 23, ...
 *
 * @see https://developer.mozilla.org/en-US/docs/Mozilla/Localization/Localization_and_Plurals#Plural_rule_14_(3_forms)
 */
final class Rule14 implements RuleInterface
{
    public static function selectPluralForm(int $number): int
    {
        if ($number % 10 === 1) {
            return 1;
        }

        if ($number % 10 === 2) {
            return 2;
        }

        return 3;
    }
}
