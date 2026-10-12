<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\Controller;

use Illuminate\Cache\Repository as Cache;
use UserFrosting\Sprinkle\Account\Tests\AccountTestCase;
use UserFrosting\Sprinkle\Account\Util\CaptchaProfiles;

/**
 * Tests RegistrationCaptchaAction.
 */
class RegistrationCaptchaActionTest extends AccountTestCase
{
    public function testCaptcha(): void
    {
        $request = $this->createJsonRequest('GET', '/account/captcha');
        $response = $this->handleRequest($request);

        // Assert response and cookie headers
        $this->assertResponseStatus(200, $response);
        $this->assertNotSame('', (string) $response->getBody());
        $cookie = $response->getHeaderLine('Set-Cookie');
        $this->assertStringContainsString(CaptchaProfiles::REGISTRATION . '=', $cookie);
        $this->assertStringContainsString('HttpOnly', $cookie);
        $this->assertStringContainsString('SameSite=Lax', $cookie);

        // Assert cache record for the captcha challenge
        [, $challengeId] = explode('=', explode(';', $cookie, 2)[0], 2);
        $cache = $this->getService(Cache::class);
        $record = $cache->get(CaptchaProfiles::REGISTRATION . ':' . hash('sha256', $challengeId));
        $this->assertIsArray($record);
        $this->assertIsString($record['hash'] ?? null);
        $this->assertSame(0, $record['attempts'] ?? null);
        $this->assertGreaterThan(time(), $record['expires_at'] ?? 0);
    }
}
