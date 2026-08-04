<?php

declare(strict_types=1);

/*
 * UserFrosting Framework (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/framework
 * @copyright Copyright (c) 2013-2024 Alexander Weissman, Louis Charette, Jordan Mele
 * @license   https://github.com/userfrosting/framework/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Support\Util;

/**
 * Static utility functions for UserFrosting.
 */
class Util
{
    /**
     * Removes a prefix from the beginning of a string, if a match is found.
     *
     * @param string $str    The string to process.
     * @param string $prefix The prefix to find and remove.
     *
     * @return string
     */
    public static function stripPrefix(string $str, string $prefix = ''): string
    {
        // if string is same as prefix, return empty string
        if ($str === $prefix) {
            return '';
        }

        if (substr($str, 0, strlen($prefix)) === $prefix) {
            $str = substr($str, strlen($prefix));
        }

        return $str;
    }

    /**
     * Determine if a given string matches one or more regular expressions.
     *
     * @param string|string[] $patterns
     * @param string          $subject
     * @param mixed[]|null    $matches
     * @param string          $delimiter
     * @param 0|256|512|768   $flags
     * @param int             $offset
     *
     * @return bool
     */
    public static function stringMatches(
        string|array $patterns,
        string $subject,
        ?array &$matches = null,
        string $delimiter = '~',
        int $flags = 0,
        int $offset = 0
    ): bool {
        $matches = [];
        $result = false;
        foreach ((array) $patterns as $pattern) {
            $currMatches = [];
            if ($pattern !== '' && preg_match($delimiter . $pattern . $delimiter, $subject, $currMatches, $flags, $offset) === 1) {
                $result = true;
                $matches[$pattern] = $currMatches;
            }
        }

        return $result;
    }

    /**
     * Recursively apply a callback to members of an array.
     *
     * @param mixed[]       $input
     * @param callable|null $callback
     *
     * @return mixed[]
     */
    public static function arrayFilterRecursive(array $input, $callback = null): array
    {
        foreach ($input as &$value) {
            if (is_array($value)) {
                $value = self::arrayFilterRecursive($value, $callback);
            }
        }

        return array_filter($input, $callback);
    }
}
