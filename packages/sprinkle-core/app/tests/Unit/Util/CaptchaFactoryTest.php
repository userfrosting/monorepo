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

use Illuminate\Cache\Repository as Cache;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Response;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Core\Util\Captcha;
use UserFrosting\Sprinkle\Core\Util\CaptchaFactory;

class CaptchaFactoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testRejectsInvalidCaptchaProfile(): void
    {
        $factory = $this->createFactory();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Captcha profile 'comments profile' is invalid.");
        $factory->forProfile('comments profile');
    }

    public function testCreatesCaptchaFromProfileIdentifier(): void
    {
        /** @var Mockery\MockInterface&Config $config */
        $config = Mockery::mock(Config::class)
            ->shouldReceive('getArray')->with('captcha.defaults', [])->once()->andReturn([
                'cookie' => [
                    'path'      => '/default',
                    'http_only' => true,
                ],
            ])
            ->getMock();
        $factory = new CaptchaFactory(Mockery::mock(Cache::class), $config);

        $captcha = $factory->forProfile('account.registration');

        $this->assertInstanceOf(Captcha::class, $captcha);
        $this->assertSame('account.registration', $captcha->getCookieName());
        $cookie = $captcha->withCookie(new Response())->getHeaderLine('Set-Cookie');
        $this->assertStringContainsString('Path=/default', $cookie);
    }

    protected function createFactory(): CaptchaFactory
    {
        return new CaptchaFactory(Mockery::mock(Cache::class), Mockery::mock(Config::class));
    }
}
