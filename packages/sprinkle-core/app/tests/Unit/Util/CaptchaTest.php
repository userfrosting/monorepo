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

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use UserFrosting\Session\Session;
use UserFrosting\Sprinkle\Core\Util\Captcha;

/**
 * Implements the captcha for user registration.
 */
class CaptchaTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testSomething(): void
    {
        /** @var Mockery\MockInterface&Session */
        $session = Mockery::mock(Session::class)->makePartial();

        // Create captcha
        $captcha = new Captcha($session);

        // Assert Getter/Setter
        $this->assertSame('test.captcha', $captcha->setKey('test.captcha')->getKey());

        // Assert default state
        $this->assertSame('', $captcha->getCaptcha());
        $this->assertSame('', $captcha->getImage());

        // Generate code
        $captcha->generateRandomCode();

        // Assert new state
        $this->assertNotSame('', $captcha->getCaptcha());
        $this->assertNotSame('', $captcha->getImage());

        // Assert verifyCode
        $this->assertFalse($captcha->verifyCode('bar'));
        $this->assertTrue($captcha->verifyCode($captcha->getCaptcha()));
    }
}
