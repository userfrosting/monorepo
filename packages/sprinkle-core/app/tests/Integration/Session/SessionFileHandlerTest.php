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

use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;
use UserFrosting\Session\FileSessionHandler;
use UserFrosting\Session\Session;

/**
 * Integration tests for the session service.
 */
class SessionFileHandlerTest extends TestCase
{
    protected string $testSessionDir = __DIR__ . '/data';

    /**
     * Test FileSessionHandler works with our locator
     */
    public function testSessionWrite(): void
    {
        $fs = new Filesystem();

        // Define random session ID
        $session_id = 'test' . md5(microtime());

        // Get session dir
        $session_dir = $this->testSessionDir;

        // Define session filename
        $session_file = "$session_dir/$session_id";

        // Delete existing file to prevent false positive
        $fs->delete($session_file);
        $this->assertFalse($fs->exists($session_file));

        // Get handler
        $handler = new FileSessionHandler($fs, $session_dir, 120);

        // Write session
        // https://github.com/laravel/framework/blob/10.x/src/Illuminate/Session/FileSessionHandler.php#L83
        // write() ==> $this->files->put($this->path.'/'.$sessionId, $data, true);
        $this->assertTrue($handler->write($session_id, 'foo'));

        // Closing the handler does nothing anyway
        // https://github.com/laravel/framework/blob/10.x/src/Illuminate/Session/FileSessionHandler.php#L61
        // close() ==> return true;
        $this->assertTrue($handler->close());

        // Read session
        // https://github.com/laravel/framework/blob/10.x/src/Illuminate/Session/FileSessionHandler.php#L71
        // read() ==> return $this->files->get($path, true);
        $this->assertSame('foo', $handler->read($session_id));

        $this->assertTrue($handler->validateId($session_id));
        $this->assertTrue($handler->updateTimestamp($session_id, 'updated'));
        $this->assertSame('foo', $handler->read($session_id));

        // Check manually that the file has been written
        $this->assertTrue($fs->exists($session_file));
        $this->assertSame('foo', $fs->get($session_file));

        // Destroy session
        // https://github.com/laravel/framework/blob/10.x/src/Illuminate/Session/FileSessionHandler.php#L93
        // destroy() ==> $this->files->delete($this->path.'/'.$sessionId);
        $this->assertTrue($handler->destroy($session_id));

        // Check filesystem to make sure it's gone
        $this->assertFalse($fs->exists($session_file));
    }

    public function testUnchangedConcurrentSessionPreservesUpdate(): void
    {
        if (!function_exists('pcntl_fork')) {
            $this->markTestSkipped('The pcntl extension is required for this test.');
        }

        $fs = new Filesystem();
        $session_id = 'timestamp' . bin2hex(random_bytes(16));
        $session_file = $this->testSessionDir . '/' . $session_id;
        $fs->delete($session_file);

        $handler = new FileSessionHandler($fs, $this->testSessionDir, 120);
        $this->assertTrue($handler->write($session_id, 'seed|s:4:"base";'));

        $control = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, 0);
        if ($control === false) {
            $fs->delete($session_file);
            $this->fail('Unable to create the process control socket.');
        }

        $reader_pid = pcntl_fork();
        if ($reader_pid === -1) {
            fclose($control[0]);
            fclose($control[1]);
            $fs->delete($session_file);
            $this->fail('Unable to fork the unchanged session request.');
        }

        if ($reader_pid === 0) {
            fclose($control[0]);

            $session = new Session(new FileSessionHandler($fs, $this->testSessionDir, 120), []);
            session_id($session_id);
            $session->start();
            fwrite($control[1], 'r');

            usleep(500000);
            session_write_close();
            fclose($control[1]);

            exit(0);
        }

        fclose($control[1]);
        $this->assertSame('r', fread($control[0], 1));

        $updated_handler = new FileSessionHandler($fs, $this->testSessionDir, 120);
        $this->assertTrue($updated_handler->write($session_id, 'updated|s:7:"updated";'));

        fclose($control[0]);
        pcntl_waitpid($reader_pid, $reader_status);

        $stored = $handler->read($session_id);
        $fs->delete($session_file);

        $this->assertSame(0, pcntl_wexitstatus($reader_status));
        $this->assertSame('updated|s:7:"updated";', $stored);
    }

    /**
     * @depends testSessionWrite
     */
    public function testUsingSessionDouble(): void
    {
        // Destroy any active session from previous test
        @session_destroy();

        $fs = new Filesystem();
        $handler = new FileSessionHandler($fs, $this->testSessionDir, 120);
        $session = new Session($handler, []);

        // Test handler is right
        $this->assertInstanceOf(FileSessionHandler::class, $session->getHandler());
        $this->assertSame($handler, $session->getHandler());

        // Destroy previously defined session
        $session->destroy();

        // Start new one and validate status
        $this->assertSame(PHP_SESSION_NONE, $session->status());
        $session->start();
        $this->assertSame(PHP_SESSION_ACTIVE, $session->status());

        // Get id
        $session_id = $session->getId();

        // Set something to the session
        $session->set('foo', 'bar');
        $this->assertEquals('bar', $session->get('foo'));

        // Close session to initiate write
        session_write_close();

        // Make sure file was filled with something
        $session_dir = $this->testSessionDir;
        $session_file = "$session_dir/$session_id";

        $fs = new Filesystem();
        $this->assertTrue($fs->exists($session_file));
        $this->assertSame('foo|s:3:"bar";', $fs->get($session_file));

        // Delete existing file
        $fs->delete($session_file);
        $this->assertFalse($fs->exists($session_file));
        $this->assertSame(PHP_SESSION_NONE, $session->status());
    }
}
