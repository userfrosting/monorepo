<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit\ServicesProvider;

use DI\Container;
use Illuminate\Cache\Repository as Cache;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Core\ServicesProvider\CaptchaService;
use UserFrosting\Sprinkle\Core\Util\CaptchaFactory;
use UserFrosting\Sprinkle\Core\Util\CaptchaFactoryInterface;
use UserFrosting\Testing\ContainerStub;

class CaptchaServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected Container $container;

    public function setUp(): void
    {
        parent::setUp();

        $this->container = ContainerStub::create((new CaptchaService())->register());
    }

    public function testRegistersCaptchaFactoryService(): void
    {
        $this->container->set(Config::class, Mockery::mock(Config::class));
        $this->container->set(Cache::class, Mockery::mock(Cache::class));

        $this->assertInstanceOf(CaptchaFactory::class, $this->container->get(CaptchaFactoryInterface::class));
    }
}
