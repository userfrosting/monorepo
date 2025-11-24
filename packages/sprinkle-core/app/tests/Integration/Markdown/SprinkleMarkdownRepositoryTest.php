<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Markdown;

use League\CommonMark\Extension\ExtensionInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Psr\Container\ContainerInterface;
use UserFrosting\Sprinkle\Core\Markdown\SprinkleMarkdownRepository;
use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\MarkdownExtensionRecipe;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase;
use UserFrosting\Sprinkle\Core\Tests\Integration\Markdown\Fixtures\DummyMarkdownExtension;
use UserFrosting\Sprinkle\SprinkleManager;
use UserFrosting\Support\Exception\BadClassNameException;
use UserFrosting\Support\Exception\BadInstanceOfException;

class SprinkleMarkdownRepositoryTest extends CoreTestCase
{
    use MockeryPHPUnitIntegration;

    public function testAllWithNoSprinkles(): void
    {
        $mockManager = Mockery::mock(SprinkleManager::class);
        $mockManager->shouldReceive('getSprinkles')->once()->andReturn([]);

        $mockContainer = Mockery::mock(ContainerInterface::class);

        $repository = new SprinkleMarkdownRepository($mockManager, $mockContainer);
        $extensions = $repository->all();

        $this->assertEmpty($extensions);
    }

    public function testAllWithSprinkleWithoutRecipe(): void
    {
        $mockSprinkle = Mockery::mock('SprinkleWithoutRecipe');

        $mockManager = Mockery::mock(SprinkleManager::class);
        $mockManager->shouldReceive('getSprinkles')->once()->andReturn([$mockSprinkle]);

        $mockContainer = Mockery::mock(ContainerInterface::class);

        $repository = new SprinkleMarkdownRepository($mockManager, $mockContainer);
        $extensions = $repository->all();

        $this->assertEmpty($extensions);
    }

    public function testAllWithValidExtension(): void
    {
        $mockExtension = new DummyMarkdownExtension();

        $mockSprinkle = Mockery::mock(MarkdownExtensionRecipe::class);
        $mockSprinkle->shouldReceive('getMarkdownExtensions')
            ->once()
            ->andReturn([DummyMarkdownExtension::class]);

        $mockManager = Mockery::mock(SprinkleManager::class);
        $mockManager->shouldReceive('getSprinkles')->once()->andReturn([$mockSprinkle]);

        $mockContainer = Mockery::mock(ContainerInterface::class);
        $mockContainer->shouldReceive('get')
            ->with(DummyMarkdownExtension::class)
            ->once()
            ->andReturn($mockExtension);

        $repository = new SprinkleMarkdownRepository($mockManager, $mockContainer);
        $extensions = $repository->all();

        $this->assertCount(1, $extensions);
        $this->assertInstanceOf(ExtensionInterface::class, $extensions[0]);
        $this->assertInstanceOf(DummyMarkdownExtension::class, $extensions[0]);
    }

    public function testAllWithInvalidClass(): void
    {
        $mockSprinkle = Mockery::mock(MarkdownExtensionRecipe::class);
        $mockSprinkle->shouldReceive('getMarkdownExtensions')
            ->once()
            ->andReturn(['NonExistentClass']);

        $mockManager = Mockery::mock(SprinkleManager::class);
        $mockManager->shouldReceive('getSprinkles')->once()->andReturn([$mockSprinkle]);

        $mockContainer = Mockery::mock(ContainerInterface::class);

        $repository = new SprinkleMarkdownRepository($mockManager, $mockContainer);

        $this->expectException(BadClassNameException::class);
        $this->expectExceptionMessage('Extension class `NonExistentClass` not found.');
        $repository->all();
    }

    public function testAllWithInvalidInstance(): void
    {
        $invalidInstance = new \stdClass();

        $mockSprinkle = Mockery::mock(MarkdownExtensionRecipe::class);
        $mockSprinkle->shouldReceive('getMarkdownExtensions')
            ->once()
            ->andReturn([\stdClass::class]);

        $mockManager = Mockery::mock(SprinkleManager::class);
        $mockManager->shouldReceive('getSprinkles')->once()->andReturn([$mockSprinkle]);

        $mockContainer = Mockery::mock(ContainerInterface::class);
        $mockContainer->shouldReceive('get')
            ->with(\stdClass::class)
            ->once()
            ->andReturn($invalidInstance);

        $repository = new SprinkleMarkdownRepository($mockManager, $mockContainer);

        $this->expectException(BadInstanceOfException::class);
        $this->expectExceptionMessage("Extension class `stdClass` doesn't implement " . ExtensionInterface::class . '.');
        $repository->all();
    }

    public function testIterable(): void
    {
        $mockExtension = new DummyMarkdownExtension();

        $mockSprinkle = Mockery::mock(MarkdownExtensionRecipe::class);
        $mockSprinkle->shouldReceive('getMarkdownExtensions')
            ->once()
            ->andReturn([DummyMarkdownExtension::class]);

        $mockManager = Mockery::mock(SprinkleManager::class);
        $mockManager->shouldReceive('getSprinkles')->once()->andReturn([$mockSprinkle]);

        $mockContainer = Mockery::mock(ContainerInterface::class);
        $mockContainer->shouldReceive('get')
            ->with(DummyMarkdownExtension::class)
            ->once()
            ->andReturn($mockExtension);

        $repository = new SprinkleMarkdownRepository($mockManager, $mockContainer);

        $count = 0;
        foreach ($repository as $extension) {
            $this->assertInstanceOf(ExtensionInterface::class, $extension);
            $count++;
        }

        $this->assertEquals(1, $count);
    }
}
