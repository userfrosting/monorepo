<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Csrf;

use Psr\Http\Message\ResponseInterface as Response;
use RuntimeException;
use Slim\App;
use Slim\Views\Twig;
use UserFrosting\Config\Config;
use UserFrosting\Routes\RouteDefinitionInterface;
use UserFrosting\Session\Session;
use UserFrosting\Sprinkle\Core\Core;
use UserFrosting\Sprinkle\Core\Csrf\CsrfGuard;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase as TestCase;

/**
 * Tests CsrfGuard middleware.
 */
class CsrfGuardTest extends TestCase
{
    protected string $mainSprinkle = CsrfSprinkle::class;

    public function setUp(): void
    {
        parent::setUp();

        // Enable CSRF for this test
        /** @var Config */
        $config = $this->getService(Config::class);
        $config->set('csrf.enabled', true);
    }

    public function testFailCsrf(): void
    {
        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/csrf');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(400, $response);
        $this->assertJsonResponse('Bad Request', $response, 'title');
        $this->assertJsonResponse('Missing CSRF token. Try refreshing the page and then submitting again?', $response, 'description');
    }

    public function testTamperedCookieFailsCsrf(): void
    {
        $bootstrap = $this->handleRequest($this->createRequest('GET', '/csrf'));
        $cookie = explode(';', $bootstrap->getHeaderLine('Set-Cookie'), 2)[0];
        [$cookieName, $cookieValue] = explode('=', $cookie, 2);

        /** @var CsrfGuard */
        $guard = $this->getService(CsrfGuard::class);
        $request = $this->createJsonRequest('POST', '/csrf', [
            'csrf_name'  => $guard->getTokenName(),
            'csrf_value' => $guard->getTokenValue(),
        ])->withCookieParams([$cookieName => $cookieValue . 'tampered']);
        $response = $this->handleRequest($request);

        $this->assertResponseStatus(400, $response);
    }

    public function testMismatchedRequestTokenFailsCsrf(): void
    {
        $bootstrap = $this->handleRequest($this->createRequest('GET', '/csrf'));
        $cookie = explode(';', $bootstrap->getHeaderLine('Set-Cookie'), 2)[0];
        [$cookieName, $cookieValue] = explode('=', $cookie, 2);

        /** @var CsrfGuard */
        $guard = $this->getService(CsrfGuard::class);
        $request = $this->createJsonRequest('POST', '/csrf', [
            'csrf_name'  => $guard->getTokenName(),
            'csrf_value' => 'not-the-cookie-token',
        ])->withCookieParams([$cookieName => $cookieValue]);
        $response = $this->handleRequest($request);

        $this->assertResponseStatus(400, $response);
    }

    public function testCsrfCookieAttributes(): void
    {
        $response = $this->handleRequest($this->createRequest('GET', '/csrf'));
        $cookie = $response->getHeaderLine('Set-Cookie');

        $this->assertStringContainsString('uf_csrf=', $cookie);
        $this->assertStringContainsString('Path=/', $cookie);
        $this->assertStringContainsString('HttpOnly', $cookie);
        $this->assertStringContainsString('SameSite=Lax', $cookie);
    }

    public function testSuccessCsrf(): void
    {
        $bootstrap = $this->handleRequest($this->createRequest('GET', '/csrf'));

        /** @var CsrfGuard */
        $guard = $this->getService(CsrfGuard::class);

        $cookie = explode(';', $bootstrap->getHeaderLine('Set-Cookie'), 2)[0];
        [$cookieName, $cookieValue] = explode('=', $cookie, 2);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/csrf', [
            'csrf_name'  => $guard->getTokenName(),
            'csrf_value' => $guard->getTokenValue(),
        ])->withCookieParams([$cookieName => $cookieValue]);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(200, $response);
        $this->assertJsonResponse(['foo' => 'bar'], $response);
    }

    public function testCsrfDisabled(): void
    {
        /** @var Config */
        $config = $this->getService(Config::class);
        $config->set('csrf.enabled', false);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/csrf');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(200, $response);
        $this->assertJsonResponse(['foo' => 'bar'], $response);
    }

    public function testWeakTokenStrengthIsRejected(): void
    {
        /** @var Config */
        $config = $this->getService(Config::class);
        $config->set('csrf.strength', 15);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Minimum strength is 16');

        new CsrfGuard($config);
    }

    public function testMissingSecretIsRejectedWhenGeneratingToken(): void
    {
        /** @var Config */
        $config = $this->getService(Config::class);
        $config->set('csrf.secret', '');
        $guard = new CsrfGuard($config);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('CSRF secret is not configured.');

        $guard->generateToken();
    }

    public function testPersistentTokenIsReusedFromCookie(): void
    {
        $bootstrap = $this->handleRequest($this->createRequest('GET', '/csrf'));
        $request = $this->createRequest('GET', '/csrf')
            ->withCookieParams($this->cookieParams($bootstrap));
        $response = $this->handleRequest($request);

        $this->assertResponseStatus(200, $response);
        $this->assertSame('', $response->getHeaderLine('Set-Cookie'));
    }

    public function testHeaderTokensAreAccepted(): void
    {
        $bootstrap = $this->handleRequest($this->createRequest('GET', '/csrf'));

        /** @var CsrfGuard */
        $guard = $this->getService(CsrfGuard::class);
        $tokenName = $guard->getTokenName();
        $tokenValue = $guard->getTokenValue();

        $this->assertNotNull($tokenName);
        $this->assertNotNull($tokenValue);

        $request = $this->createJsonRequest('POST', '/csrf')
            ->withCookieParams($this->cookieParams($bootstrap))
            ->withHeader('csrf-name', $tokenName)
            ->withHeader('csrf-value', $tokenValue);
        $response = $this->handleRequest($request);

        $this->assertResponseStatus(200, $response);
        $this->assertJsonResponse(['foo' => 'bar'], $response);
    }

    public function testMalformedCookieFormatFailsCsrf(): void
    {
        $response = $this->handleRequest($this->postWithCookie('malformed'));

        $this->assertResponseStatus(400, $response);
    }

    public function testInvalidBase64CookiePayloadFailsCsrf(): void
    {
        $response = $this->handleRequest($this->postWithCookie($this->signedCookie('!')));

        $this->assertResponseStatus(400, $response);
    }

    public function testMalformedDecodedCookiePayloadFailsCsrf(): void
    {
        $payload = rtrim(strtr(base64_encode('malformed'), '+/', '-_'), '=');
        $response = $this->handleRequest($this->postWithCookie($this->signedCookie($payload)));

        $this->assertResponseStatus(400, $response);
    }

    public function testNonPersistentTokensAreRegenerated(): void
    {
        /** @var Config */
        $config = $this->getService(Config::class);
        $config->set('csrf.persistent_token', false);

        /** @var CsrfGuard */
        $guard = new CsrfGuard($config);
        $this->assertFalse($guard->getPersistentTokenMode());

        $handler = new class($guard) implements \Psr\Http\Server\RequestHandlerInterface {
            public ?string $tokenValueDuringRequest = null;

            public function __construct(
                private CsrfGuard $guard,
            ) {
            }

            public function handle(\Psr\Http\Message\ServerRequestInterface $request): \Psr\Http\Message\ResponseInterface
            {
                $this->tokenValueDuringRequest = $this->guard->getTokenValue();

                return new \Slim\Psr7\Response();
            }
        };
        $response = $guard->process($this->createRequest('GET', '/csrf'), $handler);

        $this->assertNotSame($handler->tokenValueDuringRequest, $guard->getTokenValue());
        $this->assertStringContainsString('uf_csrf=', $response->getHeaderLine('Set-Cookie'));
    }

    public function testOptionalCookieAttributesAreApplied(): void
    {
        /** @var Config */
        $config = $this->getService(Config::class);
        $config->set('csrf.cookie.domain', 'example.test');
        $config->set('csrf.cookie.secure', true);
        $config->set('csrf.cookie.max_age', 3600);
        $config->set('csrf.cookie.same_site', 'Strict');

        $response = $this->handleRequest($this->createRequest('GET', '/csrf'));
        $cookie = $response->getHeaderLine('Set-Cookie');

        $this->assertStringContainsString('Domain=example.test', $cookie);
        $this->assertStringContainsString('Secure', $cookie);
        $this->assertStringContainsString('Max-Age=3600', $cookie);
        $this->assertStringContainsString('SameSite=Strict', $cookie);
    }

    public function testCsrfBlacklist(): void
    {
        /** @var Config */
        $config = $this->getService(Config::class);
        $config->set('csrf.blacklist', [
            '^/csrf' => ['POST'],
        ]);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/csrf');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(200, $response);
        $this->assertJsonResponse(['foo' => 'bar'], $response);
    }

    public function testCsrfDoesNotUseSessionStorage(): void
    {
        /** @var Session */
        $session = $this->getService(Session::class);

        // Create request with method and url and fetch response
        $response = $this->handleRequest($this->createRequest('GET', '/csrf'));

        $this->assertResponseStatus(200, $response);
        $this->assertFalse($session->has('csrf'));
    }

    public function testTwigCsrf(): void
    {
        /** @var CsrfGuard */
        $guard = $this->getService(CsrfGuard::class);

        $request = $this->createRequest('GET', '/csrf');
        $response = $this->handleRequest($request);

        $this->assertResponseStatus(200, $response);
        $body = (string) $response->getBody();
        $this->assertStringNotContainsString('<input type="hidden" name="" value="">', $body);
        $this->assertStringContainsString($guard->getTokenName(), $body); // @phpstan-ignore-line
        $this->assertStringContainsString($guard->getTokenValue(), $body); // @phpstan-ignore-line
    }

    /**
     * @return array<string, string>
     */
    protected function cookieParams(Response $response): array
    {
        $cookie = explode(';', $response->getHeaderLine('Set-Cookie'), 2)[0];
        [$cookieName, $cookieValue] = explode('=', $cookie, 2);

        return [$cookieName => $cookieValue];
    }

    /**
     * @return array<string, string>
     */
    protected function csrfCookie(string $value): array
    {
        /** @var Config */
        $config = $this->getService(Config::class);

        return [$config->getString('csrf.cookie.name', 'uf_csrf') => $value];
    }

    protected function postWithCookie(string $cookieValue): \Psr\Http\Message\ServerRequestInterface
    {
        $this->handleRequest($this->createRequest('GET', '/csrf'));

        /** @var CsrfGuard */
        $guard = $this->getService(CsrfGuard::class);

        return $this->createJsonRequest('POST', '/csrf', [
            'csrf_name'  => (string) $guard->getTokenName(),
            'csrf_value' => (string) $guard->getTokenValue(),
        ])->withCookieParams($this->csrfCookie($cookieValue));
    }

    protected function signedCookie(string $payload): string
    {
        /** @var Config */
        $config = $this->getService(Config::class);
        $secret = $config->getString('csrf.secret');

        if (!is_string($secret)) {
            throw new RuntimeException('CSRF secret is not configured.');
        }

        return $payload . '.' . hash_hmac('sha256', $payload, $secret);
    }
}

class TestRoute implements RouteDefinitionInterface
{
    public function register(App $app): void
    {
        $app->get('/csrf', function (Response $response, Twig $twig) {
            return $twig->render($response, 'forms/csrf.html.twig');
        });

        $app->post('/csrf', function (Response $response) {
            $payload = json_encode(['foo' => 'bar'], JSON_THROW_ON_ERROR);
            $response->getBody()->write($payload);

            return $response->withHeader('Content-Type', 'application/json');
        });
    }
}

class CsrfSprinkle extends Core
{
    public function getRoutes(): array
    {
        return [
            TestRoute::class,
        ];
    }
}
