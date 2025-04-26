<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Twig\Extensions;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFilter;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Core\Util\Util;

/**
 * Core Twig Extensions.
 */
class CoreExtension extends AbstractExtension implements GlobalsInterface
{
    /**
     * @param Config $config The config service
     */
    public function __construct(
        protected Config $config,
    ) {
    }

    /**
     * Adds Twig filters `unescape`.
     *
     * @return TwigFilter[]
     */
    public function getFilters()
    {
        return [
            /*
             * Converts phone numbers to a standard format.
             *
             * @param   string   $num   A unformatted phone number
             * @return  string   Returns the formatted phone number
             */
            new TwigFilter('phone', function ($num) {
                return Util::formatPhoneNumber($num);
            }),
            new TwigFilter('unescape', function ($string) {
                return html_entity_decode($string);
            }),
        ];
    }

    /**
     * Adds Twig global variables `site`.
     *
     * @return array<string, mixed>
     */
    public function getGlobals(): array
    {
        return [
            'site' => $this->config->get('site'),
        ];
    }
}
