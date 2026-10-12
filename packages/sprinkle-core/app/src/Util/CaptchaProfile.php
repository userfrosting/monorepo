<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Util;

/**
 * Resolved, immutable settings for one captcha profile.
 *
 * A captcha profile describes one independent captcha use case, such as
 * account registration or a comment form. It defines the cache namespace and
 * cookie identity used to keep that use case isolated from other captchas,
 * together with its expiry, attempt, locking, and cookie policies.
 *
 * The profile is resolved once by the captcha factory and then passed to a
 * captcha service. Keeping these settings immutable prevents a service from
 * changing profile while a challenge is being generated or verified.
 */
final class CaptchaProfile
{
    public function __construct(
        public readonly string $key,
        public readonly int $ttl,
        public readonly int $maxAttempts,
        public readonly int $lockSeconds,
        public readonly string $cookieName,
        public readonly string $cookiePath,
        public readonly string $cookieDomain,
        public readonly bool $cookieSecure,
        public readonly bool $cookieHttpOnly,
        public readonly string $cookieSameSite,
        public readonly int $cookieMaxAge,
    ) {
    }

    /**
     * Resolve a profile from merged configuration.
     *
     * @param array<string, mixed> $config
     */
    public static function fromArray(array $config): self
    {
        $cookie = is_array($config['cookie'] ?? null) ? $config['cookie'] : [];

        return new self(
            self::stringValue($config, 'key', 'captcha'),
            max(1, self::intValue($config, 'ttl', 300)),
            max(1, self::intValue($config, 'max_attempts', 5)),
            max(1, self::intValue($config, 'lock_seconds', 5)),
            self::stringValue($cookie, 'name', 'uf_captcha'),
            self::stringValue($cookie, 'path', '/'),
            self::stringValue($cookie, 'domain', ''),
            self::boolValue($cookie, 'secure', false),
            self::boolValue($cookie, 'http_only', true),
            self::stringValue($cookie, 'same_site', 'Lax'),
            max(0, self::intValue($cookie, 'max_age', 0)),
        );
    }

    /**
     * @param array<string, mixed> $config
     */
    private static function stringValue(array $config, string $key, string $default): string
    {
        return is_string($config[$key] ?? null) ? $config[$key] : $default;
    }

    /**
     * @param array<string, mixed> $config
     */
    private static function intValue(array $config, string $key, int $default): int
    {
        return is_int($config[$key] ?? null) ? $config[$key] : $default;
    }

    /**
     * @param array<string, mixed> $config
     */
    private static function boolValue(array $config, string $key, bool $default): bool
    {
        return is_bool($config[$key] ?? null) ? $config[$key] : $default;
    }
}
