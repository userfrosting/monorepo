<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Util;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Static utility functions.
 */
class Util
{
    protected static string $adjectivesFile = 'extra://adjectives.php';
    protected static string $nounsFile = 'extra://nouns.php';

    /**
     * Extracts specific fields from one associative array, and places them into another.
     *
     * @param mixed[]  $inputArray
     * @param string[] $fieldArray
     * @param bool     $remove
     *
     * @return mixed[]
     */
    public static function extractFields(
        array &$inputArray,
        array $fieldArray,
        bool $remove = true
    ): array {
        $result = [];

        foreach ($fieldArray as $name) {
            if (array_key_exists($name, $inputArray)) {
                $result[$name] = $inputArray[$name];

                // Optionally remove value from input array
                if ($remove) {
                    unset($inputArray[$name]);
                }
            }
        }

        return $result;
    }

    /**
     * Extracts numeric portion of a string (for example, for normalizing phone numbers).
     *
     * @param string $str
     *
     * @return string
     */
    public static function extractDigits(string $str): string
    {
        /** @var string */
        return preg_replace('/[^0-9]/', '', $str);
    }

    /**
     * Formats a phone number as a standard 7- or 10-digit string (xxx) xxx-xxxx.
     *
     * @param string $phone
     *
     * @return string
     */
    public static function formatPhoneNumber(string $phone): string
    {
        $num = static::extractDigits($phone);

        $len = strlen($num);

        if ($len === 7) {
            $num = preg_replace('/([0-9]{3})([0-9]{4})/', '$1-$2', $num) ?? $num;
        } elseif ($len === 10) {
            $num = preg_replace('/([0-9]{3})([0-9]{3})([0-9]{4})/', '($1) $2-$3', $num) ?? $num;
        }

        return $num;
    }

    /**
     * Nicely format an array for printing.
     * See https://stackoverflow.com/a/9776726/2970321.
     *
     * @param mixed[] $arr
     *
     * @return string
     */
    public static function prettyPrintArray(array $arr): string
    {
        $json = json_encode($arr, JSON_THROW_ON_ERROR);
        $result = '';
        $level = 0;
        $inQuotes = false;
        $inEscape = false;
        $endsLineLevel = null;
        $jsonLength = strlen($json);

        for ($i = 0; $i < $jsonLength; $i++) {
            $char = $json[$i];
            $newLineLevel = null;
            $post = '';
            if ($endsLineLevel !== null) {
                $newLineLevel = $endsLineLevel;
                $endsLineLevel = null;
            }
            if ($inEscape) {
                $inEscape = false;
            } elseif ($char === '"') {
                $inQuotes = !$inQuotes;
            } elseif (!$inQuotes) {
                switch ($char) {
                    case '}':
                    case ']':
                        $level--;
                        $endsLineLevel = null;
                        $newLineLevel = $level;
                        break;

                    case '{':
                    case '[':
                        $level++;

                        // no break
                    case ',':
                        $endsLineLevel = $level;
                        break;

                    case ':':
                        $post = ' ';
                        break;

                        // case ' ':
                        // case '\t':
                        // case '\n':
                        // case '\r':
                        //     $char = '';
                        //     $endsLineLevel = $newLineLevel;
                        //     $newLineLevel = null;
                        //     break;
                }
            } elseif ($char === '\\') {
                $inEscape = true;
            }

            if ($newLineLevel !== null) {
                $result .= '<br>' . str_repeat('&nbsp;&nbsp;', $newLineLevel);
            }

            $result .= $char . $post;
        }

        return $result;
    }

    /**
     * Generate a random phrase, consisting of a specified number of adjectives, followed by a noun.
     *
     * @param int    $numAdjectives
     * @param int    $maxLength
     * @param int    $maxTries
     * @param string $separator
     *
     * @return string
     */
    public static function randomPhrase(
        int $numAdjectives,
        int $maxLength = 9999999,
        int $maxTries = 10,
        string $separator = '-'
    ): string {
        $adjectives = include self::$adjectivesFile;
        $nouns = include self::$nounsFile;

        for ($n = 0; $n < $maxTries; $n++) {
            $keys = array_rand($adjectives, $numAdjectives);
            $keys = is_array($keys) ? $keys : [$keys];
            $matches = Arr::only($adjectives, $keys);

            $result = implode($separator, $matches);
            $result .= $separator . $nouns[array_rand($nouns)];
            $result = Str::slug($result, $separator);
            if (strlen($result) < $maxLength) {
                return $result;
            }
        }

        return '';
    }
}
