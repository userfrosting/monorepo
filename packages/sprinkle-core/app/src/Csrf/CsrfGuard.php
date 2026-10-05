<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Csrf;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Core\Exceptions\CsrfMissingException;

/**
 * Custom Slim Csrf Provider implementation.
 *
 * Allow injection of the Slim App, use of our session storage, custom error
 * handling, blacklist from the config service, etc.
 */
class CsrfGuard implements MiddlewareInterface
{
    /**
     * @var array<string, string>|null The key pair for the CSRF token, containing the token name and value.
     */
    protected ?array $keyPair = null;

    /**
     * @var bool Indicates whether the CSRF cookie has been modified and needs to be updated.
     */
    protected bool $cookieDirty = false;

    /**
     * @var string The prefix used for CSRF token keys.
     */
    protected string $prefix;

    /**
     * @var string The name of the CSRF cookie.
     */
    protected string $cookieName;

    /**
     * @var string The secret used for generating CSRF tokens.
     */
    protected string $secret;

    /**
     * @var int The strength (in bytes) of the CSRF token.
     */
    protected int $strength;

    /**
     * @var bool Indicates whether persistent token mode is enabled.
     */
    protected bool $persistentTokenMode;

    /**
     * Overwrites the default constructor to inject dependencies.
     *
     * @param Config $config
     */
    public function __construct(
        protected Config $config,
    ) {
        $this->prefix = rtrim($config->getString('csrf.name', 'csrf'), '_');
        $this->secret = $config->getString('csrf.secret', '');
        $this->strength = $config->getInt('csrf.strength', 16);
        $this->persistentTokenMode = $config->getBool('csrf.persistent_token', true);
        $this->cookieName = $config->getString('csrf.cookie.name', $this->prefix . '_token');

        if ($this->strength < 16) {
            throw new RuntimeException('CSRF middleware instantiation failed. Minimum strength is 16.');
        }
    }

    /**
     * Middleware call to process an incoming server request and return a response, ensuring CSRF protection.
     *
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        if ($this->config->getBool('csrf.enabled') === false) {
            return $handler->handle($request);
        }

        $cookiePair = $this->readCookie($request);
        $isBlacklisted = $this->isBlacklisted($request);

        if (!$isBlacklisted && $this->isUnsafeMethod($request)) {
            $requestPair = $this->readRequestToken($request);

            if (!$this->matches($cookiePair, $requestPair)) {
                $this->generateToken();

                throw new CsrfMissingException('The CSRF code was invalid or not provided.');
            }

            $this->keyPair = $cookiePair;
        } elseif ($this->persistentTokenMode && $cookiePair !== null) {
            $this->keyPair = $cookiePair;
        } else {
            $this->generateToken();
        }

        $response = $handler->handle($request);

        if (!$this->persistentTokenMode) {
            $this->generateToken();
        }

        return $this->appendCookie($response);
    }

    /**
     * Generates a new CSRF token and stores it internally.
     *
     * @return array<string, string>
     */
    public function generateToken(): array
    {
        $this->assertSecret();

        $this->keyPair = [
            $this->getTokenNameKey()  => $this->prefix . bin2hex(random_bytes(8)),
            $this->getTokenValueKey() => bin2hex(random_bytes(max(1, $this->strength))),
        ];
        $this->cookieDirty = true;

        return $this->keyPair;
    }

    /**
     * Retrieves the current CSRF token name.
     *
     * @return string|null The CSRF token name, or null if not set.
     */
    public function getTokenName(): ?string
    {
        return $this->keyPair[$this->getTokenNameKey()] ?? null;
    }

    /**
     * Retrieves the current CSRF token value.
     *
     * @return string|null The CSRF token value, or null if not set.
     */
    public function getTokenValue(): ?string
    {
        return $this->keyPair[$this->getTokenValueKey()] ?? null;
    }

    /**
     * Retrieves the key used for the CSRF token name in the internal storage.
     *
     * @return string The key for the CSRF token name.
     */
    public function getTokenNameKey(): string
    {
        return $this->prefix . '_name';
    }

    /**
     * Retrieves the key used for the CSRF token value in the internal storage.
     *
     * @return string The key for the CSRF token value.
     */
    public function getTokenValueKey(): string
    {
        return $this->prefix . '_value';
    }

    /**
     * Retrieves the current persistent token mode.
     *
     * @return bool True if persistent token mode is enabled, false otherwise.
     */
    public function getPersistentTokenMode(): bool
    {
        return $this->persistentTokenMode;
    }

    /**
     * Asserts that the CSRF secret is configured.
     *
     * @throws RuntimeException if the CSRF secret is not configured.
     */
    protected function assertSecret(): void
    {
        if ($this->secret === '') {
            throw new RuntimeException('CSRF secret is not configured.');
        }
    }

    /**
     * Determines if the HTTP request method (POST, PUT, etc.) is considered
     * unsafe.
     *
     * @param ServerRequestInterface $request The HTTP request.
     *
     * @return bool True if the request method is unsafe, false otherwise.
     */
    protected function isUnsafeMethod(ServerRequestInterface $request): bool
    {
        return in_array(strtoupper($request->getMethod()), ['POST', 'PUT', 'DELETE', 'PATCH'], true);
    }

    /**
     * Determines if the HTTP request is blacklisted based on the configured
     * CSRF blacklist.
     *
     * @param ServerRequestInterface $request The HTTP request.
     *
     * @return bool True if the request is blacklisted, false otherwise.
     */
    protected function isBlacklisted(ServerRequestInterface $request): bool
    {
        $path = '/' . ltrim($request->getUri()->getPath(), '/');
        $method = strtoupper($request->getMethod());
        $blacklist = $this->config->getArray('csrf.blacklist', []);

        foreach ($blacklist as $pattern => $methods) {
            $methods = array_map('strtoupper', $methods);
            if (in_array($method, $methods, true) && $pattern !== '' && preg_match('~' . $pattern . '~', $path) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Reads the CSRF token from the request body or headers. Returns null if
     * the token is missing or malformed.
     *
     * @param ServerRequestInterface $request The HTTP request.
     *
     * @return array<string, string>|null
     */
    protected function readRequestToken(ServerRequestInterface $request): ?array
    {
        // Try to read the CSRF token from the request body first.
        $body = $request->getParsedBody();
        if (is_array($body) && isset($body[$this->getTokenNameKey()], $body[$this->getTokenValueKey()])) {
            return [
                $this->getTokenNameKey()  => (string) $body[$this->getTokenNameKey()],
                $this->getTokenValueKey() => (string) $body[$this->getTokenValueKey()],
            ];
        }

        // Read the CSRF token from the request headers if it's not present in the body.
        $name = $this->getHeaderValue($request, $this->getTokenNameKey());
        $value = $this->getHeaderValue($request, $this->getTokenValueKey());
        if ($name === null || $value === null) {
            return null;
        }

        return [
            $this->getTokenNameKey()  => $name,
            $this->getTokenValueKey() => $value,
        ];
    }

    /**
     * Retrieves the value of a specific header from the request. Returns null
     * if the header is missing or empty.
     *
     * @param ServerRequestInterface $request The HTTP request.
     * @param string                 $name    The name of the header.
     *
     * @return string|null The header value or null if not found.
     */
    protected function getHeaderValue(ServerRequestInterface $request, string $name): ?string
    {
        $value = $request->getHeaderLine($name);
        if ($value === '') {
            $value = $request->getHeaderLine(str_replace('_', '-', $name));
        }

        return $value === '' ? null : $value;
    }

    /**
     * Reads the CSRF token from the request cookie. Returns null if the cookie
     * is missing, malformed, or invalid.
     *
     * @param ServerRequestInterface $request The incoming server request.
     *
     * @return array<string, string>|null
     */
    protected function readCookie(ServerRequestInterface $request): ?array
    {
        // Fetch the CSRF token from the request cookie. Returns null if the
        // cookie is missing or malformed.
        $cookie = $request->getCookieParams()[$this->cookieName] ?? null;
        if (!is_string($cookie)) {
            return null;
        }

        // Split the cookie into payload and signature. The cookie should be
        // in the format "payload.signature".
        $parts = explode('.', $cookie, 2);
        if (count($parts) !== 2) {
            return null;
        }

        // Extract the payload and signature from the cookie. Verify the
        // signature of the cookie to ensure it hasn't been tampered with.
        [$payload, $signature] = $parts;
        $this->assertSecret();
        if (!hash_equals(hash_hmac('sha256', $payload, $this->secret), $signature)) {
            return null;
        }

        // Decode the payload from base64 URL format.
        $encodedPayload = strtr($payload, '-_', '+/');
        $padding = strlen($encodedPayload) % 4;
        if ($padding !== 0) {
            $encodedPayload .= str_repeat('=', 4 - $padding);
        }

        // Perform base64 URL decoding of the payload.
        $decoded = base64_decode($encodedPayload, true);
        if ($decoded === false) {
            return null;
        }

        $token = explode('.', $decoded, 2);
        if (count($token) !== 2 || $token[0] === '' || $token[1] === '') {
            return null;
        }

        return [
            $this->getTokenNameKey()  => $token[0],
            $this->getTokenValueKey() => $token[1],
        ];
    }

    /**
     * Checks if the provided CSRF token matches the expected token.
     *
     * @param array<string, string>|null $expected
     * @param array<string, string>|null $provided
     *
     * @return bool True if the tokens match, false otherwise.
     */
    protected function matches(?array $expected, ?array $provided): bool
    {
        if ($expected === null || $provided === null) {
            return false;
        }

        return hash_equals($expected[$this->getTokenNameKey()], $provided[$this->getTokenNameKey()])
            && hash_equals($expected[$this->getTokenValueKey()], $provided[$this->getTokenValueKey()]);
    }

    /**
     * Appends the CSRF cookie to the response if it has been modified.
     *
     * @param ResponseInterface $response The HTTP response.
     *
     * @return ResponseInterface The modified response with the CSRF cookie.
     */
    protected function appendCookie(ResponseInterface $response): ResponseInterface
    {
        if (!$this->cookieDirty || $this->keyPair === null) {
            return $response;
        }

        $payload = $this->base64UrlEncode($this->getTokenName() . '.' . $this->getTokenValue());
        $value = $payload . '.' . hash_hmac('sha256', $payload, $this->secret);
        $parts = [$this->cookieName . '=' . $value];
        $path = $this->config->getString('csrf.cookie.path', '/');
        $domain = $this->config->getString('csrf.cookie.domain', '');
        $sameSite = $this->config->getString('csrf.cookie.same_site', 'Lax');
        $maxAge = $this->config->getInt('csrf.cookie.max_age', 0);

        $parts[] = 'Path=' . $path;
        if ($domain !== '') {
            $parts[] = 'Domain=' . $domain;
        }
        if ($this->config->getBool('csrf.cookie.secure', false) === true) {
            $parts[] = 'Secure';
        }
        if ($this->config->getBool('csrf.cookie.http_only', true) === true) {
            $parts[] = 'HttpOnly';
        }
        if ($maxAge > 0) {
            $parts[] = 'Max-Age=' . $maxAge;
        }
        if ($sameSite !== '') {
            $parts[] = 'SameSite=' . $sameSite;
        }

        $this->cookieDirty = false;

        return $response->withAddedHeader('Set-Cookie', implode('; ', $parts));
    }

    /**
     * Encodes a string using base64 URL encoding.
     *
     * @param string $value The string to encode.
     *
     * @return string The base64 URL encoded string.
     */
    protected function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
