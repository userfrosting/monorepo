<?php

/*
 * UserFrosting Framework (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/framework
 * @copyright Copyright (c) 2013-2024 Alexander Weissman, Louis Charette, Jordan Mele
 * @license   https://github.com/userfrosting/framework/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests;

use Psr\Http\Message\ServerRequestInterface;
use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Testing\TestCase;
use UserFrosting\Tests\TestSprinkle\TestServicesProviders;
use UserFrosting\Tests\TestSprinkle\TestSprinkle;
use UserFrosting\UserFrosting;

/**
 * Tests a UserFrosting install with a modular/dependent Sprinkle.
 */
class ModularTest extends TestCase
{
    protected string $mainSprinkle = ChildServiceSprinkleStub::class;

    /**
     * Test ServerRequestInterface is properly created.
     */
    public function testServiceOverwritten(): void
    {
        $this->assertSame('blah', $this->getContainer()->get('testMessageGenerator'));
        $this->assertSame('bar', $this->getContainer()->get('foo'));
    }
}

class ServiceSprinkleStub extends TestSprinkle
{
    public function getServices(): array
    {
        return [
            TestServicesProviders::class,
        ];
    }
}

class ChildServiceSprinkleStub extends TestSprinkle
{
    public function getServices(): array
    {
        return [
            OverwriteTestServicesProviders::class,
            OtherTestServicesProviders::class,
        ];
    }

    public function getSprinkles(): array
    {
        return [
            ServiceSprinkleStub::class,
        ];
    }
}

/**
 * Will overwrite the TestServicesProviders definition.
 */
class OverwriteTestServicesProviders implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            'testMessageGenerator' => 'blah',
        ];
    }
}

/**
 * Second definition, will be added.
 */
class OtherTestServicesProviders implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            'foo' => 'bar',
        ];
    }
}
