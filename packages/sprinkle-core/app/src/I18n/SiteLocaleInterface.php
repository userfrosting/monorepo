<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\I18n;

use UserFrosting\Config\Config;
use UserFrosting\I18n\Locale;

/**
 * Helper methods for the locale system.
 */
interface SiteLocaleInterface
{
    /**
     * Returns the list of available locale, as defined in the config.
     * Return the list as an array of \UserFrosting\I18n\Locale instances.
     *
     * @return Locale[]
     */
    public function getAvailable(): array;

    /**
     * Check if a locale identifier is available in the config.
     *
     * @param string $identifier
     *
     * @return bool
     */
    public function isAvailable(string $identifier): bool;

    /**
     * Returns the list of available locale, as defined in the config.
     * Formatted as an array that can be used to populate an HTML select element.
     * Keys are identifier, and value is the locale name, eg. `fr_FR => French (Français)`.
     *
     * @return string[]
     */
    public function getAvailableOptions(): array;

    /**
     * Returns the list of available locales identifiers (string), as defined in the config.
     * The default locale will always be added in the available list.
     *
     * @return string[] Array of locale identifiers
     */
    public function getAvailableIdentifiers(): array;

    /**
     * Returns the default locale from the config.
     *
     * @return string
     */
    public function getDefaultLocale(): string;

    /**
     * Returns the locale identifier (ie. en_US) to use.
     *
     * @return string Locale identifier
     *
     * @todo This should accept the request service as argument, or null, in which case the `getBrowserLocale` method would be skipped
     */
    public function getLocaleIdentifier(): string;

    /**
     * Returns the Locale instance for the current locale identifier.
     *
     * @return Locale
     */
    public function getLocale(): Locale;

    /**
     * Returns the locale configuration for the current locale identifier.
     *
     * @return mixed[]
     */
    public function getLocaleConfig(): array;
}
