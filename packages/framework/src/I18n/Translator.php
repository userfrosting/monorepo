<?php

declare(strict_types=1);

/*
 * UserFrosting Framework (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/framework
 * @copyright Copyright (c) 2013-2024 Alexander Weissman, Louis Charette, Jordan Mele
 * @license   https://github.com/userfrosting/framework/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\I18n;

use InvalidArgumentException;
use OutOfRangeException;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * Translate message ids to a message in a specified language.
 */
class Translator
{
    /**
     * @var Environment A Twig environment used to replace placeholders.
     */
    protected Environment $twig;

    /**
     * @var string The default key that contains the pluralization code.
     */
    protected string $defaultPluralKey = 'plural';

    /**
     * @var array<int,class-string> Rules class list.
     */
    protected array $rulesClass = [
        0  => \UserFrosting\I18n\PluralRules\Rule0::class,
        1  => \UserFrosting\I18n\PluralRules\Rule1::class,
        2  => \UserFrosting\I18n\PluralRules\Rule2::class,
        3  => \UserFrosting\I18n\PluralRules\Rule3::class,
        4  => \UserFrosting\I18n\PluralRules\Rule4::class,
        5  => \UserFrosting\I18n\PluralRules\Rule5::class,
        6  => \UserFrosting\I18n\PluralRules\Rule6::class,
        7  => \UserFrosting\I18n\PluralRules\Rule7::class,
        8  => \UserFrosting\I18n\PluralRules\Rule8::class,
        9  => \UserFrosting\I18n\PluralRules\Rule9::class,
        10 => \UserFrosting\I18n\PluralRules\Rule10::class,
        11 => \UserFrosting\I18n\PluralRules\Rule11::class,
        12 => \UserFrosting\I18n\PluralRules\Rule12::class,
        13 => \UserFrosting\I18n\PluralRules\Rule13::class,
        14 => \UserFrosting\I18n\PluralRules\Rule14::class,
        15 => \UserFrosting\I18n\PluralRules\Rule15::class,
    ];

    /**
     * Create the translator.
     *
     * @param DictionaryInterface $dictionary
     */
    public function __construct(protected DictionaryInterface $dictionary)
    {
        // Make sure dictionary is loaded
        $this->dictionary->getDictionary();

        // Prepare Twig Environment
        $loader = new FilesystemLoader();
        $this->twig = new Environment($loader);
    }

    /**
     * Returned the associated dictionary.
     *
     * @return DictionaryInterface
     */
    public function getDictionary(): DictionaryInterface
    {
        return $this->dictionary;
    }

    /**
     * Returns the associated locale for the specified dictionary.
     *
     * @return LocaleInterface
     */
    public function getLocale(): LocaleInterface
    {
        return $this->dictionary->getLocale();
    }

    /**
     * Translate the given message id into the currently configured language, substituting any placeholders that appear in the translated string.
     *
     * Return the $messageKey if not match is found
     *
     * @param string      $messageKey   The id of the message id to translate. can use dot notation for array
     * @param mixed[]|int $placeholders An optional hash of placeholder names => placeholder values to substitute (default : [])
     *
     * @return string The translated message.
     */
    public function translate(string $messageKey, array|int $placeholders = []): string
    {
        // Get the correct message from the specified key
        $message = $this->getMessageFromKey($messageKey, $placeholders);

        // Parse Placeholders
        $message = $this->parsePlaceHolders($message, $placeholders);

        return $message;
    }

    /**
     * Get the message from key.
     * Go through all registered language keys available and find the correct
     * one to use, using the placeholders to select the correct plural form.
     *
     * @param string      $messageKey   The key to find the message for
     * @param mixed[]|int $placeholders Passed by reference, since plural placeholder will be added for later processing
     *
     * @return string The message string
     */
    protected function getMessageFromKey(string $messageKey, array|int &$placeholders): string
    {
        // If we can't find a match, return the key
        if (!$this->dictionary->has($messageKey)) {
            return $messageKey;
        }

        // Get message from items
        $message = $this->dictionary->get($messageKey);

        // If message is not string/array, abort
        if (!is_array($message) && !is_string($message)) {
            throw new InvalidArgumentException("Message for key `$messageKey` must be string or array.");
        }

        // If message is an array, we'll need to go deeper to get the actual string. Otherwise we're good to move on.
        if (!is_array($message)) {
            return $message;
        }

        // First, let's see if we can get the plural rules.
        // A plural form will always have priority over the `@TRANSLATION` instruction
        if (count(array_filter(array_keys($message), 'is_int')) !== 0) {
            // We start by picking up the plural key, aka which placeholder contains the numeric value defining how many {x} we have
            $pluralKey = $this->getPluralKey($message);

            // Let's get the plural value, aka how many {x} we have
            $pluralValue = $this->getPluralValue($placeholders, $pluralKey);

            // If no plural value was found, we either use the singular form or fallback to `@TRANSLATION` instruction
            if (is_null($pluralValue)) {
                // If we have a `@TRANSLATION` instruction, return this
                if ($this->dictionary->has($messageKey . '.@TRANSLATION') && is_string($this->dictionary->get($messageKey . '.@TRANSLATION'))) {
                    return $this->dictionary->get($messageKey . '.@TRANSLATION');
                }

                // Otherwise fallback to singular version
                $pluralValue = 1;
            }

            // If $placeholders is a numeric value, we transform back to an array for replacement in the main $message
            if (is_numeric($placeholders) || count($placeholders) === 0) {
                $placeholders = [$pluralKey => $pluralValue];
            }

            // At this point, we need to go deeper and find the correct plural form to use
            $plural = $this->getPluralMessageKey($message, $pluralValue);

            // Only return if the plural is not null. Will happen if the message array don't follow the rules
            if (!is_null($plural)) {
                return $message[$plural];
            }

            // One last check... If we don't have a rule, but the $pluralValue
            // as a key does exist, we might still be able to return it
            if (isset($message[$pluralValue])) {
                return $message[$pluralValue];
            }
        }

        // If we didn't find a plural form, we try to find the "@TRANSLATION" form.
        if ($this->dictionary->has($messageKey . '.@TRANSLATION') && is_string($this->dictionary->get($messageKey . '.@TRANSLATION'))) {
            return $this->dictionary->get($messageKey . '.@TRANSLATION');
        }

        // If the message is an array, but we can't find a plural form or a "@TRANSLATION" instruction, we can't go further.
        // We can't return the array, so we'll return the key
        return $messageKey;
    }

    /**
     * Return the plural key from a translation array.
     * If no plural key is defined in the `@PLURAL` instruction of the message array, we fallback to the default one.
     *
     * @param mixed[] $messageArray
     *
     * @return string
     */
    protected function getPluralKey(array $messageArray): string
    {
        if (isset($messageArray['@PLURAL']) && is_string($messageArray['@PLURAL'])) {
            return $messageArray['@PLURAL'];
        } else {
            return $this->defaultPluralKey;
        }
    }

    /**
     * Return the plural value, aka the number to display, from the placeholder values.
     *
     * @param mixed[]|int $placeholders Placeholder
     * @param string      $pluralKey    The plural key, for key => value match
     *
     * @return int|null The number, null if not found
     */
    protected function getPluralValue(array|int $placeholders, string $pluralKey): ?int
    {
        if (is_array($placeholders) && isset($placeholders[$pluralKey])) {
            return (int) $placeholders[$pluralKey];
        }

        if (!is_array($placeholders)) {
            return $placeholders;
        }

        // Null will be returned
        return null;
    }

    /**
     * Return the correct plural message form to use.
     * When multiple plural form are available for a message, this method will return the correct one to use based on the numeric value.
     *
     * @param mixed[] $messageArray The array with all the form inside ($pluralRule => $message)
     * @param int     $pluralValue  The numeric value used to select the correct message
     *
     * @return int|null Returns which key from $messageArray to use
     */
    protected function getPluralMessageKey(array $messageArray, int $pluralValue): ?int
    {
        // Bypass the rules for a value of "0". Instead of returning the correct
        // plural form (>= 1), we force return the "0" form, which can used to
        // display "0 users" as "No users".
        if ($pluralValue === 0 && isset($messageArray[0])) {
            return 0;
        }

        // Get the correct plural form to use depending on the language
        $usePluralForm = $this->getPluralForm($pluralValue);

        // If the message array contains a string for this form, return it
        if (isset($messageArray[$usePluralForm])) {
            return $usePluralForm;
        }

        // If the key we need doesn't exist, use the previous available one, including the special "0" form
        // This is a fallback to avoid errors when the dictionary is not complete
        $numbers = array_keys($messageArray);
        foreach (array_reverse($numbers) as $num) {
            if (is_int($num) && $num > $usePluralForm) {
                break;
            }

            return $num;
        }

        // If no key was found, null will be returned
        return null;
    }

    /**
     * Parse Placeholder.
     * Replace placeholders in the message with their values from the passed argument.
     *
     * @param string      $message      The message to replace placeholders in
     * @param mixed[]|int $placeholders An optional hash of placeholder (names => placeholder) values to substitute (default : [])
     *
     * @return string The message with replaced placeholders
     */
    protected function parsePlaceHolders(string $message, $placeholders): string
    {
        // If $placeholders is not an array at this point, we make it an array, using `plural` as the key
        if (!is_array($placeholders)) {
            $placeholders = [$this->defaultPluralKey => $placeholders];
        }

        // Interpolate translatable placeholders values. This allows to
        // pre-translate placeholder which value starts with the `&` character
        foreach ($placeholders as $name => $value) {
            //We don't allow nested placeholders. They will return errors on the next lines
            if (!is_string($value)) {
                continue;
            }

            // We test if the placeholder value starts the "&" character.
            // That means we need to translate that placeholder value
            if (substr($value, 0, 1) === '&') {
                // Remove the current placeholder from the master $placeholder
                // array, otherwise we end up in an infinite loop
                $data = array_diff($placeholders, [$name => $value]);

                // Translate placeholders value and place it in the main $placeholder array
                $placeholders[$name] = $this->translate(ltrim($value, '&'), $data);
            }
        }

        // We check for {{&...}} strings in the resulting message.
        // While the previous loop pre-translated placeholder value, this one
        // pre-translate the message string vars
        // We use some regex magic to detect them !
        $message = preg_replace_callback('/{{&(([^}]+[^a-z]))}}/', function ($matches) use ($placeholders) {
            return $this->translate($matches[1], $placeholders);
        }, $message) ?? $message;

        // Now it's time to replace the remaining placeholder. We use Twig do to this.
        // It's a bit slower, but allows to use the many Twig filters
        // See: http://twig.sensiolabs.org/doc/2.x/
        $template = $this->twig->createTemplate($message);
        $message = $template->render($placeholders);

        return $message;
    }

    /**
     * Determine which plural form we should use.
     * For some languages this is not as simple as for English.
     *
     * @see https://developer.mozilla.org/en-US/docs/Mozilla/Localization/Localization_and_Plurals
     *
     * @param int|float $number    The number we want to get the plural form of. Float numbers are floored.
     * @param int|null  $forceRule Used to force a certain plural rule. Null (default) to use the plural rule of the language package.
     *
     * @return int The plural form we need to use for the number & plural rule combination
     */
    public function getPluralForm(int|float $number, ?int $forceRule = null): int
    {
        $rule = $forceRule ?? $this->dictionary->getLocale()->getPluralRule();

        // Get the rule class
        if (!array_key_exists($rule, $this->rulesClass)) {
            throw new OutOfRangeException("The rule number '$rule' must be between 0 and 15.");
        }

        return $this->rulesClass[$rule]::selectPluralForm((int) $number);
    }
}
