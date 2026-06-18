import { describe, expect, beforeEach, test, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { DateTime, Settings } from 'luxon'
import axios from 'axios'
import { useTranslator } from '../../stores/useTranslator'
import type { DictionaryConfig, DictionaryEntries, DictionaryResponse } from '../../interfaces'

const testDictionaryConfig: DictionaryConfig = {
    name: 'English',
    regional: 'English',
    authors: ['UserFrosting'],
    plural_rule: 1,
    tag: 'en-US'
}

const testDictionaryConfigFr: DictionaryConfig = {
    name: 'French',
    regional: 'Français',
    authors: ['Malou'],
    plural_rule: 1,
    tag: 'fr-FR'
}

const testDictionaryEntries: DictionaryEntries = {
    YES: 'Yes',
    NO: 'No',
    WELCOME_TO: 'Welcome to {{title}}!',
    'ERROR.@TRANSLATION': 'The Error',
    'ERROR.TITLE': "We've sensed a great disturbance in the Force.",
    USERNAME: 'Username',
    'COLOR.BLACK': 'black',
    'COLOR.RED': 'red',
    'COLOR.WHITE': 'white',
    'COLOR.0': 'colors',
    'COLOR.1': 'color',
    'COLOR.2': 'colors',
    'X_CARS.0': 'no cars',
    'X_CARS.1': 'a car',
    'X_CARS.2': '{{plural}} cars',
    'X_HUNGRY_CATS.@PLURAL': 'num',
    'X_HUNGRY_CATS.1': '{{num}} hungry cat',
    'X_HUNGRY_CATS.2': '{{num}} hungry cats',
    X_FOO: '{{plural}}x foos',
    'CAR.1': 'car',
    'CAR.2': 'cars',
    'CAR.GAS': 'gas',
    'CAR.EV.@TRANSLATION': 'electric',
    'CAR.EV.FULL': 'full electric',
    'CAR.FULL_MODEL': '{{make}} {{model}} {{year}}',
    'MY_CARS.@TRANSLATION': 'My cars',
    'MY_CARS.1': 'I have a {{type}} {{&CAR}}',
    'MY_CARS.2': 'I have {{plural}} {{type}} {{&CAR}}',
    TEST_LIMIT: 'Your test must be between {{min}} and {{max}} potatoes.',
    MIN: 'minimum',
    'X_RULES.0': 'no rules',
    'X_RULES.1': '{{plural}} rule', // No plural form for 2
    'X_BANANAS.0': 'no bananas', // No plural form for 1, 2, etc.
    'X_DOGS.5': 'five dogs',
    'X_DOGS.101': '101 Dalmatians',
    'X_DOGS.1000': 'An island of dogs'
}

const testDictionary: DictionaryResponse = {
    identifier: 'en_US',
    config: testDictionaryConfig,
    dictionary: testDictionaryEntries
}

const testDictionaryFr: DictionaryResponse = {
    identifier: 'fr_FR',
    config: testDictionaryConfigFr,
    dictionary: testDictionaryEntries
}

describe('API Tests', async () => {
    test('API should be saved', async () => {
        // Arrange
        setActivePinia(createPinia())
        const translator = useTranslator()
        const { translate, load } = translator
        const response = { data: testDictionary }
        vi.spyOn(axios, 'get').mockResolvedValue(response as any)

        // Assert initial state
        expect(translator.identifier).toBe('')
        expect(translator.config).toEqual({
            name: '',
            regional: '',
            authors: [],
            plural_rule: 0,
            tag: ''
        })

        // Act
        await load()

        // Assert config data
        expect(axios.get).toHaveBeenCalledWith('/api/dictionary')
        expect(translator.config).toEqual(testDictionaryConfig)
        expect(translator.identifier).toBe('en_US')

        // Assert basic translate method
        expect(translate('YES')).toBe('Yes')
        expect(translate('NO')).toBe('No')
        expect(translate('WELCOME_TO')).toBe('Welcome to {{title}}!')
    })
})

describe('Translator Tests', () => {
    beforeEach(async () => {
        // Arrange
        setActivePinia(createPinia())
        const translator = useTranslator()
        const { load } = translator
        const response = { data: testDictionary }
        vi.spyOn(axios, 'get').mockResolvedValue(response as any)

        // Act
        await load()
    })

    test('Should handle basic translation', () => {
        const { translate } = useTranslator()
        expect(translate('USERNAME')).toBe('Username')
    })

    test('Should handle key not existing', () => {
        const { translate } = useTranslator()
        expect(translate('NOT_EXIST')).toBe('NOT_EXIST')
        expect(translate('NOT EXIST')).toBe('NOT EXIST')
    })

    test('Should handle @TRANSLATION', () => {
        const { translate } = useTranslator()
        expect(translate('ERROR')).toBe('The Error')
        expect(translate('ERROR.TITLE')).toBe("We've sensed a great disturbance in the Force.")
    })

    test('Should handle placeholders', () => {
        const { translate } = useTranslator()
        expect(translate('WELCOME_TO')).toBe('Welcome to {{title}}!') // Placeholder not replaced
        expect(translate('WELCOME_TO', { title: 'UserFrosting' })).toBe('Welcome to UserFrosting!')
        expect(translate('WELCOME_TO', { bar: 'UserFrosting' })).toBe('Welcome to {{title}}!') // Wrong key
        expect(translate('WELCOME_TO', 'UserFrosting')).toBe('Welcome to {{title}}!') // Key not provided
        expect(translate('WELCOME_TO', { title: 'UserFrosting', foo: 'bar' })).toBe(
            'Welcome to UserFrosting!'
        ) // Extra key not used
    })

    test('Should handle basic plurals', () => {
        const { translate } = useTranslator()
        expect(translate('X_CARS', 0)).toBe('no cars')
        expect(translate('X_CARS', 1)).toBe('a car')
        expect(translate('X_CARS', 2)).toBe('2 cars')
        expect(translate('X_CARS', 5)).toBe('5 cars')
    })

    test('Should handle pluralization with custom plural key', () => {
        const { translate } = useTranslator()
        expect(translate('X_HUNGRY_CATS', { num: 0 })).toBe('0 hungry cats')
        expect(translate('X_HUNGRY_CATS', { num: 1 })).toBe('1 hungry cat')
        expect(translate('X_HUNGRY_CATS', { num: 2 })).toBe('2 hungry cats')
        expect(translate('X_HUNGRY_CATS', { num: 5 })).toBe('5 hungry cats')

        // Custom key can also be omitted in the placeholder if it's the only
        // placeholder even with custom plural key
        expect(translate('X_HUNGRY_CATS', 5)).toBe('5 hungry cats')

        // Test missing pluralization and placeholder (default to 1)
        expect(translate('X_HUNGRY_CATS')).toBe('1 hungry cat')
    })

    test('Should handle plurals default, when no placeholder', () => {
        const { translate } = useTranslator()
        expect(translate('X_CARS')).toBe('a car')
    })

    test('Should handle Key With No Plural', () => {
        const { translate } = useTranslator()
        expect(translate('USERNAME', 123)).toBe('Username') // USERNAME has no placeholders
        expect(translate('X_FOO')).toBe('{{plural}}x foos') // 'X_FOO' doesn't have children, so it's not treated as a "pluralize-able" string
        expect(translate('X_FOO', { plural: 1 })).toBe('1x foos') // Replace {{plural}} with 1
        expect(translate('X_FOO', 1)).toBe('1x foos') // Replace {{plural}} with 1, without specifying the key
        expect(translate('X_FOO', 123)).toBe('123x foos') // Replace {{plural}} with 123
    })

    test('Should handle plurals for different plural rules', () => {
        const { translate } = useTranslator()

        // English plural rule is 1
        expect(translate('COLOR', 0)).toBe('colors')
        expect(translate('COLOR', 1)).toBe('color')
        expect(translate('COLOR', 2)).toBe('colors')
        expect(translate('COLOR', 3)).toBe('colors')

        // Same as above, but with a custom plural key (french)
        // Note "0" is plural (colors) in english, singular (couleur) in french !
        // TODO : Implement plurals & load custom locale
        // expect(translate('COLOR', 0)).toBe('colors')
        // expect(translate('COLOR', 1)).toBe('color')
        // expect(translate('COLOR', 2)).toBe('colors')
        // expect(translate('COLOR', 3)).toBe('colors')
    })

    test('Should handle a simple replacement when the key is not defined in dictionary', () => {
        const { translate } = useTranslator()
        expect(translate('You are {{status}}', { status: 'dumb' })).toBe('You are dumb')
    })

    test('Should handle @TRANSLATION when the key have plural rules', () => {
        const { translate } = useTranslator()
        expect(translate('MY_CARS')).toBe('My cars') // @TRANSLATION is used, not the 1 rule
        expect(translate('MY_CARS', 1)).toBe('I have a {{type}} car')
        expect(translate('MY_CARS', 2)).toBe('I have 2 {{type}} cars')
    })

    test('Should handle complex placeholders', () => {
        const { translate } = useTranslator()
        expect(translate('MY_CARS', { type: 'car' })).toBe('My cars')
        expect(translate('MY_CARS', { type: 'gaz', plural: 1 })).toBe('I have a gaz car')
        expect(translate('MY_CARS', { type: 'gaz', plural: 2 })).toBe('I have 2 gaz cars')
        expect(translate('MY_CARS', { type: '&CAR.GAS', plural: 2 })).toBe('I have 2 gas cars')
        expect(translate('MY_CARS', { type: '&CAR.EV', plural: 2 })).toBe('I have 2 electric cars')
        expect(translate('MY_CARS', { type: '&CAR.EV.FULL', plural: 1 })).toBe(
            'I have a full electric car'
        )
        expect(
            translate('MY_CARS', {
                type: '&CAR.FULL_MODEL',
                plural: 5,
                make: 'Toyota',
                model: 'Camry',
                year: 2022
            })
        ).toBe('I have 5 Toyota Camry 2022 cars')
    })

    // Test basic placeholder replacement using int as placeholder value (So they don't try to translate "min" and "max")
    // We don't want to end up with "Your test must be between _minimum_ and 200 potatoes"
    test('Should handle placeholder not overwritten by other key', () => {
        const { translate } = useTranslator()
        expect(translate('TEST_LIMIT', { min: 4, max: 200 })).toBe(
            'Your test must be between 4 and 200 potatoes.'
        )
    })

    // 2 will return singular as the plural is not defined
    test('Should handle pluralization with no rules', () => {
        const { translate } = useTranslator()
        expect(translate('X_RULES', 0)).toBe('no rules')
        expect(translate('X_RULES', 1)).toBe('1 rule')
        expect(translate('X_RULES', 2)).toBe('2 rule')

        // X_BANANAS
        expect(translate('X_BANANAS', 0)).toBe('no bananas')
        expect(translate('X_BANANAS', 1)).toBe('no bananas')
        expect(translate('X_BANANAS', 2)).toBe('no bananas')
        expect(translate('X_BANANAS', 5)).toBe('no bananas')
    })

    // The keys are int, but don't follow the rules. It will fallback to the literal key
    test("Should handle plurals who doesn't follow the rules", () => {
        const { translate } = useTranslator()
        expect(translate('X_DOGS')).toBe('X_DOGS')
        expect(translate('X_DOGS', 0)).toBe('X_DOGS')
        expect(translate('X_DOGS', 1)).toBe('X_DOGS')
        expect(translate('X_DOGS', 2)).toBe('X_DOGS') // No plural rules found
        expect(translate('X_DOGS', 5)).toBe('five dogs') // This one is hardcoded and will fallback as normal string key
        expect(translate('X_DOGS', 101)).toBe('101 Dalmatians') // Same here
        expect(translate('X_DOGS', 102)).toBe('X_DOGS') // This one is not hardcoded
        expect(translate('X_DOGS', 1000)).toBe('An island of dogs') // Still fallback, if the key is a string representing and INT
    })
})

describe('Date Tests', async () => {
    test('translateDate should return the correct values in English', async () => {
        // Arrange
        setActivePinia(createPinia())
        const translator = useTranslator()
        const { translateDate, load, getDateTime } = translator
        const response = { data: testDictionary }
        vi.spyOn(axios, 'get').mockResolvedValue(response as any)
        await load()

        // Assert basic translate method
        // Force the default timezone, so the test is consistent regardless of
        // the timezone of the runner
        Settings.defaultZone = 'America/New_York'
        expect(translateDate('2025-02-02T14:42:12.000000Z')).toBe('Sun, Feb 2, 2025, 9:42 AM')
        expect(translateDate('2025-02-02T14:42:12.000000Z', 'DDD')).toBe('February 2, 2025')
        expect(translateDate('2025-02-02T14:42:12.000000Z', DateTime.DATETIME_MED)).toBe(
            'Feb 2, 2025, 9:42 AM'
        )

        // Test get the Datetime object
        expect(getDateTime('2025-02-02T14:42:12.000000Z').monthLong).toBe('February')
    })

    test('translateDate should return the correct values in French', async () => {
        // Arrange
        setActivePinia(createPinia())
        const translator = useTranslator()
        const { translateDate, load, getDateTime } = translator
        const response = { data: testDictionaryFr }
        vi.spyOn(axios, 'get').mockResolvedValue(response as any)
        await load()

        // Assert basic translate method
        // Force the default timezone, so the test is consistent regardless of
        // the timezone of the runner
        Settings.defaultZone = 'America/New_York'
        expect(translateDate('2025-02-02T14:42:12.000000Z')).toBe('dim. 2 févr. 2025, 09:42')
        expect(translateDate('2025-02-02T14:42:12.000000Z', 'DDD')).toBe('2 février 2025')
        expect(translateDate('2025-02-02T14:42:12.000000Z', DateTime.DATETIME_MED)).toBe(
            '2 févr. 2025, 09:42'
        )

        // Test get the Datetime object
        expect(getDateTime('2025-02-02T14:42:12.000000Z').monthLong).toBe('février')
    })
})

describe('Plural Form Tests', () => {
    beforeEach(async () => {
        // Arrange
        setActivePinia(createPinia())
        const translator = useTranslator()
        const { load } = translator
        const response = { data: testDictionary }
        vi.spyOn(axios, 'get').mockResolvedValue(response as any)

        // Act
        await load()
    })

    test('Should return correct plural form for rule 1', () => {
        const { getPluralForm } = useTranslator()
        expect(getPluralForm(0, 1)).toBe(2)
        expect(getPluralForm(1, 1)).toBe(1)
        expect(getPluralForm(2, 1)).toBe(2)
        expect(getPluralForm(20, 1)).toBe(2)
    })

    test('Should return correct plural form for rule 2', () => {
        const { getPluralForm } = useTranslator()
        expect(getPluralForm(0, 2)).toBe(1) // French = zero is singular
        expect(getPluralForm(1, 2)).toBe(1)
        expect(getPluralForm(2, 2)).toBe(2)
        expect(getPluralForm(20, 2)).toBe(2)
    })

    test('Should throw error for invalid rule', () => {
        const { getPluralForm } = useTranslator()
        expect(() => getPluralForm(1, 16)).toThrow('The rule number 16 must be between 0 and 15')
    })

    test('Should use default rule if forceRule is not provided', () => {
        const { getPluralForm } = useTranslator()
        expect(getPluralForm(0)).toBe(2)
        expect(getPluralForm(1)).toBe(1)
        expect(getPluralForm(2)).toBe(2)
    })

    test('Should used the specified rule if forceRule is provided', () => {
        const { getPluralForm } = useTranslator()
        expect(getPluralForm(0, 1)).toBe(2) // English
        expect(getPluralForm(0, 2)).toBe(1) // French
        expect(getPluralForm(5, 1)).toBe(2) // English
        expect(getPluralForm(5, 8)).toBe(3) // Slavic
    })
})

// TODO : Test each plural rules
