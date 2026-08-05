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
use UserFrosting\Sprinkle\Core\Util\RequestContainer;

/**
 * Helper methods for the locale system.
 */
class SiteLocale implements SiteLocaleInterface
{
    /**
     * @param Config           $config
     * @param RequestContainer $request
     */
    public function __construct(
        protected Config $config,
        protected RequestContainer $request,
    ) {
    }

    /**
     * Returns the list of available locale, as defined in the config.
     * Return the list as an array of \UserFrosting\I18n\Locale instances.
     *
     * @return Locale[]
     */
    public function getAvailable(): array
    {
        $locales = [];

        foreach ($this->getAvailableIdentifiers() as $identifier) {
            $locales[] = new Locale($identifier);
        }

        return $locales;
    }

    /**
     * Check if a locale identifier is available in the config.
     *
     * @param string $identifier
     *
     * @return bool
     */
    public function isAvailable(string $identifier): bool
    {
        return in_array($identifier, $this->getAvailableIdentifiers(), true);
    }

    /**
     * Returns the list of available locale, as defined in the config.
     * Formatted as an array that can be used to populate an HTML select element.
     * Keys are identifier, and value is the locale name, eg. `fr_FR => French (Français)`.
     *
     * @return string[]
     */
    public function getAvailableOptions(): array
    {
        $options = [];

        foreach ($this->getAvailable() as $locale) {
            $options[$locale->getIdentifier()] = $locale->getRegionalName();
        }

        // Sort the options by name before returning it
        asort($options);

        return $options;
    }

    /**
     * Returns the list of available locales identifiers (string), as defined in the config.
     * The default locale will always be added in the available list.
     *
     * @return string[] Array of locale identifiers
     */
    public function getAvailableIdentifiers(): array
    {
        // Get all keys where value is true
        $config = $this->config->getArray('site.locales.available', []);
        $available = array_filter($config, fn ($value) => $value !== false && $value !== null && $value !== '');

        // Add the default to the list. it will always be available
        $default = $this->getDefaultLocale();
        $available = array_keys($available); // Keep only keys
        $available = array_merge($available, [$default]); // Add default to list
        $available = array_unique($available); // Remove duplicates, as a result of adding the default

        return $available;
    }

    /**
     * Returns the default locale from the config.
     *
     * @param string $fallback Fallback locale to use if no default or empty string is set in the config
     *
     * @return string
     */
    public function getDefaultLocale(string $fallback = 'en_US'): string
    {
        $defaultIdentifier = $this->config->getString('site.locales.default', $fallback);

        // Make sure the locale config is a valid string. Otherwise, fallback to en_US
        if ($defaultIdentifier === '') {
            return $fallback;
        }

        return $defaultIdentifier;
    }

    /**
     * Returns the locale identifier (ie. en_US) to use.
     *
     * @return string Locale identifier
     */
    public function getLocaleIdentifier(): string
    {
        // Get default locales as specified in configurations.
        $browserLocale = $this->getBrowserLocale();
        if (!is_null($browserLocale)) {
            $localeIdentifier = $browserLocale;
        } else {
            $localeIdentifier = $this->getDefaultLocale();
        }

        return $localeIdentifier;
    }

    /**
     * Returns the Locale instance for the current locale identifier.
     *
     * @return Locale
     */
    public function getLocale(): Locale
    {
        return new Locale($this->getLocaleIdentifier());
    }

    /**
     * Returns the locale configuration for the current locale identifier.
     *
     * @return mixed[]
     */
    public function getLocaleConfig(): array
    {
        return $this->getLocale()->getConfig();
    }

    /**
     * Return the browser locale.
     *
     * @return string|null Returns null if no valid locale can be found
     */
    protected function getBrowserLocale(): ?string
    {
        // Stop if there's no request
        if (is_null($request = $this->request->getRequest())) {
            return null;
        }

        // Stop if request doesn't have the header
        if (!$request->hasHeader('Accept-Language')) {
            return null;
        }

        // Get available locales
        $availableLocales = $this->getAvailableIdentifiers();

        $foundLocales = [];

        // Split all locales returned by the header
        $acceptLanguage = explode(',', $request->getHeaderLine('Accept-Language'));

        foreach ($acceptLanguage as $index => $browserLocale) {
            // Split to access locale & "q"
            $parts = explode(';', $browserLocale);

            // Format locale for UF's i18n
            $identifier = trim(str_replace('-', '_', $parts[0]));

            // Ensure locale available
            $localeIndex = array_search(strtolower($identifier), array_map('strtolower', $availableLocales), true);

            if ($localeIndex !== false) {
                $matchedLocale = $availableLocales[$localeIndex];

                // Determine preference level (q=0.x), and add to $foundLocales
                // If no preference level, set as 1
                if (isset($parts[1])) {
                    $preference = str_replace('q=', '', $parts[1]);
                    $preference = (float) $preference; // Sanitize with int cast (bad values go to 0)
                } else {
                    $preference = 1;
                }

                // Add to list, and format for UF's i18n.
                $foundLocales[$matchedLocale] = $preference;
            }
        }

        // if no $foundLocales, return null
        if (count($foundLocales) === 0) {
            return null;
        }

        // Sort by preference (value)
        arsort($foundLocales, SORT_NUMERIC);

        // Return first element
        return array_key_first($foundLocales);
    }
}
