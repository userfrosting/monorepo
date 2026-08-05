<?php

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\I18n;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use UserFrosting\Config\Config;
use UserFrosting\I18n\Locale;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\I18n\SiteLocale as AccountSiteLocale;
use UserFrosting\Sprinkle\Account\Tests\AccountTestCase;
use UserFrosting\Sprinkle\Core\I18n\SiteLocale;
use UserFrosting\Sprinkle\Core\I18n\SiteLocaleInterface;

/**
 * Tests SiteLocale.
 *
 * N.B.: This requires the full App stack, since locale files will be loaded.
 */
class SiteLocaleTest extends AccountTestCase
{
    use MockeryPHPUnitIntegration;

    /** @var array<string, bool> */
    protected array $testLocale = [
        'fr_FR' => true,
        'en_US' => true,
    ];

    protected Config $config;

    // Apply fake config
    public function setUp(): void
    {
        parent::setUp();

        /** @var Config */
        $config = $this->getService(Config::class);

        // Set test config
        $config->set('site.locales.available', $this->testLocale);
        $config->set('site.locales.default', 'fr_FR');

        $this->config = $config;
    }

    public function testService(): void
    {
        /** @var SiteLocale */
        $locale = $this->getService(SiteLocaleInterface::class);

        // @phpstan-ignore-next-line Check overwriting is working
        $this->assertInstanceOf(SiteLocaleInterface::class, $locale);
        $this->assertInstanceOf(AccountSiteLocale::class, $locale);
    }

    /**
     * Will return the default locale (fr_FR)
     */
    public function testFallbackWhenNoUser(): void
    {
        /** @var SiteLocale */
        $locale = $this->getService(SiteLocaleInterface::class);

        $this->assertSame('fr_FR', $locale->getLocaleIdentifier());
    }

    /**
     * Will return the default locale (fr_FR)
     */
    public function testFallbackWhenNullUser(): void
    {
        $authenticator = Mockery::mock(Authenticator::class)
            ->shouldReceive('user')->once()->andReturn(null)
            ->getMock();
        $this->getContainer()->set(Authenticator::class, $authenticator);

        /** @var SiteLocale */
        $locale = $this->getService(SiteLocaleInterface::class);

        $this->assertSame('fr_FR', $locale->getLocaleIdentifier());
    }

    /**
     * Will return the USER default locale (en_US)
     */
    public function testWithUser(): void
    {
        $user = User::factory()->make();

        $authenticator = Mockery::mock(Authenticator::class)
            ->shouldReceive('user')->once()->andReturn($user)
            ->getMock();
        $this->getContainer()->set(Authenticator::class, $authenticator);

        /** @var SiteLocale */
        $locale = $this->getService(SiteLocaleInterface::class);

        $this->assertSame('en_US', $locale->getLocaleIdentifier());
    }

    /**
     * Will return the default locale (fr_FR)
     */
    public function testWithUserWithNonAvailableLocale(): void
    {
        $user = User::factory()->make();

        $authenticator = Mockery::mock(Authenticator::class)
            ->shouldReceive('user')->once()->andReturn($user)
            ->getMock();
        $this->getContainer()->set(Authenticator::class, $authenticator);

        // Remove en_US from available locale
        $this->config->set('site.locales.available', ['fr_FR']);

        /** @var SiteLocale */
        $locale = $this->getService(SiteLocaleInterface::class);

        $this->assertSame('fr_FR', $locale->getLocaleIdentifier());
    }
}
