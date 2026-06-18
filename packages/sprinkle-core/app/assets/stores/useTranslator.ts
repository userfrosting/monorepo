/**
 * Translator composable store.
 *
 * This pinia store is used to access the translator and to use the translator.
 */
import { ref } from 'vue'
import { defineStore } from 'pinia'
import axios from 'axios'
import type { DictionaryEntries, DictionaryResponse, DictionaryConfig } from '../interfaces'
import { DateTime } from 'luxon'
import type { PluralRules } from './Helpers/PluralRules'
import {
    rule0,
    rule1,
    rule2,
    rule3,
    rule4,
    rule5,
    rule6,
    rule7,
    rule8,
    rule9,
    rule10,
    rule11,
    rule12,
    rule13,
    rule14,
    rule15
} from './Helpers/PluralRules'

// List all available plural rules
const rules: PluralRules = {
    0: rule0,
    1: rule1,
    2: rule2,
    3: rule3,
    4: rule4,
    5: rule5,
    6: rule6,
    7: rule7,
    8: rule8,
    9: rule9,
    10: rule10,
    11: rule11,
    12: rule12,
    13: rule13,
    14: rule14,
    15: rule15
}

export const useTranslator = defineStore(
    'translator',
    () => {
        /**
         * Variables
         */
        const defaultPluralKey = 'plural'
        const identifier = ref<string>('')
        const dictionary = ref<DictionaryEntries>({})
        const config = ref<DictionaryConfig>({
            name: '',
            regional: '',
            authors: [],
            plural_rule: 0,
            tag: ''
        })

        /**
         * Functions
         */
        // Load the dictionary from the API
        async function load() {
            axios.get<DictionaryResponse>('/api/dictionary').then((response) => {
                identifier.value = response.data.identifier
                config.value = response.data.config
                dictionary.value = response.data.dictionary
            })
        }

        // The translate function
        function translate(key: string, placeholders: string | number | object = {}): string {
            const { message, placeholders: mutatedPlaceholders } = getMessageFromKey(
                key,
                placeholders
            )
            placeholders = mutatedPlaceholders

            return replacePlaceholders(message, placeholders)
        }

        /**
         * Format a date to the user locale
         *
         * @param date The date to format, in ISO format
         * @param format The format to use. Default to `DATETIME_MED_WITH_WEEKDAY`.
         *               See the Luxon documentation for more information on formatting
         *
         * @see https://moment.github.io/luxon/#/formatting?id=presets
         * @see https://moment.github.io/luxon/#/formatting?id=table-of-tokens
         */
        function translateDate(
            date: string,
            format: string | object = DateTime.DATETIME_MED_WITH_WEEKDAY
        ): string {
            const dt = getDateTime(date)
            if (typeof format === 'object') {
                return dt.toLocaleString(format)
            } else {
                return dt.toFormat(format)
            }
        }

        /**
         * Returns the Luxon DateTime object for the given date, with the user
         * locale, so Luxon methods can be used without having to set the locale
         *
         * @param date The date to format, in ISO format
         */
        function getDateTime(date: string): DateTime {
            return DateTime.fromISO(date).setLocale(config.value.tag)
        }

        // TODO : Add doc + make Placeholders a type
        function getMessageFromKey(
            key: string,
            placeholders: string | number | Record<string, any>
        ): { message: string; placeholders: string | number | Record<string, any> } {
            // Return direct match
            if (dictionary.value[key] !== undefined) {
                return { message: dictionary.value[key], placeholders }
            }

            // First, let's see if we can get the plural rules.
            // A plural form will always have priority over the `@TRANSLATION` instruction
            // We start by picking up the plural key, aka which placeholder contains the numeric value defining how many {x} we have
            const pluralKey = dictionary.value[key + '.@PLURAL'] || defaultPluralKey

            // Let's get the plural value, aka how many {x} we have
            // If no plural value was found, we fallback to `@TRANSLATION` instruction or default to 1 as a last resort
            let pluralValue: number = 1
            if (typeof placeholders === 'object' && placeholders[pluralKey] !== undefined) {
                pluralValue = Number(placeholders[pluralKey])
            } else if (typeof placeholders === 'number' || typeof placeholders === 'string') {
                pluralValue = Number(placeholders)
            } else if (dictionary.value[key + '.@TRANSLATION'] !== undefined) {
                // We have a `@TRANSLATION` instruction, return this
                return { message: dictionary.value[key + '.@TRANSLATION'], placeholders }
            }

            // If placeholders is a numeric value, we transform back to an array for replacement in the main message
            if (typeof placeholders === 'number' || typeof placeholders === 'string') {
                placeholders = { [pluralKey]: pluralValue }
            } else if (typeof placeholders === 'object' && placeholders[pluralKey] === undefined) {
                placeholders = { ...placeholders, [pluralKey]: pluralValue }
            }

            // At this point, we need to go deeper and find the correct plural form to use
            const pluralRuleKey = getPluralMessageKey(key, pluralValue)

            // Only return if the plural is not null. Will happen if the message array don't follow the rules
            if (dictionary.value[key + '.' + pluralRuleKey] !== undefined) {
                return { message: dictionary.value[key + '.' + pluralRuleKey], placeholders }
            }

            // One last check... If we don't have a rule, but the $pluralValue
            // as a key does exist, we might still be able to return it
            if (dictionary.value[key + '.' + pluralValue] !== undefined) {
                return { message: dictionary.value[key + '.' + pluralValue], placeholders }
            }

            // Return @TRANSLATION match
            if (dictionary.value[key + '.@TRANSLATION'] !== undefined) {
                return { message: dictionary.value[key + '.@TRANSLATION'], placeholders }
            }

            // If the message is an array, but we can't find a plural form or a "@TRANSLATION" instruction, we can't go further.
            // We can't return the array, so we'll return the key
            return { message: key, placeholders }
        }

        function replacePlaceholders(
            message: string,
            placeholders: string | number | Record<string, any>
        ): string {
            // If placeholders is not an object at this point, we make it an object, using `plural` as the key
            if (typeof placeholders !== 'object') {
                placeholders = { [defaultPluralKey]: placeholders }
            }

            // Interpolate translatable placeholders values. This allows to
            // pre-translate placeholder which value starts with the `&` character
            // console.debug('Looping Placeholders', placeholders)
            for (const [name, value] of Object.entries(placeholders)) {
                // console.debug(`> ${name}: ${value}`)

                //We don't allow nested placeholders. They will return errors on the next lines
                if (typeof value !== 'string') {
                    continue
                }

                // We test if the placeholder value starts the "&" character.
                // That means we need to translate that placeholder value
                if (value.startsWith('&')) {
                    // Remove the current placeholder from the master $placeholder
                    // array, otherwise we end up in an infinite loop
                    const data = Object.fromEntries(
                        Object.entries(placeholders).filter(([k]) => k !== name)
                    )

                    // Translate placeholders value and place it in the main $placeholder array
                    placeholders[name] = translate(value.substring(1), data)
                }
            }

            // We check for {{&...}} strings in the resulting message.
            // While the previous loop pre-translated placeholder value, this one
            // pre-translate the message string vars
            // We use some regex magic to detect them !
            message = message.replace(/{{&(([^}]+[^a-z]))}}/g, (match, p1) => {
                return translate(p1, placeholders)
            })

            // Now it's time to replace the remaining placeholder.
            for (const [name, value] of Object.entries(placeholders)) {
                const regex = new RegExp(`{{${name}}}`, 'g')
                message = message.replace(regex, String(value))
            }

            return message
        }

        /**
         * Return the correct plural message form to use.
         * When multiple plural form are available for a message, this method will return the correct oen to use based on the numeric value.
         *
         * @param int     $pluralValue  The numeric value used to select the correct message
         *
         * @return int|null Returns which key from $messageArray to use
         */
        function getPluralMessageKey(key: string, pluralValue: number): number | null {
            // Bypass the rules for a value of "0". Instead of returning the
            // correct plural form (>= 1), we force return the "0" form, which
            // can used to display "0 users" as "No users".
            if (pluralValue === 0 && dictionary.value[key + '.0'] !== undefined) {
                return 0
            }

            // Get the correct plural form to use depending on the language
            const pluralForm = getPluralForm(pluralValue)

            // If the dictionary contains a string for this form, return the form
            if (dictionary.value[key + '.' + pluralForm] !== undefined) {
                return pluralForm
            }

            // If the key we need doesn't exist, use the previous available one, including the special "0" form
            // This is a fallback to avoid errors when the dictionary is not complete
            for (let i = pluralForm; i >= 0; i--) {
                if (dictionary.value[key + '.' + i] !== undefined) {
                    return i
                }
            }

            // If no key was found, null will be returned
            return null
        }

        function getPluralForm(pluralValue: number, forceRule?: number): number {
            const rule = forceRule ?? config.value.plural_rule

            if (rule < 0 || rule >= Object.keys(rules).length) {
                throw new Error(`The rule number ${rule} must be between 0 and 15`)
            }

            return rules[rule](pluralValue)
        }

        /**
         * Return store
         */
        return {
            dictionary,
            load,
            config,
            identifier,
            translate,
            translateDate,
            getPluralForm,
            getDateTime
        }
    },
    { persist: true }
)
