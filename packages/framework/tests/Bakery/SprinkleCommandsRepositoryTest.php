<?php

/*
 * UserFrosting Framework (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/framework
 * @copyright Copyright (c) 2013-2024 Alexander Weissman, Louis Charette, Jordan Mele
 * @license   https://github.com/userfrosting/framework/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests\Bakery;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;
use Symfony\Component\Console\Command\Command;
use UserFrosting\Bakery\SprinkleCommandsRepository;
use UserFrosting\Sprinkle\SprinkleManager;
use UserFrosting\Sprinkle\SprinkleRecipe;
use UserFrosting\Support\Exception\BadClassNameException;
use UserFrosting\Support\Exception\BadInstanceOfException;
use UserFrosting\Tests\TestSprinkle\TestSprinkle;

class SprinkleCommandsRepositoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testGetAll(): void
    {
        $command = Mockery::mock(Command::class);

        /** @var Mockery\MockInterface&SprinkleRecipe */
        $sprinkle = Mockery::mock(TestSprinkle::class)
            ->shouldReceive('getBakeryCommands')->once()->andReturn([$command::class])
            ->getMock();

        /** @var Mockery\MockInterface&SprinkleManager */
        $sprinkleManager = Mockery::mock(SprinkleManager::class)
            ->shouldReceive('getSprinkles')->once()->andReturn([$sprinkle])
            ->getMock();

        /** @var Mockery\MockInterface&ContainerInterface */
        $ci = Mockery::mock(ContainerInterface::class)
            ->shouldReceive('get')->with($command::class)->once()->andReturn($command)
            ->getMock();

        $repository = new SprinkleCommandsRepository($sprinkleManager, $ci);
        $classes = $repository->all();

        $this->assertCount(1, $classes);
        $this->assertSame($command, $classes[0]);
    }

    public function testGetAllNoInterface(): void
    {
        /** @var Mockery\MockInterface&SprinkleRecipe */
        $sprinkle = Mockery::mock(SprinkleRecipe::class)
            ->shouldNotReceive('getBakeryCommands')
            ->getMock();

        /** @var Mockery\MockInterface&SprinkleManager */
        $sprinkleManager = Mockery::mock(SprinkleManager::class)
            ->shouldReceive('getSprinkles')->once()->andReturn([$sprinkle])
            ->getMock();

        /** @var Mockery\MockInterface&ContainerInterface */
        $ci = Mockery::mock(ContainerInterface::class)
            ->shouldNotReceive('get')
            ->getMock();

        $repository = new SprinkleCommandsRepository($sprinkleManager, $ci);
        $classes = $repository->all();

        $this->assertCount(0, $classes);
    }

    public function testGetAllWithCommandNotFound(): void
    {
        /** @var Mockery\MockInterface&SprinkleRecipe */
        $sprinkle = Mockery::mock(TestSprinkle::class)
            ->shouldReceive('getBakeryCommands')->once()->andReturn(['/Not/Command'])
            ->getMock();

        /** @var Mockery\MockInterface&SprinkleManager */
        $sprinkleManager = Mockery::mock(SprinkleManager::class)
            ->shouldReceive('getSprinkles')->once()->andReturn([$sprinkle])
            ->getMock();

        /** @var Mockery\MockInterface&ContainerInterface */
        $ci = Mockery::mock(ContainerInterface::class);

        $repository = new SprinkleCommandsRepository($sprinkleManager, $ci);

        $this->expectException(BadClassNameException::class);
        $this->expectExceptionMessage('Bakery command class `/Not/Command` not found.');
        $repository->all();
    }

    public function testGetAllWithCommandWrongInterface(): void
    {
        $command = Mockery::mock(stdClass::class);

        /** @var Mockery\MockInterface&SprinkleRecipe */
        $sprinkle = Mockery::mock(TestSprinkle::class)
            ->shouldReceive('getBakeryCommands')->once()->andReturn([$command::class])
            ->getMock();

        /** @var Mockery\MockInterface&SprinkleManager */
        $sprinkleManager = Mockery::mock(SprinkleManager::class)
            ->shouldReceive('getSprinkles')->once()->andReturn([$sprinkle])
            ->getMock();

        /** @var Mockery\MockInterface&ContainerInterface */
        $ci = Mockery::mock(ContainerInterface::class)
            ->shouldReceive('get')->with($command::class)->once()->andReturn($command)
            ->getMock();

        $repository = new SprinkleCommandsRepository($sprinkleManager, $ci);

        $this->expectException(BadInstanceOfException::class);
        $this->expectExceptionMessage('Bakery command class `' . $command::class . "` doesn't implement Symfony\Component\Console\Command\Command.");
        $repository->all();
    }
}
