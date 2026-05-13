<?php

declare(strict_types=1);

/*
 * UserFrosting Framework (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/framework
 * @copyright Copyright (c) 2013-2024 Alexander Weissman, Louis Charette, Jordan Mele
 * @license   https://github.com/userfrosting/framework/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests\I18n;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use UserFrosting\I18n\Dictionary;
use UserFrosting\I18n\Locale;
use UserFrosting\I18n\Translator;
use UserFrosting\UniformResourceLocator\ResourceLocation;
use UserFrosting\UniformResourceLocator\ResourceLocator;
use UserFrosting\UniformResourceLocator\ResourceStream;

class TranslatorTest extends TestCase
{
    /**
     * @var string Test locale file location
     **/
    protected string $basePath = __DIR__ . '/data/sprinkles';

    /**
     * @var ResourceLocator
     **/
    protected ResourceLocator $locator;

    public function setUp(): void
    {
        $this->locator = new ResourceLocator($this->basePath);

        // Register stream
        $this->locator->addStream(new ResourceStream('locale'));

        // Add them one at a time to simulate how they are added in SprinkleManager
        $this->locator->addLocation(new ResourceLocation('core'))
                      ->addLocation(new ResourceLocation('account'))
                      ->addLocation(new ResourceLocation('admin')) // Simulate non existing sprinkle
                      ->addLocation(new ResourceLocation('fr_CA')) // Simulate the fr_CA locale !
                      ->addLocation(new ResourceLocation('test'));
    }

    /**
     * Test locale with a plural option.
     */
    public function testGetDictionaryAndLocale(): void
    {
        $locale = new Locale('en_US');
        $dictionary = new Dictionary($locale, $this->locator);
        $translator = new Translator($dictionary);

        $this->assertSame($dictionary, $translator->getDictionary());
        $this->assertSame($locale, $translator->getLocale());
        $this->assertSame($dictionary->getLocale(), $translator->getLocale());
    }

    /**
     * Test locale with a plural option.
     */
    public function testGetPluralForm(): void
    {
        $translator = $this->getTranslator('en_US');
        $this->assertSame(1, $translator->getPluralForm(1));
        $this->assertSame(2, $translator->getPluralForm(2));
        $this->assertSame(2, $translator->getPluralForm(20));

        // Test with 0. If `plural_rule` 1 is applied, it will return `X_CARS.2` (zero is plural)
        // With `plural_rule` 0, it would have been `X_CARS.1` (no plurals)
        // and with `plural_rule` 2, would have been `X_CARS.1` also (0 is singular)
        $this->assertEquals($translator->translate('X_CARS', 0), 'no cars');
        $this->assertEquals($translator->translate('X_CARS', 1), 'a car');
        $this->assertEquals($translator->translate('X_CARS', 2), '2 cars');
    }

    /**
     * @depends testGetPluralForm
     */
    public function testGetPluralFormForced(): void
    {
        $translator = $this->getTranslator('en_US');
        $this->assertSame(1, $translator->getPluralForm(2, 0));
    }

    /**
     * @depends testGetPluralForm
     * Test locale without a plural rule.
     */
    public function testGetPluralFormWithNoDefineRule(): void
    {
        $translator = $this->getTranslator('es_ES');
        $this->assertSame(1, $translator->getPluralForm(1));
        $this->assertSame(2, $translator->getPluralForm(2));
        $this->assertSame(2, $translator->getPluralForm(20));

        // Test with 0. If `@PLURAL_RULE` 1 is applied, it will return `X_CARS.2` (zero is plural)
        // With `@PLURAL_RULE` 0, it would have been `X_CARS.1` (no plurals)
        // and with `@PLURAL_RULE` 2, would have been `X_CARS.1` also (0 is singular)
        $this->assertEquals($translator->translate('X_CARS', 0), '0 coches');
    }

    /**
     * @depends testGetPluralForm
     */
    public function testGetPluralFormWithException(): void
    {
        $translator = $this->getTranslator();
        $this->expectException(\OutOfRangeException::class);
        $translator->getPluralForm(1, 132);
    }

    /**
     * @dataProvider localeStringProvider
     *
     * @param string       $key
     * @param string[]|int $placeholders
     * @param string       $expectedResultEnglish
     * @param string       $expectedResultFrench
     */
    public function testTranslate(string $key, array|int $placeholders, string $expectedResultEnglish, string $expectedResultFrench): void
    {
        $translator = $this->getTranslator();
        $this->assertEquals($expectedResultEnglish, $translator->translate($key, $placeholders));

        $frenchTranslator = $this->getTranslator('fr_FR');
        $this->assertEquals($expectedResultFrench, $frenchTranslator->translate($key, $placeholders));
    }

    /**
     * @depends testTranslate
     */
    public function testTranslateKeyWithNoPlural(): void
    {
        $translator = $this->getTranslator();
        $this->assertEquals($translator->translate('USERNAME', 123), 'Username');
        $this->assertEquals($translator->translate('X_FOO'), 'x foos');
        $this->assertEquals($translator->translate('X_FOO', ['plural' => 1]), '1x foos');
        $this->assertEquals($translator->translate('X_FOO', 1), '1x foos');
        $this->assertEquals($translator->translate('X_FOO', 123), '123x foos');
    }

    /**
     * Basic test to see if triple dependency works.
     */
    public function testTranslateWithNestedDependencies(): void
    {
        $frenchTranslator = $this->getTranslator('fr_CA');
        $this->assertEquals($frenchTranslator->translate('USERNAME'), 'Nom d\'utilisateur tabarnak');
    }

    /**
     * Run more complex translations outside the provider.
     */
    public function testTranslateWithNested(): void
    {
        // English translator
        $translator = $this->getTranslator();

        // Example of a lang key in a placeholder
        // N.B.: In a real life situation, it's recommended to create a new Top level plural instead
        $this->assertEquals($translator->translate('MY_CARS', ['x_cars' => $translator->translate('X_CARS', 10)]), 'I have 10 cars');

        // Example of a complex translation
        $this->assertEquals($translator->translate('MY_CAR_STRING', [
            'my_car' => $translator->translate('CAR.EV.PLUGIN_HYBRID'),
            'color'  => $translator->translate('COLOR.RED'),
        ]), 'I drive a red plug-in hybrid');

        // FRENCH version
        $frenchTranslator = $this->getTranslator('fr_FR');

        // Example of a lang key in a placeholder
        // N.B.: In a real life situation, it's recommended to create a new Top level plural instead
        $this->assertEquals($frenchTranslator->translate('MY_CARS', ['x_cars' => $frenchTranslator->translate('X_CARS', 10)]), "J'ai 10 voitures");

        // Example of a complex translation
        $this->assertEquals($frenchTranslator->translate('MY_CAR_STRING', [
            'my_car' => $frenchTranslator->translate('CAR.EV.PLUGIN_HYBRID'),
            'color'  => $frenchTranslator->translate('COLOR.RED'),
        ]), 'Je conduit une hybride branchable de couleur rouge');
    }

    /**
     * DataProvider for testTranslateEN.
     *
     * @return mixed[] [$key, $placeholders, $expectedResultEnglish, $expectedResultFrench]
     */
    public static function localeStringProvider(): array
    {
        return [
            // Test most basic functionality
            ['USERNAME', [], 'Username', "Nom d'utilisateur"],

            // Test key overwrite in higher sprinkle. GROUP is not defined in English
            ['GROUP', [], 'My Foo Group', 'Mes Foo Groupe'],

            // Test most the base locale
            ['BASE_FALLBACK', [], 'Base fallback', 'Base fallback'],

            // Test @TRANSLATION
            ['ACCOUNT', [], 'Account', "Compte de l'utilisateur"], // Shortcut for `ACCOUNT.@TRANSLATION`
            ['ACCOUNT.ALT', [], 'Profile', 'Profil'],

            // Test basic plural functionality
            ['COLOR', 0, 'colors', 'couleur'], //Note plural in english, singular in french !
            ['COLOR', 1, 'color', 'couleur'],
            ['COLOR', 2, 'colors', 'couleurs'],
            ['COLOR', 3, 'colors', 'couleurs'],

            // Test plural default
            ['COLOR', [], 'color', 'couleur'],

            // Test basic nested items
            ['COLOR.BLACK', [], 'black', 'noir'],
            ['COLOR.WHITE', [], 'white', 'blanc'],

            // Test placeholders
            ['MY_CAR_MAKE', ['car_make' => 'Toyota'], 'My car is a Toyota', 'Ma voiture est une Toyota'],
            ['MY_CAR_YEAR', ['year' => 2015], 'I bought my car in 2015', "J'ai acheté ma voiture en 2015"],

            // Test plural placeholder
            ['X_CARS', 0, 'no cars', 'aucune voiture'],
            ['X_CARS', 1, 'a car', 'une voiture'],
            ['X_CARS', 2, '2 cars', '2 voitures'],
            ['X_CARS', 10, '10 cars', '10 voitures'],

            // Test `+CAR` called (top nested name) without "CAR" defined
            ['CAR', [], 'car', 'voiture'],

            // Test 3 levels nested with "CAR"
            ['CAR.GAS', [], 'gas', 'à essence'],
            ['CAR.EV', [], 'electric', 'électrique'],
            ['CAR.EV.HYBRID', [], 'hybrid', 'hybride'],
            ['CAR.HYDROGEN', [], 'hydrogen', "à l'hydrogène"],

            // Test extra placeholder (`year` not used)
            ['MY_CAR_MAKE', ['car_make' => 'Toyota', 'year' => 2014], 'My car is a Toyota', 'Ma voiture est une Toyota'],

            // Test missing placeholder (`car_make` nor defined)
            ['MY_CAR_MAKE', [], 'My car is a ', 'Ma voiture est une '],

            // Test that we can still access @TRANSLATION
            ['MY_EV_CARS', [], 'My electric cars', 'Mes voitures électriques'],

            // Test the special handles all together
            // MY_EV_CARS => {{who}} have {{plural}} {{type}} {{item}}
            //  - who => Hard coded literal in language file
            //  - plural => 3
            //  - type => Will be replaced by the message of "CAR.EV" key
            //  - item => Hard coded language key in languge file
            ['MY_EV_CARS', [
                'plural' => 3,
                'type'   => '&CAR.EV',
            ], 'I have 3 electric cars', 'Le chat a 3 voitures électriques'],

            // Test pluralization with custom plural key
            ['X_HUNGRY_CATS', ['num' => 0], '0 hungry cats', '0 chat affamé'],
            ['X_HUNGRY_CATS', ['num' => 1], '1 hungry cat', '1 chat affamé'],
            ['X_HUNGRY_CATS', ['num' => 2], '2 hungry cats', '2 chats affamés'],
            ['X_HUNGRY_CATS', ['num' => 5], '5 hungry cats', '5 chats affamés'],

            // Custom key can also be omitted in the placeholder if it's the only placeholder even with custom plural key
            ['X_HUNGRY_CATS', 5, '5 hungry cats', '5 chats affamés'],

            // Test missing pluralization and placeholder (default to 1)
            ['X_HUNGRY_CATS', [], '1 hungry cat', '1 chat affamé'],

            // Test basic placeholder replacement using int as placeholder value (So they don't try to translate "min" and "max")
            // We don't want to end up with "Votre test doit être entre minimum et 200 patates"
            ['TEST_LIMIT', ['min' => 4, 'max' => 200], 'Your test must be between 4 and 200 potatoes.', 'Votre test doit être entre 4 et 200 patates.'],

            // Test message is an empty array. Will return the key
            ['EMPTY', [], 'EMPTY', 'EMPTY'],

            // Test missing one rule. 2 will return singular as the plural is not defined
            ['X_RULES', 1, '1 rule', '1 règle'],
            ['X_RULES', 2, '2 rule', '2 règle'],

            // Test missing all rules, but still have 0. Will return the "zero" form.
            ['X_BANANAS', 1, 'no bananas', 'aucune banane'],
            ['X_BANANAS', 2, 'no bananas', 'aucune banane'],

            // Test keys are int, but don't follow the rules. It will fallback to the key
            ['X_DOGS', [], 'X_DOGS', 'X_DOGS'],
            ['X_DOGS', 0, 'X_DOGS', 'X_DOGS'],
            ['X_DOGS', 1, 'X_DOGS', 'X_DOGS'],
            ['X_DOGS', 2, 'X_DOGS', 'X_DOGS'], // No plural rules found
            ['X_DOGS', 5, 'five dogs', 'cinq chiens'], // This one is hardcoded and will fallback as normal string key
            ['X_DOGS', 101, '101 Dalmatians', '101 Dalmatiens'], // Same here
            ['X_DOGS', 102, 'X_DOGS', 'X_DOGS'], // This one is not hardcoded
            ['X_DOGS', 1000, 'An island of dogs', 'Une tempête de chiens'], // Still fallback, if the key is a string representing and INT

            // keys as strings
            ['X_TABLES', 0, 'no tables', 'aucune table'],
            ['X_TABLES', 1, 'a table', 'une table'],
            ['X_TABLES', 2, '2 tables', '2 tables'],
            ['X_TABLES', 5, '5 tables', '5 tables'],
        ];
    }

    /**
     * Test the readme examples.
     */
    public function testReadme(): void
    {
        // Create the $translator object
        $translator = $this->getTranslator();

        // Test from the README
        $carMake = 'Honda';
        $this->assertEquals($translator->translate('COMPLEX_STRING', [
            'child'    => '&X_CHILD',
            'nb_child' => 1,
            'adult'    => '&X_ADULT',
            'nb_adult' => 0,
            'color'    => '&COLOR.WHITE',
            'car'      => '&CAR.FULL_MODEL',
            'make'     => $carMake,
            'model'    => 'Civic',
            'year'     => 1993,
        ]), "There's a child and no adults in the white Honda Civic 1993");

        $this->assertEquals($translator->translate('COMPLEX_STRING2', [
            'nb_child' => 1,
            'nb_adult' => 0,
            'color'    => '&COLOR.WHITE',
            'make'     => $carMake,
            'model'    => 'Civic',
            'year'     => 1993,
        ]), "There's a child and no adults in the white Honda Civic 1993");
    }

    /**
     * Test for placeholder applied to `$key` if it doesn't match any languages keys.
     */
    public function testWithoutKeys(): void
    {
        $translator = $this->getTranslator();
        $this->assertEquals($translator->translate('You are {{status}}', ['status' => 'dumb']), 'You are dumb');
    }

    /**
     * Test for placeholder applied to `$key` if it doesn't match any languages keys.
     */
    public function testNonStringMessage(): void
    {
        $translator = $this->getTranslator();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Message for key `NON_STRING` must be string or array.');
        $translator->translate('NON_STRING');
    }

    /**
     * @dataProvider twigProvider
     *
     * @param string       $key
     * @param string[]|int $placeholders
     * @param string       $expectedResult
     */
    public function testTwigFilters(string $key, array|int $placeholders, string $expectedResult): void
    {
        $translator = $this->getTranslator();
        $this->assertEquals($translator->translate($key, $placeholders), $expectedResult);
    }

    /**
     * Data Provider for testTwigFilters.
     *
     * @return mixed[]
     */
    public static function twigProvider(): array
    {
        return [

            //ESCAPE : http://twig.sensiolabs.org/doc/2.x/filters/escape.html
            //RAW : http://twig.sensiolabs.org/doc/2.x/filters/raw.html
            ['TWIG.ESCAPE', ['foo' => '<strong>bar</strong>'], 'Placeholder should be escaped : &lt;strong&gt;bar&lt;/strong&gt;'],
            ['TWIG.ESCAPE_DEFAULT', ['foo' => '<strong>bar</strong>'], 'Placeholder should be escaped : &lt;strong&gt;bar&lt;/strong&gt;'],
            ['TWIG.ESCAPE_NOT', ['foo'     => '<strong>bar</strong>'], 'Placeholder should NOT be escaped : <strong>bar</strong>'],

            //DEFAULT: http://twig.sensiolabs.org/doc/2.x/filters/default.html
            ['TWIG.DEFAULT', [], 'Using default: bar'],
            ['TWIG.DEFAULT', ['foo' => 'cat'], 'Using default: cat'],
            ['TWIG.DEFAULT_NOT', [], 'Not using default: '],

            //ABS : http://twig.sensiolabs.org/doc/2.x/filters/abs.html
            ['TWIG.ABS', ['number' => '-5'], '5'],
            ['TWIG.ABS_NOT', ['number' => '-5'], '-5'],

            //DATE : http://twig.sensiolabs.org/doc/2.x/filters/date.html
            ['TWIG.DATE', ['when' => '10 September 2000'], '09/10/2000'],

            //FIRST : http://twig.sensiolabs.org/doc/2.x/filters/first.html
            //LAST: http://twig.sensiolabs.org/doc/2.x/filters/last.html
            ['TWIG.FIRST', ['numbers' => [1, 3, 5]], '1'],
            ['TWIG.LAST', ['numbers' => [1, 3, 5]], '5'],

            //NUMBER_FORMAT: http://twig.sensiolabs.org/doc/2.x/filters/number_format.html
            ['TWIG.NUMBER_FORMAT', ['number' => 9800.333], '9 800.33'],

            //LOWER: http://twig.sensiolabs.org/doc/2.x/filters/lower.html
            //UPPER: http://twig.sensiolabs.org/doc/2.x/filters/upper.html
            //CAPITALIZE: http://twig.sensiolabs.org/doc/2.x/filters/capitalize.html
            ['TWIG.LOWER', ['string' => 'WeLcOmE'], 'welcome'],
            ['TWIG.UPPER', ['string'      => 'WeLcOmE'], 'WELCOME'],
            ['TWIG.CAPITALIZE', ['string' => 'WeLcOmE'], 'Welcome'],
        ];
    }

    /**
     * @param string $language Default to 'en_US'. Use 'fr_FR' for french
     *
     * @return Translator
     */
    protected function getTranslator(string $language = 'en_US'): Translator
    {
        $locale = new Locale($language);
        $dictionary = new Dictionary($locale, $this->locator);
        $translator = new Translator($dictionary);

        return $translator;
    }
}
