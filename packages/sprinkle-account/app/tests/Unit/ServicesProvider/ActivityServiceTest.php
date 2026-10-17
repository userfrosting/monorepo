<?php

declare(strict_types=1);

namespace UserFrosting\Sprinkle\Account\Tests\Unit\ServicesProvider;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use UserFrosting\Sprinkle\Account\Log\ActivityTypeRegistryInterface;
use UserFrosting\Sprinkle\Account\Log\SprinkleActivityTypeRegistry;
use UserFrosting\Sprinkle\Account\ServicesProvider\ActivityService;
use UserFrosting\Sprinkle\SprinkleManager;
use UserFrosting\Testing\ContainerStub;

class ActivityServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testRegistryIsRegistered(): void
    {
        $container = ContainerStub::create((new ActivityService())->register());
        $manager = Mockery::mock(SprinkleManager::class);
        $container->set(SprinkleManager::class, $manager);

        $this->assertInstanceOf(SprinkleActivityTypeRegistry::class, $container->get(ActivityTypeRegistryInterface::class));
    }
}
