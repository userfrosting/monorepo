<?php

declare(strict_types=1);

/*
 * UserFrosting Framework (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/framework
 * @copyright Copyright (c) 2013-2024 Alexander Weissman, Louis Charette, Jordan Mele
 * @license   https://github.com/userfrosting/framework/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Session;

use ArrayAccess;
use Illuminate\Session\ExistenceAwareInterface;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use SessionHandlerInterface;

/**
 * A wrapper for $_SESSION that can be used with a variety of different session
 * handlers, based on illuminate/session.
 *
 * @implements ArrayAccess<string, mixed>
 */
class Session implements ArrayAccess
{
    /**
     * The session handler implementation.
     *
     * @var SessionHandlerInterface
     */
    protected SessionHandlerInterface $handler;

    /**
     * Create the session wrapper.
     *
     * @param SessionHandlerInterface $handler
     * @param mixed[]                 $config
     *
     * @todo $config array should be replaced with a more strict alternative
     */
    public function __construct(SessionHandlerInterface $handler, array $config = [])
    {
        $this->handler = $handler;

        if ($this->status() == PHP_SESSION_NONE) {
            session_set_save_handler($handler, true);

            if (isset($config['cache_limiter'])) {
                session_cache_limiter(strval($config['cache_limiter']));
            }

            if (isset($config['cache_expire'])) {
                session_cache_expire(intval($config['cache_expire']));
            }

            if (isset($config['name'])) {
                session_name(strval($config['name']));
            }

            if (isset($config['cookie_parameters'])) {
                $param = $config['cookie_parameters'];
                if (is_array($param)) {
                    session_set_cookie_params($param);
                } else {
                    session_set_cookie_params(intval($param));
                }
            }
        }
    }

    /**
     * Returns the current session status.
     *
     * @return int PHP_SESSION_DISABLED | PHP_SESSION_NONE | PHP_SESSION_ACTIVE
     */
    public function status(): int
    {
        return session_status();
    }

    /**
     * Start the session.
     */
    public function start(): void
    {
        if ($this->status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Destroy the current session, and unset all values in memory.  Destroy the
     * session cookie as well to remove all traces client-side.
     *
     * @param bool $destroyCookie Destroy the cookie on the client side as well.
     */
    public function destroy(bool $destroyCookie = true): void
    {
        if ($this->status() == PHP_SESSION_NONE) {
            return;
        }

        session_unset();

        // If it's desired to kill the session, also delete the session cookie.
        // Note: This will destroy the session, and not just the session data!
        if ($destroyCookie && ini_get('session.use_cookies') == true) {
            $params = session_get_cookie_params();
            $name = session_name();
            setcookie(
                ($name !== false) ? $name : '',
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    /**
     * Regenerate the session id.  For example, when logging someone in, you
     * should regenerate the session to prevent session fixation attacks.
     *
     * @param bool $deleteOldSession Set to true when you are logging someone in.
     */
    public function regenerateId(bool $deleteOldSession = false): void
    {
        session_regenerate_id($deleteOldSession);

        $this->setExists(false);
    }

    /**
     * Get the current session id.
     *
     * @see https://www.php.net/manual/en/function.session-id.php
     *
     * @return string|false Return false on error
     */
    public function getId(): string|false
    {
        return session_id();
    }

    /**
     * Set the existence of the session on the handler if applicable.
     *
     * @param bool $value
     */
    public function setExists(bool $value): void
    {
        if ($this->handler instanceof ExistenceAwareInterface) {
            $this->handler->setExists($value);
        }
    }

    /**
     * Determine if the given session value exists.
     *
     * @param string $key Dot notation is supported.
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return Arr::has($_SESSION, $key);
    }

    /**
     * Get the specified session value.
     *
     * @param string $key     Dot notation is supported.
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($_SESSION, $key, $default);
    }

    /**
     * Set a given session value.
     *
     * @param mixed[]|string|null $key   Dot notation is supported.
     * @param mixed               $value
     */
    public function set(array|string|null $key, mixed $value = null): void
    {
        if (is_array($key)) {
            foreach ($key as $innerKey => $innerValue) {
                Arr::set($_SESSION, $innerKey, $innerValue);
            }
        } else {
            Arr::set($_SESSION, $key, $value);
        }
    }

    /**
     * Prepend a value onto an array session value.
     *
     * @param string $key   Dot notation is supported.
     * @param mixed  $value
     */
    public function prepend(string $key, mixed $value): void
    {
        $array = $this->get($key);

        if (!is_array($array)) {
            throw new InvalidArgumentException('Can prepend on non-array');
        }

        array_unshift($array, $value);
        $this->set($key, $array);
    }

    /**
     * Push a value onto an array session value.
     *
     * @param string $key   Dot notation is supported.
     * @param mixed  $value
     */
    public function push(string $key, mixed $value): void
    {
        $array = $this->get($key);

        if (!is_array($array)) {
            throw new InvalidArgumentException('Can push on non-array');
        }

        $array[] = $value;
        $this->set($key, $array);
    }

    /**
     * Unset a session value.
     *
     * @param string $key Dot notation is supported.
     */
    public function forget(string $key): void
    {
        Arr::forget($_SESSION, $key);
    }

    /**
     * Get all of the session items for the application.
     *
     * @return mixed[]
     */
    public function all(): array
    {
        return $_SESSION;
    }

    /**
     * Determine if the given session option exists.
     *
     * @internal
     *
     * @param string $key Dot notation is supported.
     *
     * @return bool
     */
    public function offsetExists($key): bool
    {
        return $this->has($key);
    }

    /**
     * Get a session option.
     *
     * @internal
     *
     * @param string $key Dot notation is supported.
     *
     * @return mixed
     */
    public function offsetGet($key): mixed
    {
        return $this->get($key);
    }

    /**
     * Set a session option.
     *
     * @internal
     *
     * @param string|null $key   Dot notation is supported.
     * @param mixed       $value
     */
    public function offsetSet($key, $value): void
    {
        $this->set($key, $value);
    }

    /**
     * Unset a session option.
     *
     * @internal
     *
     * @param string $key Will be cast to string. Dot notation is supported.
     */
    public function offsetUnset($key): void
    {
        $this->forget($key);
    }

    /**
     * @return SessionHandlerInterface
     */
    public function getHandler(): SessionHandlerInterface
    {
        return $this->handler;
    }
}
