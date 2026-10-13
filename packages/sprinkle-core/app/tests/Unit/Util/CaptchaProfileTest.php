<?php

declare(strict_types=1);

namespace UserFrosting\Sprinkle\Core\Tests\Unit\Util;

use PHPUnit\Framework\TestCase;
use UserFrosting\Sprinkle\Core\Util\CaptchaProfile;

class CaptchaProfileTest extends TestCase
{
    public function testResolvesDefaults(): void
    {
        $profile = CaptchaProfile::fromArray([]);

        $this->assertSame('captcha', $profile->key);
        $this->assertSame(300, $profile->ttl);
        $this->assertSame(5, $profile->maxAttempts);
        $this->assertSame(5, $profile->lockSeconds);
        $this->assertSame('uf_captcha', $profile->cookieName);
        $this->assertSame('/', $profile->cookiePath);
        $this->assertSame('', $profile->cookieDomain);
        $this->assertFalse($profile->cookieSecure);
        $this->assertTrue($profile->cookieHttpOnly);
        $this->assertSame('Lax', $profile->cookieSameSite);
        $this->assertSame(0, $profile->cookieMaxAge);
    }

    public function testResolvesConfiguredValues(): void
    {
        $profile = CaptchaProfile::fromArray([
            'key'          => 'registration',
            'ttl'          => 60,
            'max_attempts' => 3,
            'lock_seconds' => 10,
            'cookie'       => [
                'name'      => 'registration_captcha',
                'path'      => '/account',
                'domain'    => 'example.test',
                'secure'    => true,
                'http_only' => false,
                'same_site' => 'Strict',
                'max_age'   => 600,
            ],
        ]);

        $this->assertSame('registration', $profile->key);
        $this->assertSame(60, $profile->ttl);
        $this->assertSame(3, $profile->maxAttempts);
        $this->assertSame(10, $profile->lockSeconds);
        $this->assertSame('registration_captcha', $profile->cookieName);
        $this->assertSame('/account', $profile->cookiePath);
        $this->assertSame('example.test', $profile->cookieDomain);
        $this->assertTrue($profile->cookieSecure);
        $this->assertFalse($profile->cookieHttpOnly);
        $this->assertSame('Strict', $profile->cookieSameSite);
        $this->assertSame(600, $profile->cookieMaxAge);
    }

    public function testNormalizesInvalidAndTooSmallValues(): void
    {
        $profile = CaptchaProfile::fromArray([
            'key'          => 123,
            'ttl'          => 0,
            'max_attempts' => 0,
            'lock_seconds' => 0,
            'cookie'       => 'invalid',
        ]);

        $this->assertSame('captcha', $profile->key);
        $this->assertSame(1, $profile->ttl);
        $this->assertSame(1, $profile->maxAttempts);
        $this->assertSame(1, $profile->lockSeconds);
        $this->assertSame('uf_captcha', $profile->cookieName);
    }

    public function testIgnoresInvalidCookieValues(): void
    {
        $profile = CaptchaProfile::fromArray([
            'cookie' => [
                'name'      => 123,
                'path'      => 123,
                'domain'    => 123,
                'secure'    => 'true',
                'http_only' => 1,
                'same_site' => 123,
                'max_age'   => -1,
            ],
        ]);

        $this->assertSame('uf_captcha', $profile->cookieName);
        $this->assertSame('/', $profile->cookiePath);
        $this->assertSame('', $profile->cookieDomain);
        $this->assertFalse($profile->cookieSecure);
        $this->assertTrue($profile->cookieHttpOnly);
        $this->assertSame('Lax', $profile->cookieSameSite);
        $this->assertSame(0, $profile->cookieMaxAge);
    }
}
