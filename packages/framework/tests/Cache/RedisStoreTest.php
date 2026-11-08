<?php

declare(strict_types=1);

/*
 * UserFrosting Framework (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/framework
 * @copyright Copyright (c) 2013-2024 Alexander Weissman, Louis Charette, Jordan Mele
 * @license   https://github.com/userfrosting/framework/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests\Cache;

use Illuminate\Cache\Repository as Cache;
use Predis\Client;
use Predis\Connection\ConnectionException;
use UserFrosting\Cache\RedisStore;

/**
 * @requires extension redis
 */
class RedisStoreTest extends StoreTestCase
{
    private static bool $redisAvailabilityChecked = false;
    private static ?string $redisUnavailableReason = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (!self::$redisAvailabilityChecked) {
            $host = env('REDIS_HOST', '127.0.0.1');
            $client = new Client([
                'host'    => $host === false || $host === '' ? '127.0.0.1' : $host,
                'port'    => 6379,
                'timeout' => 0.2,
            ]);

            try {
                $client->ping();
            } catch (ConnectionException $exception) {
                self::$redisUnavailableReason = $exception->getMessage();
            }

            self::$redisAvailabilityChecked = true;
        }

        if (self::$redisUnavailableReason !== null) {
            $this->markTestSkipped('Redis server is unavailable: ' . self::$redisUnavailableReason);
        }
    }

    /** {@inheritdoc} */
    protected function createStore(): Cache
    {
        // Create $cache with default config in CI
        $host = env('REDIS_HOST', '127.0.0.1');
        $cacheStore = new RedisStore($host === false || $host === '' ? [] : ['host' => $host]);

        return $cacheStore->instance();
    }

    /**
     * Test redis store.
     */
    public function testRedisStore(): void
    {
        $cache = $this->createStore();

        // Store "foo" and try to read it
        $cache->forever('foo', 'Redis bar');
        $this->assertEquals('Redis bar', $cache->get('foo'));
    }

    public function testRedisStorePersistence(): void
    {
        $cache = $this->createStore();

        // Doesn't store anything, just tried to read the last one
        $this->assertEquals('Redis bar', $cache->get('foo'));
    }

    public function testMultipleRedisStore(): void
    {
        $cache = $this->createStore();

        // Store stuff in first
        $cache->tags('global')->forever('test', '1234');
        $cache->tags('global')->forever('foo', 'bar');
        $cache->tags('global')->forever('cities', ['Montréal', 'Paris', 'NYC']);

        // Store stuff in second
        $cache->tags('user')->forever('test', '1234');
        $cache->tags('user')->forever('foo', 'BARRRRRRRRE');
        $cache->tags('user')->forever('cities', ['Montréal', 'Paris', 'NYC']);

        // Flush first
        $cache->tags('global')->flush();

        // First show be empty, but not the second one
        $this->assertEquals(null, $cache->tags('global')->get('foo'));
        $this->assertEquals('BARRRRRRRRE', $cache->tags('user')->get('foo'));
    }

    public function testTagsFlush(): void
    {
        $cache = $this->createStore();

        // Start by not using tags
        $cache->put('test', '123', 60);
        $this->assertEquals('123', $cache->get('test'));
        $this->assertTrue($cache->flush()); // @phpstan-ignore-line False positive.
        $this->assertNull($cache->get('test'));

        // Try again with tags
        $cache->tags('blah')->put('blah', '321', 60);
        $this->assertEquals('321', $cache->tags('blah')->get('blah'));
        $cache->tags('blah')->flush(); // $this->assertTrue($cache->tags('blah')->flush()); <-- Returns null pre 5.7
        $this->assertNull($cache->tags('blah')->get('blah'));
    }
}
