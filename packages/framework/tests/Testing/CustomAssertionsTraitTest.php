<?php

declare(strict_types=1);

/*
 * UserFrosting Framework (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/framework
 * @copyright Copyright (c) 2013-2024 Alexander Weissman, Louis Charette, Jordan Mele
 * @license   https://github.com/userfrosting/framework/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests\Unit\Testing;

use InvalidArgumentException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use UserFrosting\Testing\CustomAssertionsTrait;

/**
 * Tests for CustomAssertionsTrait.
 *
 * Run each assertions with code we know is equals to make sure assertions are
 * rights.
 */
class CustomAssertionsTraitTest extends TestCase
{
    use MockeryPHPUnitIntegration;
    use CustomAssertionsTrait;

    protected string $json = '{"result": {"foo":true,"bar":false,"list":["foo","bar"]}}';

    public function testAssertResponse(): void
    {
        /** @var Mockery\MockInterface&StreamInterface $stream */
        $stream = Mockery::mock(StreamInterface::class)
           ->shouldReceive('__toString')->andReturn('foo bar')
           ->getMock();

        /** @var Mockery\MockInterface&ResponseInterface $response */
        $response = Mockery::mock(ResponseInterface::class)
            ->shouldReceive('getBody')->once()->andReturn($stream)
            ->getMock();

        $this->assertResponse('foo bar', $response);
    }

    public function testAssertResponseStatus(): void
    {
        /** @var Mockery\MockInterface&ResponseInterface $response */
        $response = Mockery::mock(ResponseInterface::class)
            ->shouldReceive('getStatusCode')->once()->andReturn(123)
            ->getMock();

        $this->assertResponseStatus(123, $response);
    }

    /** @depends testAssertJsonEquals */
    public function testAssertJsonResponse(): void
    {
        /** @var Mockery\MockInterface&StreamInterface $stream */
        $stream = Mockery::mock(StreamInterface::class)
           ->shouldReceive('__toString')->andReturn($this->json)
           ->getMock();

        /** @var Mockery\MockInterface&ResponseInterface $response */
        $response = Mockery::mock(ResponseInterface::class)
            ->shouldReceive('getBody')->times(2)->andReturn($stream)
            ->getMock();

        $array = ['result' => ['foo' => true, 'bar' => false, 'list' => ['foo', 'bar']]];
        $this->assertJsonResponse($array, $response);
        $this->assertJsonResponse(['foo', 'bar'], $response, 'result.list');
    }

    /** @depends testAssertJsonNotEquals */
    public function testAssertNotJsonResponse(): void
    {
        /** @var Mockery\MockInterface&StreamInterface $stream */
        $stream = Mockery::mock(StreamInterface::class)
           ->shouldReceive('__toString')->andReturn($this->json)
           ->getMock();

        /** @var Mockery\MockInterface&ResponseInterface $response */
        $response = Mockery::mock(ResponseInterface::class)
            ->shouldReceive('getBody')->times(4)->andReturn($stream)
            ->getMock();

        $this->assertNotJsonResponse(['foo'], $response);
        $this->assertNotJsonResponse(['foo'], $response, 'result.list');
        $this->assertJsonNotEquals(['foo'], $response);
        $this->assertJsonNotSame(['foo'], $response);
    }

    public function testAssertJsonEquals(): void
    {
        // N.B.: Array is in the reverse order, which is equals
        $array = ['result' => ['list' => ['foo', 'bar'], 'bar' => false, 'foo' => true]];

        $this->assertJsonEquals($array, $this->json);
        $this->assertJsonEquals(['foo', 'bar'], $this->json, 'result.list');
        $this->assertJsonEquals(true, $this->json, 'result.foo');
    }

    public function testAssertJsonSame(): void
    {
        // N.B.: Array order is important with same
        $array = ['result' => ['foo' => true, 'bar' => false, 'list' => ['foo', 'bar']]];

        $this->assertJsonSame($array, $this->json);
        $this->assertJsonSame(['foo', 'bar'], $this->json, 'result.list');
        $this->assertJsonSame(true, $this->json, 'result.foo');
    }

    public function testAssertJsonNotEquals(): void
    {
        $this->assertJsonNotEquals(['foo'], $this->json);
        $this->assertJsonNotEquals(['foo'], $this->json, 'result.list');
        $this->assertJsonNotEquals(false, $this->json, 'result.foo');
    }

    public function testAssertJsonNotSame(): void
    {
        // Use reversed array
        $array = ['result' => ['list' => ['foo', 'bar'], 'bar' => false, 'foo' => true]];

        $this->assertJsonNotSame($array, $this->json);
        $this->assertJsonNotSame(['foo'], $this->json);
        $this->assertJsonNotSame(['foo'], $this->json, 'result.list');
        $this->assertJsonNotSame(false, $this->json, 'result.foo');
    }

    public function testAssertJsonEqualsAndSameWithResponse(): void
    {
        /** @var Mockery\MockInterface&StreamInterface $stream */
        $stream = Mockery::mock(StreamInterface::class)
           ->shouldReceive('__toString')->andReturn($this->json)
           ->getMock();

        /** @var Mockery\MockInterface&ResponseInterface $response */
        $response = Mockery::mock(ResponseInterface::class)
            ->shouldReceive('getBody')->times(4)->andReturn($stream)
            ->getMock();

        // N.B.: Array is in the reverse order, which is equals
        $array = ['result' => ['list' => ['foo', 'bar'], 'bar' => false, 'foo' => true]];

        $this->assertJsonEquals($array, $response);
        $this->assertJsonEquals(['foo', 'bar'], $response, 'result.list');
        $this->assertJsonEquals(true, $response, 'result.foo');

        // N.B.: Array order is important with same. Change and retest same.
        $array = ['result' => ['foo' => true, 'bar' => false, 'list' => ['foo', 'bar']]];
        $this->assertJsonSame($array, $response);
    }

    public function testAssertJsonStructure(): void
    {
        $this->assertJsonStructure(['result'], $this->json);
        $this->assertJsonStructure(['foo', 'bar', 'list'], $this->json, 'result');
    }

    public function testAssertJsonStructureWithResponse(): void
    {
        /** @var Mockery\MockInterface&StreamInterface $stream */
        $stream = Mockery::mock(StreamInterface::class)
           ->shouldReceive('__toString')->andReturn($this->json)
           ->getMock();

        /** @var Mockery\MockInterface&ResponseInterface $response */
        $response = Mockery::mock(ResponseInterface::class)
            ->shouldReceive('getBody')->times(2)->andReturn($stream)
            ->getMock();

        $this->assertJsonStructure(['result'], $response);
        $this->assertJsonStructure(['foo', 'bar', 'list'], $response, 'result');
    }

    public function testAssertJsonStructureWithError(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->assertJsonStructure([true], $this->json, 'result.foo');
    }

    public function testAssertJsonCount(): void
    {
        $this->assertJsonCount(1, $this->json);
        $this->assertJsonCount(3, $this->json, 'result');
        $this->assertJsonCount(2, $this->json, 'result.list');
    }

    public function testAssertJsonCountWithResponse(): void
    {
        /** @var Mockery\MockInterface&StreamInterface $stream */
        $stream = Mockery::mock(StreamInterface::class)
           ->shouldReceive('__toString')->andReturn($this->json)
           ->getMock();

        /** @var Mockery\MockInterface&ResponseInterface $response */
        $response = Mockery::mock(ResponseInterface::class)
            ->shouldReceive('getBody')->times(3)->andReturn($stream)
            ->getMock();

        $this->assertJsonCount(1, $response);
        $this->assertJsonCount(3, $response, 'result');
        $this->assertJsonCount(2, $response, 'result.list');
    }

    public function testAssertJsonCountWithError(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->assertJsonCount(10, $this->json, 'result.foo');
    }

    public function testAssertHtmlTagCount(): void
    {
        $html = '<html><div>One</div><div>Two</div><span>Not You</span><div>Three</div></html>';
        $this->assertHtmlTagCount(3, $html, 'div');
        $this->assertHtmlTagCount(1, $html, 'html');
        $this->assertHtmlTagCount(1, $html, 'span');
        $this->assertHtmlTagCount(0, $html, 'p');
    }

    public function testAssertHtmlTagCountWithResponse(): void
    {
        $html = '<html><div>One</div><div>Two</div><span>Not You</span><div>Three</div></html>';

        /** @var Mockery\MockInterface&StreamInterface $stream */
        $stream = Mockery::mock(StreamInterface::class)
           ->shouldReceive('__toString')->andReturn($html)
           ->getMock();

        /** @var Mockery\MockInterface&ResponseInterface $response */
        $response = Mockery::mock(ResponseInterface::class)
            ->shouldReceive('getBody')->times(4)->andReturn($stream)
            ->getMock();

        $this->assertHtmlTagCount(3, $response, 'div');
        $this->assertHtmlTagCount(1, $response, 'html');
        $this->assertHtmlTagCount(1, $response, 'span');
        $this->assertHtmlTagCount(0, $response, 'p');
    }
}
