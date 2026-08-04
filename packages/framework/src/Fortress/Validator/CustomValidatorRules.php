<?php

declare(strict_types=1);

/*
 * UserFrosting Framework (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/framework
 * @copyright Copyright (c) 2013-2024 Alexander Weissman, Louis Charette, Jordan Mele
 * @license   https://github.com/userfrosting/framework/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Fortress\Validator;

/**
 * Custom validator methods for Valitron\Validator.
 */
class CustomValidatorRules
{
    /**
     * Validate that a field has a particular value.
     *
     * @param string               $field
     * @param mixed                $value
     * @param array{string, mixed} $params
     *
     * @return bool
     */
    public static function validateEqualsValue(string $field, mixed $value, array $params): bool
    {
        $targetValue = $params[0];
        $caseSensitive = is_bool($params[1]) ? $params[1] : false;

        if (is_string($value) && !$caseSensitive) {
            $value = strtolower($value);
            $targetValue = strtolower($targetValue);
        }

        return $value === $targetValue;
    }

    /**
     * Validate that a field does NOT have a particular value.
     *
     * @param string               $field
     * @param mixed                $value
     * @param array{string, mixed} $params
     *
     * @return bool
     */
    public static function validateNotEqualsValue(string $field, mixed $value, array $params): bool
    {
        return !self::validateEqualsValue($field, $value, $params);
    }

    /**
     * Matches US phone number format
     * Ported from jqueryValidation rules.
     *
     * where the area code may not start with 1 and the prefix may not start with 1
     * allows '-' or ' ' as a separator and allows parens around area code
     * some people may want to put a '1' in front of their number
     *
     * 1(212)-999-2345 or
     * 212 999 2344 or
     * 212-999-0983
     *
     * but not
     * 111-123-5434
     * and not
     * 212 123 4567
     *
     * @param string $field
     * @param mixed  $value
     *
     * @return bool
     */
    public static function validatePhoneUS(string $field, mixed $value): bool
    {
        $value = preg_replace('/\s+/', '', $value);

        return (strlen($value) > 9) &&
            preg_match('/^(\+?1-?)?(\([2-9]([02-9]\d|1[02-9])\)|[2-9]([02-9]\d|1[02-9]))-?[2-9]([02-9]\d|1[02-9])-?\d{4}$/', $value) === 1;
    }

    /**
     * Validate that a field contains only valid username characters:
     * alpha-numeric characters, dots, dashes, and underscores.
     *
     * @param string $field
     * @param mixed  $value
     *
     * @return bool
     */
    public static function validateUsername(string $field, mixed $value): bool
    {
        return preg_match('/^([a-z0-9\.\-_])+$/i', strval($value)) === 1;
    }
}
