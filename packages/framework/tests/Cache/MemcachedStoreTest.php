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
use UserFrosting\Cache\MemcachedStore;

/**
 * @requires extension Memcached
 */
class MemcachedStoreTest extends StoreTestCase
{
    private static bool $memcachedAvailabilityChecked = false;
    private static ?string $memcachedUnavailableReason = null;
    protected Cache $cache;

    protected function setUp(): void
    {
        parent::setUp();

        if (!self::$memcachedAvailabilityChecked) {
            $cache = $this->createStore();
            $this->setConnectionTimeout($cache);
            $connected = false;

            try {
                $cache->put('__memcached_connection_check__', 'connected', 1);
                $connected = $cache->get('__memcached_connection_check__') === 'connected';
            } catch (\Throwable $exception) {
                self::$memcachedUnavailableReason = $exception->getMessage();
            }

            if (self::$memcachedUnavailableReason === null && !$connected) {
                self::$memcachedUnavailableReason = 'Memcached server is unavailable.';
            }

            self::$memcachedAvailabilityChecked = true;
        }

        if (self::$memcachedUnavailableReason !== null) {
            $this->markTestSkipped('Memcached is unavailable: ' . self::$memcachedUnavailableReason);
        }

        $this->cache = $this->createStore();
        $this->setConnectionTimeout($this->cache);
    }

    private function setConnectionTimeout(Cache $cache): void
    {
        $store = $cache->getStore();
        if (!$store instanceof \Illuminate\Cache\MemcachedStore) {
            throw new \LogicException('Expected an Illuminate Memcached cache store.');
        }

        $store->getMemcached()->setOption(\Memcached::OPT_CONNECT_TIMEOUT, 1000);
    }

    /** {@inheritdoc} */
    protected function createStore(): Cache
    {
        $host = env('MEMCACHED_HOST', '127.0.0.1');
        $cacheStore = new MemcachedStore($host === false || $host === '' ? [] : ['host' => $host]);

        return $cacheStore->instance();
    }

    /**
     * Test memcached store.
     */
    public function testMemcachedStore(): void
    {
        // Store "foo" and try to read it
        $this->cache->forever('foo', 'memcached bar');
        $this->assertEquals('memcached bar', $this->cache->get('foo'));
    }

    public function testMemcachedStorePersistence(): void
    {
        // Doesn't store anything, just tried to read the last one
        $this->assertEquals('memcached bar', $this->cache->get('foo'));
    }

    public function testMultipleMemcachedStore(): void
    {
        // Store stuff in first
        $this->cache->tags('global')->forever('test', '1234');
        $this->cache->tags('global')->forever('foo', 'bar');
        $this->cache->tags('global')->forever('cities', ['Montréal', 'Paris', 'NYC']);

        // Store stuff in second
        $this->cache->tags('user')->forever('test', '1234');
        $this->cache->tags('user')->forever('foo', 'BARRRRRRRRE');
        $this->cache->tags('user')->forever('cities', ['Montréal', 'Paris', 'NYC']);

        // Flush first
        $this->cache->tags('global')->flush();

        // First show be empty, but not the second one
        $this->assertEquals(null, $this->cache->tags('global')->get('foo'));
        $this->assertEquals('BARRRRRRRRE', $this->cache->tags('user')->get('foo'));
    }

    public function testMultipleMemcachedStoreWithTags(): void
    {
        // Store stuff in first
        $this->cache->tags(['foo', 'red'])->forever('bar', 'red');

        // Store stuff in second
        $this->cache->tags(['foo', 'blue'])->forever('bar', 'blue');

        // Flush first
        $this->cache->tags('red')->flush();

        // First show be empty, but not the second one
        $this->assertEquals(null, $this->cache->tags(['foo', 'red'])->get('bar'));
        $this->assertEquals('blue', $this->cache->tags(['foo', 'blue'])->get('bar'));
    }

    public function testTagsFlush(): void
    {
        // Start by not using tags
        $this->cache->put('test', '123', 60);
        $this->assertEquals('123', $this->cache->get('test'));
        $this->assertTrue($this->cache->flush()); // @phpstan-ignore-line False positive.
        $this->assertNull($this->cache->get('test'));

        // Try again with tags
        $this->cache->tags('blah')->put('blah', '321', 60);
        $this->assertEquals('321', $this->cache->tags('blah')->get('blah'));
        $this->cache->tags('blah')->flush(); // $this->assertTrue($this->cache->tags('blah')->flush()); <-- Returns null pre 5.7
        $this->assertNull($this->cache->tags('blah')->get('blah'));
    }
}
