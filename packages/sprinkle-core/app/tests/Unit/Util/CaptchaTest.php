<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit\Util;

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository as Cache;
use Illuminate\Contracts\Cache\Store;
use Mockery;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Response;
use UserFrosting\Sprinkle\Core\Util\Captcha;
use UserFrosting\Sprinkle\Core\Util\CaptchaProfile;

/**
 * Implements the captcha for user registration.
 */
class CaptchaTest extends TestCase
{
    public function testVerificationRequiresAChallengeId(): void
    {
        $this->assertFalse($this->createCaptcha()->verifyCode('code'));
    }

    public function testVerificationRequiresALockCapableCacheStore(): void
    {
        $cache = new Cache(Mockery::mock(Store::class));
        $captcha = new class($cache, CaptchaProfile::fromArray([])) extends Captcha {
            public function setChallengeId(string $challengeId): void
            {
                $this->challengeId = $challengeId;
            }
        };
        $captcha->setChallengeId('challenge');

        $this->assertFalse($captcha->verifyCode('code'));
    }

    public function testSomething(): void
    {
        // Create captcha
        $captcha = $this->createCaptcha();

        // Assert default state
        $this->assertSame('', $captcha->getCode());
        $this->assertSame('', $captcha->getImage());

        // Generate code
        $challengeId = $captcha->generate();

        // Assert new state
        $this->assertNotSame('', $captcha->getCode());
        $this->assertNotSame('', $captcha->getImage());
        $this->assertSame($challengeId, $captcha->getChallengeId());

        // Assert verifyCode
        $this->assertFalse($captcha->verifyCode('bar', $challengeId));
        $this->assertTrue($captcha->verifyCode($captcha->getCode(), $challengeId));
        $this->assertFalse($captcha->verifyCode($captcha->getCode(), $challengeId));
    }

    public function testGenerateImageUsesDefaultColorsWhenAllocationFails(): void
    {
        $cache = new Cache(new ArrayStore());
        $captcha = new class($cache, CaptchaProfile::fromArray([])) extends Captcha {
            protected function allocateColor(\GdImage $image, int $red, int $green, int $blue): int|false
            {
                return false;
            }
        };

        $captcha->generate();

        $this->assertNotSame('', $captcha->getImage());
    }

    public function testCaptchaChallengeIsInvalidatedAfterMaximumAttempts(): void
    {
        $cache = new Cache(new ArrayStore());
        $captcha = new Captcha($cache, CaptchaProfile::fromArray(['max_attempts' => 1]));
        $challengeId = $captcha->generate();

        $this->assertFalse($captcha->verifyCode('wrong', $challengeId));
        $this->assertFalse($captcha->verifyCode($captcha->getCode(), $challengeId));
    }

    public function testVerificationFailsWhenTheChallengeLockIsAlreadyHeld(): void
    {
        $store = new ArrayStore();
        $cache = new Cache($store);
        $captcha = new Captcha($cache, CaptchaProfile::fromArray([]));
        $challengeId = $captcha->generate();
        $lock = $store->lock('captcha:lock:' . hash('sha256', $challengeId), 5);

        $this->assertTrue($lock->get());
        $this->assertFalse($captcha->verifyCode($captcha->getCode(), $challengeId));
        $lock->release();
    }

    public function testVerificationFailsForMissingChallengeRecord(): void
    {
        $cache = new Cache(new ArrayStore());
        $captcha = new Captcha($cache, CaptchaProfile::fromArray([]));
        $challengeId = $captcha->generate();
        $cache->forget('captcha:' . hash('sha256', $challengeId));

        $this->assertFalse($captcha->verifyCode('code', $challengeId));
    }

    public function testVerificationFailsForMalformedChallengeRecord(): void
    {
        $cache = new Cache(new ArrayStore());
        $captcha = new Captcha($cache, CaptchaProfile::fromArray([]));
        $challengeId = $captcha->generate();
        $cache->put('captcha:' . hash('sha256', $challengeId), ['hash' => 'hash'], 300);

        $this->assertFalse($captcha->verifyCode('code', $challengeId));
    }

    public function testExpiredChallengeIsRemoved(): void
    {
        $cache = new Cache(new ArrayStore());
        $captcha = new Captcha($cache, CaptchaProfile::fromArray([]));
        $challengeId = $captcha->generate();
        $key = 'captcha:' . hash('sha256', $challengeId);
        $cache->put($key, [
            'hash'       => password_hash('code', PASSWORD_DEFAULT),
            'attempts'   => 0,
            'expires_at' => time() - 1,
        ], 300);

        $this->assertFalse($captcha->verifyCode('code', $challengeId));
        $this->assertFalse($cache->has($key));
    }

    public function testChallengeWithMaximumAttemptsIsRemoved(): void
    {
        $cache = new Cache(new ArrayStore());
        $captcha = new Captcha($cache, CaptchaProfile::fromArray(['max_attempts' => 2]));
        $challengeId = $captcha->generate();
        $key = 'captcha:' . hash('sha256', $challengeId);
        $record = $cache->get($key);
        $record['attempts'] = 2;
        $cache->put($key, $record, 300);

        $this->assertFalse($captcha->verifyCode('code', $challengeId));
        $this->assertFalse($cache->has($key));
    }

    public function testChallengeWithNonStringHashIsRejected(): void
    {
        $cache = new Cache(new ArrayStore());
        $captcha = new Captcha($cache, CaptchaProfile::fromArray([]));
        $challengeId = $captcha->generate();
        $key = 'captcha:' . hash('sha256', $challengeId);
        $cache->put($key, [
            'hash'       => ['invalid'],
            'attempts'   => 0,
            'expires_at' => time() + 300,
        ], 300);

        $this->assertFalse($captcha->verifyCode('code', $challengeId));
        $this->assertFalse($cache->has($key));
    }

    public function testCaptchaAddsConfiguredCookieAttributes(): void
    {
        $captcha = new Captcha(new Cache(new ArrayStore()), CaptchaProfile::fromArray([
            'cookie' => [
                'name'      => 'custom_captcha',
                'path'      => '/comments',
                'domain'    => 'example.test',
                'secure'    => true,
                'http_only' => false,
                'same_site' => 'Strict',
                'max_age'   => 900,
            ],
        ]));
        $captcha->generate();

        $response = $captcha->withCookie(new Response());
        $cookie = $response->getHeaderLine('Set-Cookie');

        $this->assertStringContainsString('custom_captcha=', $cookie);
        $this->assertStringContainsString('Path=/comments', $cookie);
        $this->assertStringContainsString('Domain=example.test', $cookie);
        $this->assertStringContainsString('Secure', $cookie);
        $this->assertStringNotContainsString('HttpOnly', $cookie);
        $this->assertStringContainsString('Max-Age=900', $cookie);
        $this->assertStringContainsString('SameSite=Strict', $cookie);
    }

    public function testCaptchaOmitsUnsetCookieAttributes(): void
    {
        $captcha = new Captcha(new Cache(new ArrayStore()), CaptchaProfile::fromArray([
            'cookie' => [
                'secure'    => false,
                'http_only' => false,
                'same_site' => '',
                'max_age'   => 0,
            ],
        ]));
        $captcha->generate();

        $cookie = $captcha->withCookie(new Response())->getHeaderLine('Set-Cookie');

        $this->assertStringContainsString('Path=/', $cookie);
        $this->assertStringNotContainsString('Domain=', $cookie);
        $this->assertStringNotContainsString('Secure', $cookie);
        $this->assertStringNotContainsString('HttpOnly', $cookie);
        $this->assertStringNotContainsString('Max-Age=', $cookie);
        $this->assertStringNotContainsString('SameSite=', $cookie);
    }

    protected function createCaptcha(): Captcha
    {
        return new Captcha(new Cache(new ArrayStore()), CaptchaProfile::fromArray([]));
    }
}
