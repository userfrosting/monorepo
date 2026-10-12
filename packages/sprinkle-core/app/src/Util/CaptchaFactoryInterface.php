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

interface CaptchaFactoryInterface
{
    /**
     * Create a captcha service configured for a named profile.
     *
     * @param string $profile The configured captcha profile name.
     *
     * @return CaptchaInterface
     */
    public function forProfile(string $profile): CaptchaInterface;
}
