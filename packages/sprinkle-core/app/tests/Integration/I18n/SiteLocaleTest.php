<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\I18n;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
use Psr\Http\Message\ResponseInterface as Response;
use UserFrosting\Config\Config;
use UserFrosting\I18n\Locale;
use UserFrosting\I18n\Translator;
use UserFrosting\Sprinkle\Core\I18n\SiteLocale;
use UserFrosting\Sprinkle\Core\I18n\SiteLocaleInterface;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase as TestCase;
use UserFrosting\Sprinkle\Core\Util\RequestContainer;
use UserFrosting\UniformResourceLocator\ResourceLocator;
use UserFrosting\UniformResourceLocator\ResourceStream;

/**
 * Tests SiteLocale.
 *
 * N.B.: This requires the full App stack, since locale files will be loaded.
 */
class SiteLocaleTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var array<string|bool|null>
     */
    protected array $testLocale = [
        'fr_FR' => 'french', // Legacy setting
        'en_US' => true,
        'es_ES' => false,
        'it_IT' => null, // Legacy setting
    ];

    protected Config $config;
    protected SiteLocale $locale;

    // Apply fake config
    public function setUp(): void
    {
        parent::setUp();

        // Set test config
        $this->config = $this->getService(Config::class);
        $this->config->set('site.locales.available', $this->testLocale);
    }

    public function testService(): void
    {
        $locale = $this->getService(SiteLocale::class);
        $this->assertInstanceOf(SiteLocale::class, $locale); // @phpstan-ignore-line
    }

    public function testFakeConfig(): void
    {
        $this->assertSame($this->testLocale, $this->config->get('site.locales.available'));
    }

    /**
     * @depends testService
     * @depends testFakeConfig
     */
    public function testGetAvailableIdentifiers(): void
    {
        $locale = $this->getService(SiteLocale::class);
        $this->assertSame([
            'fr_FR',
            'en_US',
        ], $locale->getAvailableIdentifiers());
    }

    /**
     * @depends testService
     * @depends testFakeConfig
     */
    public function testGetAvailable(): void
    {
        $locale = $this->getService(SiteLocale::class);
        $locales = $locale->getAvailable();

        $this->assertCount(2, $locales);
        $this->assertSame('fr_FR', $locales[0]->getIdentifier());
    }

    /**
     * @depends testGetAvailable
     */
    public function testGetAvailableOptions(): void
    {
        $locale = $this->getService(SiteLocale::class);

        // Implement fake locale file location & locator
        $locator = new ResourceLocator(__DIR__);
        $stream = new ResourceStream('locale', 'data', true);
        $locator->addStream($stream);

        // Set expectations. Note the sort applied here
        $expected = [
            'en_US' => 'English',
            'fr_FR' => 'Tomate', // Just to be sure the fake locale are loaded ;)
        ];
        $options = $locale->getAvailableOptions();
        $this->assertSame($expected, $options);
    }

    /**
     * @depends testGetAvailableIdentifiers
     */
    public function testIsAvailable(): void
    {
        $locale = $this->getService(SiteLocale::class);
        $this->assertFalse($locale->isAvailable('ZZ_zz'));
        $this->assertFalse($locale->isAvailable('es_ES'));
        $this->assertTrue($locale->isAvailable('en_US'));
    }

    /**
     * Will return the default locale (fr_FR)
     */
    public function testGetLocaleIdentifier(): void
    {
        $locale = $this->getService(SiteLocale::class);
        $this->config->set('site.locales.default', 'fr_FR');
        $this->assertSame('fr_FR', $locale->getLocaleIdentifier());
    }

    /**
     * Make sure the translator is loaded with the correct SiteLocale dependency.
     * Will return the default locale (fr_FR)
     */
    public function testGetLocaleIdentifierAndTranslator(): void
    {
        $locale = $this->getService(SiteLocale::class);

        $this->config->set('site.locales.default', 'fr_FR');
        $this->assertSame('fr_FR', $locale->getLocaleIdentifier());

        $translator = $this->getService(Translator::class);
        $this->assertSame('fr_FR', $translator->getLocale()->getIdentifier());
    }

    /**
     * Will return en_US
     */
    public function testGetLocaleIdentifierWithDefaultIdentifier(): void
    {
        $locale = $this->getService(SiteLocale::class);
        $this->config->set('site.locales.default', '');
        $this->assertSame('en_US', $locale->getLocaleIdentifier());
    }

    /**
     * Test old method of defining the default locale
     */
    public function testGetLocaleIdentifierWithCommaSeparatedString(): void
    {
        $locale = $this->getService(SiteLocale::class);
        $this->config->set('site.locales.default', 'fr_FR, en_US');
        $this->assertSame('fr_FR, en_US', $locale->getLocaleIdentifier());
    }

    /**
     * Test old method of defining the default locale
     */
    public function testGetLocaleIdentifierWithCommaSeparatedStringReverseOrder(): void
    {
        $locale = $this->getService(SiteLocale::class);
        $this->config->set('site.locales.default', 'en_US,fr_FR');
        $this->assertSame('en_US,fr_FR', $locale->getLocaleIdentifier());
    }

    public function testGetLocaleIdentifierWithBrowserAndNoHeader(): void
    {
        // Define request mock
        $request = m::mock(\Psr\Http\Message\ServerRequestInterface::class);
        $request->shouldReceive('hasHeader')->with('Accept-Language')->once()->andReturn(false);
        $requestContainer = new RequestContainer();
        $requestContainer->setRequest($request);
        $this->getContainer()->set(RequestContainer::class, $requestContainer);

        // Define default locale
        $this->config['site.locales.default'] = 'fr_FR';

        // Assertions
        $locale = $this->getService(SiteLocale::class);
        $this->assertSame('fr_FR', $locale->getLocaleIdentifier());
    }

    public function testGetLocaleIdentifierWithBrowserAndComplexLocale(): void
    {
        // Define mock
        $request = m::mock(\Psr\Http\Message\ServerRequestInterface::class);
        $request->shouldReceive('hasHeader')->with('Accept-Language')->once()->andReturn(true);
        $request->shouldReceive('getHeaderLine')->with('Accept-Language')->once()->andReturn('en-US, en;q=0.9, fr;q=0.8, de;q=0.7, *;q=0.5');
        $requestContainer = new RequestContainer();
        $requestContainer->setRequest($request);
        $this->getContainer()->set(RequestContainer::class, $requestContainer);

        // Define default locale
        $this->config['site.locales.default'] = 'fr_FR';

        // Assertions
        $locale = $this->getService(SiteLocale::class);
        $identifier = $locale->getLocaleIdentifier();
        $this->assertSame('en_US', $identifier);
        $this->assertTrue($locale->isAvailable($identifier));
    }

    public function testGetLocaleIdentifierWithBrowserAndComplexLocaleInLowerCase(): void
    {
        // Define mock
        $request = m::mock(\Psr\Http\Message\ServerRequestInterface::class);
        $request->shouldReceive('hasHeader')->with('Accept-Language')->once()->andReturn(true);
        $request->shouldReceive('getHeaderLine')->with('Accept-Language')->once()->andReturn('en-us, en;q=0.9, fr;q=0.8, de;q=0.7, *;q=0.5');
        $requestContainer = new RequestContainer();
        $requestContainer->setRequest($request);
        $this->getContainer()->set(RequestContainer::class, $requestContainer);

        // Define default locale
        $this->config['site.locales.default'] = 'fr_FR';

        // Assertions
        $locale = $this->getService(SiteLocale::class);
        $identifier = $locale->getLocaleIdentifier();
        $this->assertSame('en_US', $identifier);
        $this->assertTrue($locale->isAvailable($identifier));
    }

    public function testGetLocaleIdentifierWithBrowserAndMultipleLocale(): void
    {
        // Define mock
        $request = m::mock(\Psr\Http\Message\ServerRequestInterface::class);
        $request->shouldReceive('hasHeader')->with('Accept-Language')->once()->andReturn(true);
        $request->shouldReceive('getHeaderLine')->with('Accept-Language')->once()->andReturn('es-ES, fr-FR;q=0.7, fr-CA;q=0.9, en-US;q=0.8, *;q=0.5');
        $requestContainer = new RequestContainer();
        $requestContainer->setRequest($request);
        $this->getContainer()->set(RequestContainer::class, $requestContainer);

        // Define default locale
        $this->config['site.locales.default'] = 'fr_FR';

        // Assertions
        $locale = $this->getService(SiteLocale::class);
        $this->assertSame('en_US', $locale->getLocaleIdentifier());
    }

    public function testGetLocaleIdentifierWithBrowserAndLocaleInSecondPlace(): void
    {
        // Define mock
        $request = m::mock(\Psr\Http\Message\ServerRequestInterface::class);
        $request->shouldReceive('hasHeader')->with('Accept-Language')->once()->andReturn(true);
        $request->shouldReceive('getHeaderLine')->with('Accept-Language')->once()->andReturn('zz-ZZ, en-US;q=0.9, fr;q=0.8, de;q=0.7, *;q=0.5');
        $requestContainer = new RequestContainer();
        $requestContainer->setRequest($request);
        $this->getContainer()->set(RequestContainer::class, $requestContainer);

        // Define default locale
        $this->config['site.locales.default'] = 'fr_FR';

        // Assertions
        $locale = $this->getService(SiteLocale::class);
        $this->assertSame('en_US', $locale->getLocaleIdentifier());
    }

    public function testGetLocaleIdentifierWithBrowserAndInvalidLocale(): void
    {
        // Define mock
        $request = m::mock(\Psr\Http\Message\ServerRequestInterface::class);
        $request->shouldReceive('hasHeader')->with('Accept-Language')->once()->andReturn(true);
        $request->shouldReceive('getHeaderLine')->with('Accept-Language')->once()->andReturn('fo,oba;;;r,');
        $requestContainer = new RequestContainer();
        $requestContainer->setRequest($request);
        $this->getContainer()->set(RequestContainer::class, $requestContainer);

        // Define default locale
        $this->config['site.locales.default'] = 'fr_FR';

        // Assertions
        $locale = $this->getService(SiteLocale::class);
        $this->assertSame('fr_FR', $locale->getLocaleIdentifier());
    }

    public function testGetLocaleIdentifierWithBrowserAndNonExistingLocale(): void
    {
        // Define mock
        $request = m::mock(\Psr\Http\Message\ServerRequestInterface::class);
        $request->shouldReceive('hasHeader')->with('Accept-Language')->once()->andReturn(true);
        $request->shouldReceive('getHeaderLine')->with('Accept-Language')->once()->andReturn('fr-ca');
        $requestContainer = new RequestContainer();
        $requestContainer->setRequest($request);
        $this->getContainer()->set(RequestContainer::class, $requestContainer);

        // Define default locale
        $this->config['site.locales.default'] = 'fr_FR';

        // Assertions
        $locale = $this->getService(SiteLocale::class);
        $this->assertSame('fr_FR', $locale->getLocaleIdentifier());
    }

    public function testMiddlewareWithSimulatedBrowserLocaleControl(): void
    {
        // Define default locale
        $this->config['site.locales.default'] = 'fr_FR';

        // Add test route
        $this->getApp()->get('/testMiddlewareWithSimulatedBrowserLocale', ControllerStub::class);

        // Create request with method and url and fetch response
        $request = $this->createRequest('GET', '/testMiddlewareWithSimulatedBrowserLocale');
        $response = $this->handleRequest($request);

        // Asserts
        $this->assertSame(200, $response->getStatusCode());
        $this->assertResponse('fr_FR', $response);
    }

    /**
     * @depends testMiddlewareWithSimulatedBrowserLocaleControl
     */
    public function testMiddlewareWithSimulatedBrowserLocale(): void
    {
        // Define default locale
        $this->config['site.locales.default'] = 'fr_FR';

        // Add test route
        $this->getApp()->get('/testMiddlewareWithSimulatedBrowserLocale', ControllerStub::class);

        // Create request with method and url and fetch response
        $request = $this->createRequest('GET', '/testMiddlewareWithSimulatedBrowserLocale');
        $request = $request->withHeader('Accept-Language', 'en-US');
        $response = $this->handleRequest($request);

        // Asserts
        $this->assertSame(200, $response->getStatusCode());
        $this->assertResponse('en_US', $response);
    }
}

class ControllerStub
{
    public function __invoke(Response $response, SiteLocaleInterface $siteLocale): Response
    {
        $response->getBody()->write($siteLocale->getLocaleIdentifier());

        return $response;
    }
}
