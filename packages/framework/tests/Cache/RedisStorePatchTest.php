<?php

declare(strict_types=1);

namespace UserFrosting\Tests\Cache;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use UserFrosting\Cache\Patch\Redis\RedisStore;

class RedisStorePatchTest extends TestCase
{
    public function testSerializeAndUnserializePreserveValues(): void
    {
        $store = (new ReflectionClass(RedisStore::class))->newInstanceWithoutConstructor();
        $serialize = new ReflectionMethod(RedisStore::class, 'serialize');
        $unserialize = new ReflectionMethod(RedisStore::class, 'unserialize');

        foreach ([0, 'value', ['key' => 'value'], null] as $value) {
            $this->assertSame($value, $unserialize->invoke($store, $serialize->invoke($store, $value)));
        }
    }
}