<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Session;

use Illuminate\Database\Connection;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Session\DatabaseSessionHandler;
use UserFrosting\Config\Config;
use UserFrosting\Session\Session;
use UserFrosting\Sprinkle\Core\Database\Models\Session as SessionTable;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase as TestCase;

/**
 * Integration tests for the session service.
 */
class SessionDatabaseHandlerTest extends TestCase
{
    use RefreshDatabase;

    protected Connection $connection;
    protected Config $config;

    public function setUp(): void
    {
        parent::setUp();

        $this->refreshDatabase();

        // Set service alias
        $this->connection = $this->getService(Connection::class); // @phpstan-ignore-line
        $this->config = $this->getService(Config::class); // @phpstan-ignore-line
    }

    /**
     * Test session table connection & existence
     */
    public function testSessionTable(): void
    {
        $table = $this->config->getString('session.database.table', '');

        // Check connection is ok and returns what's expected from DatabaseSessionHandler
        $this->assertInstanceOf(ConnectionInterface::class, $this->connection); // @phpstan-ignore-line
        $this->assertInstanceOf(\Illuminate\Database\Query\Builder::class, $this->connection->table($table)); // @phpstan-ignore-line

        // Check table exist
        $this->assertTrue($this->connection->getSchemaBuilder()->hasTable($table));
    }

    /**
     * @depends testSessionTable
     */
    public function testSessionWrite(): void
    {
        // Define random session ID
        $session_id = 'test' . md5(microtime());

        // Make sure db is empty at first
        $this->assertEquals(0, SessionTable::count());
        $this->assertNull(SessionTable::find($session_id));

        // Get handler
        /** @var DatabaseSessionHandler */
        $handler = $this->getService(DatabaseSessionHandler::class);

        // Write session
        // https://github.com/laravel/framework/blob/10.x/src/Illuminate/Session/DatabaseSessionHandler.php#L132
        $this->assertTrue($handler->write($session_id, 'foo'));

        // Closing the handler does nothing anyway
        // https://github.com/laravel/framework/blob/10.x/src/Illuminate/Session/DatabaseSessionHandler.php#L78
        $this->assertTrue($handler->close());

        // Read session
        // https://github.com/laravel/framework/blob/10.x/src/Illuminate/Session/DatabaseSessionHandler.php#L86-L101
        $this->assertSame('foo', $handler->read($session_id));

        // Check manually that the file has been written
        $this->assertNotEquals(0, SessionTable::count());
        $this->assertNotNull(SessionTable::find($session_id));
        $this->assertSame(base64_encode('foo'), SessionTable::find($session_id)->payload);

        // Destroy session
        // https://github.com/laravel/framework/blob/10.x/src/Illuminate/Session/DatabaseSessionHandler.php#L256
        $this->assertTrue($handler->destroy($session_id));

        // Check db to make sure it's gone
        $this->assertEquals(0, SessionTable::count());
        $this->assertNull(SessionTable::find($session_id));
    }

    /**
     * Simulate session service with database handler.
     *
     * @depends testSessionWrite
     */
    public function testUsingSessionDouble(): void
    {
        // Destroy any active session from previous test
        @session_destroy();

        /** @var DatabaseSessionHandler */
        $handler = $this->getService(DatabaseSessionHandler::class);
        $session = new Session($handler, []);

        // Test handler is right
        $this->assertInstanceOf(DatabaseSessionHandler::class, $session->getHandler());
        $this->assertSame($handler, $session->getHandler());

        // Destroy previously defined session
        $session->destroy();

        // Start new one and validate status
        $this->assertSame(PHP_SESSION_NONE, $session->status());
        $session->start();
        $this->assertSame(PHP_SESSION_ACTIVE, $session->status());

        // Get id
        $session_id = $session->getId();
        $this->assertIsString($session_id);

        // Set something to the session
        $session->set('foo', 'bar');
        $this->assertEquals('bar', $session->get('foo'));

        // Close session to initiate write
        session_write_close();

        // Make sure db was filled with something
        $this->assertNotEquals(0, SessionTable::count());
        $this->assertNotNull(SessionTable::find($session_id));
        $this->assertSame(PHP_SESSION_NONE, $session->status());
    }
}
