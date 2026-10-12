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

use Illuminate\Cache\Repository as Cache;
use InvalidArgumentException;
use UserFrosting\Config\Config;

class CaptchaFactory implements CaptchaFactoryInterface
{
    public function __construct(
        protected Cache $cache,
        protected Config $config,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function forProfile(string $profile): CaptchaInterface
    {
        if (preg_match('/^[A-Za-z0-9._-]+$/', $profile) !== 1) {
            throw new InvalidArgumentException("Captcha profile '$profile' is invalid.");
        }

        $defaults = $this->config->getArray('captcha.defaults', []);
        $resolvedConfig = array_replace_recursive($defaults, [
            'key'    => $profile,
            'cookie' => ['name' => $profile],
        ]);

        return new Captcha($this->cache, CaptchaProfile::fromArray($resolvedConfig));
    }
}
