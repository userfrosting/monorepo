<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Throttle;

use Carbon\Carbon;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Core\Database\Models\Throttle;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase;
use UserFrosting\Sprinkle\Core\Throttle\Throttler;
use UserFrosting\Sprinkle\Core\Throttle\ThrottlerException;
use UserFrosting\Sprinkle\Core\Throttle\ThrottleRule;

class ThrottlerTest extends CoreTestCase
{
    use MockeryPHPUnitIntegration;
    use RefreshDatabase;

    public function testRule(): void
    {
        $rule = new ThrottleRule('ip', 3600, [2 => 5, 3 => 10]);
        $this->assertSame('ip', $rule->getMethod());
        $this->assertSame(3600, $rule->getInterval());
        $this->assertSame([3 => 10, 2 => 5], $rule->getDelays()); // Sort is applied

        $carbon = Carbon::now();
        $this->assertSame(0, $rule->getDelay($carbon, 0));
        $this->assertSame(0, $rule->getDelay($carbon, 1));
    }

    public function testThrottle(): void
    {
        $this->refreshDatabase();

        $throttler = new Throttler(new Throttle());
        $rule = new ThrottleRule('ip', 3600, [1 => 5, 2 => 10]);
        $throttler->addThrottleRule('test', $rule)->addThrottleRule('bar', null);

        // Assert get rule
        $rules = $throttler->getThrottleRules();
        $this->assertCount(2, $rules);
        $this->assertSame($rule, $rules['test']);
        $this->assertNull($rules['bar']);
        $this->assertNull($throttler->getRule('bar'));

        // Assert null rule
        $this->assertSame(0, $throttler->logEvent('bar')->getDelay('bar'));

        // Assert Rule
        $_SERVER['REMOTE_ADDR'] = '0.0.0.0';
        $this->assertSame(0, $throttler->getDelay('test'));
        $throttler->logEvent('test', ['user_identifier' => 'testUser']);
        $this->assertNotSame(0, $throttler->getDelay('test'));
    }

    public function testThrottleNonIp(): void
    {
        $this->refreshDatabase();

        $throttler = new Throttler(new Throttle());
        $rule = new ThrottleRule('foo', 3600, [1 => 5, 2 => 10]);
        $throttler->addThrottleRule('test', $rule);

        // Assert Rule
        $_SERVER['REMOTE_ADDR'] = '0.0.0.0';
        $data = ['user_identifier' => 'testUser'];
        $this->assertSame(0, $throttler->getDelay('test', $data));
        $throttler->logEvent('test', $data);
        $this->assertNotSame(0, $throttler->getDelay('test', $data));
        $this->assertSame(0, $throttler->getDelay('test', ['foo' => 'bar']));
    }

    public function testGetRuleException(): void
    {
        $throttler = new Throttler(new Throttle());
        $this->expectException(ThrottlerException::class);
        $throttler->getRule('foo');
    }

    public function testService(): void
    {
        $data = [
            'test' => [
                'method'   => 'ip',
                'interval' => 3600,
                'delays'   => [1 => 5, 2 => 10]
            ],
            'bar'  => null,
        ];

        // Mock config
        $config = Mockery::mock(Config::class)
            ->shouldReceive('has')->with('throttles')->once()->andReturn(true)
            ->shouldReceive('get')->with('throttles')->andReturn($data)
            ->getMock();

        $this->ci->set(Config::class, $config);

        $throttler = $this->ci->get(Throttler::class);
        $rules = $throttler->getThrottleRules();
        $this->assertCount(2, $rules);
        $this->assertInstanceOf(ThrottleRule::class, $rules['test']);
        $this->assertNull($rules['bar']);
    }

    /**
     * @dataProvider invalidConfigurationProvider
     *
     * @param mixed $data
     */
    public function testServiceRejectsInvalidConfiguration(mixed $data): void
    {
        $config = Mockery::mock(Config::class)
            ->shouldReceive('has')->with('throttles')->once()->andReturn(true)
            ->shouldReceive('get')->with('throttles')->once()->andReturn($data)
            ->getMock();

        $this->ci->set(Config::class, $config);
        $this->expectException(\InvalidArgumentException::class);

        $this->ci->get(Throttler::class);
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function invalidConfigurationProvider(): array
    {
        return [
            'non-array configuration' => ['invalid'],
            'non-string rule name'    => [[1 => null]],
            'missing rule field'      => [['test' => ['method' => 'ip', 'interval' => 3600]]],
            'invalid rule field type' => [['test' => [
                'method'   => 'ip',
                'interval' => '3600',
                'delays'   => [1 => 5],
            ]]],
            'invalid delay value'     => [['test' => [
                'method'   => 'ip',
                'interval' => 3600,
                'delays'   => ['attempts' => 5],
            ]]],
        ];
    }
}
