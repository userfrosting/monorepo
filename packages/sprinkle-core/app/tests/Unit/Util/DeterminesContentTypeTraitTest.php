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
use Psr\Http\Message\ServerRequestInterface;
use UserFrosting\Sprinkle\Core\Util\Captcha;
use UserFrosting\Sprinkle\Core\Util\DeterminesContentTypeTrait;

/**
 * Implements the captcha for user registration.
 */
class DeterminesContentTypeTraitTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testNormal(): void
    {
        $stub = new DeterminesContentTypeTraitStub();

        /** @var Mockery\MockInterface&ServerRequestInterface */
        $request = Mockery::mock(ServerRequestInterface::class)
            ->shouldReceive('getHeaderLine')->once()->with('Accept')->andReturn('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8')
            ->getMock();
        $knownContentTypes = ['application/json', 'application/xml', 'text/xml', 'text/html', 'text/plain'];

        $result = $stub->process($request, $knownContentTypes, 'text/html');
        $this->assertSame('text/html', $result);
    }

    public function testTestPlain(): void
    {
        $stub = new DeterminesContentTypeTraitStub();

        /** @var Mockery\MockInterface&ServerRequestInterface */
        $request = Mockery::mock(ServerRequestInterface::class)
            ->shouldReceive('getHeaderLine')->once()->with('Accept')->andReturn('text/plain,application/html,application/xml')
            ->getMock();
        $knownContentTypes = ['application/json', 'application/xml', 'text/xml', 'text/html', 'text/plain'];

        $result = $stub->process($request, $knownContentTypes, 'text/html');
        $this->assertSame('application/xml', $result); // text/plain is skipped, application/html not in known, so application/xml is the one returned
    }

    public function testTestDefaultMatch(): void
    {
        $stub = new DeterminesContentTypeTraitStub();

        /** @var Mockery\MockInterface&ServerRequestInterface */
        $request = Mockery::mock(ServerRequestInterface::class)
            ->shouldReceive('getHeaderLine')->once()->with('Accept')->andReturn('application/jpeg')
            ->getMock();
        $knownContentTypes = [];

        $result = $stub->process($request, $knownContentTypes, 'text/html');
        $this->assertSame('text/html', $result); // application/jpeg not in known, so default (text/html) is the one returned
    }

    public function testTestPregMatch(): void
    {
        $stub = new DeterminesContentTypeTraitStub();

        /** @var Mockery\MockInterface&ServerRequestInterface */
        $request = Mockery::mock(ServerRequestInterface::class)
            ->shouldReceive('getHeaderLine')->once()->with('Accept')->andReturn('application/xhtml+xml')
            ->getMock();
        $knownContentTypes = ['application/xml'];

        $result = $stub->process($request, $knownContentTypes, 'text/html');
        $this->assertSame('application/xml', $result); // application/xhtml+xml not in known, but application/xml is still a match
    }
}

/**
 * Wrapper for the trait.
 */
class DeterminesContentTypeTraitStub
{
    use DeterminesContentTypeTrait;

    /**
     * @param ServerRequestInterface $request
     * @param string[]               $knownContentTypes
     * @param string                 $defaultType
     *
     * @return string
     */
    public function process(
        ServerRequestInterface $request,
        array $knownContentTypes,
        string $defaultType,
    ): string {
        return $this->determineContentType($request, $knownContentTypes, $defaultType);
    }
}
