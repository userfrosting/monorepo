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

use Illuminate\Cache\Repository as Cache;
use Illuminate\Contracts\Cache\LockProvider;
use Psr\Http\Message\ResponseInterface;

/**
 * Implements a cache-backed captcha profile.
 */
class Captcha implements CaptchaInterface
{
    /**
     * @var string The randomly generated captcha code.
     */
    protected string $code = '';

    /**
     * @var string The captcha image, represented as a binary string.
     */
    protected string $image = '';

    /**
     * @var string The opaque challenge ID associated with the captcha.
     */
    protected string $challengeId = '';

    /**
     * @var string The alphabet used to generate the captcha code.
     */
    protected string $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    /**
     * Create a new captcha.
     *
     * @param Cache          $cache
     * @param CaptchaProfile $profile The resolved captcha profile.
     */
    public function __construct(
        protected Cache $cache,
        protected CaptchaProfile $profile,
    ) {
    }

    /**
     * This generates a random 5-character captcha and stores its password hash
     * in the cache. Also, generates the corresponding captcha image.
     *
     * {@inheritDoc}
     */
    public function generate(): string
    {
        $this->code = '';
        for ($i = 0; $i < 5; $i++) {
            $this->code .= $this->alphabet[random_int(0, strlen($this->alphabet) - 1)];
        }

        $this->challengeId = bin2hex(random_bytes(32));
        $hash = password_hash($this->code, PASSWORD_DEFAULT);

        $expiresAt = time() + $this->getTtl();
        $this->cache->put($this->cacheKey($this->challengeId), [
            'hash'       => $hash,
            'attempts'   => 0,
            'expires_at' => $expiresAt,
        ], $this->getTtl());

        $this->generateImage();

        return $this->challengeId;
    }

    /**
     * {@inheritDoc}
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * {@inheritDoc}
     */
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * {@inheritDoc}
     */
    public function getChallengeId(): string
    {
        return $this->challengeId;
    }

    /**
     * {@inheritDoc}
     */
    public function getCookieName(): string
    {
        return $this->profile->cookieName;
    }

    /**
     * {@inheritDoc}
     */
    public function withCookie(ResponseInterface $response): ResponseInterface
    {
        $parts = [$this->getCookieName() . '=' . $this->challengeId];
        $parts[] = 'Path=' . $this->profile->cookiePath;
        $domain = $this->profile->cookieDomain;
        if ($domain !== '') {
            $parts[] = 'Domain=' . $domain;
        }
        if ($this->profile->cookieSecure) {
            $parts[] = 'Secure';
        }
        if ($this->profile->cookieHttpOnly) {
            $parts[] = 'HttpOnly';
        }
        $maxAge = $this->profile->cookieMaxAge;
        if ($maxAge > 0) {
            $parts[] = 'Max-Age=' . $maxAge;
        }
        $sameSite = $this->profile->cookieSameSite;
        if ($sameSite !== '') {
            $parts[] = 'SameSite=' . $sameSite;
        }

        return $response->withAddedHeader('Set-Cookie', implode('; ', $parts));
    }

    /**
     * {@inheritDoc}
     */
    public function verifyCode(string $code, ?string $challengeId = null): bool
    {
        // If no challenge ID is provided, use the current challenge ID.
        // If still empty, verification cannot proceed.
        $challengeId = $challengeId ?? $this->challengeId;
        if ($challengeId === '') {
            return false;
        }

        // Acquire a lock for the current challenge ID to prevent race conditions on the same challenge.
        $store = $this->cache->getStore();
        if (!$store instanceof LockProvider) {
            return false;
        }
        $lock = $store->lock($this->lockKey($challengeId), $this->getLockSeconds());
        if ($lock->get() !== true) {
            return false;
        }

        try {
            $key = $this->cacheKey($challengeId);
            $record = $this->cache->get($key);
            if (!is_array($record) || !isset($record['hash'], $record['attempts'], $record['expires_at'])) {
                return false;
            }

            // Check if the captcha has expired before proceeding with verification.
            $expiresAt = (int) $record['expires_at'];
            if ($expiresAt <= time()) {
                $this->cache->forget($key);

                return false;
            }

            // Ensure that the number of attempts and the hash are valid before proceeding.
            $attempts = (int) $record['attempts'];
            $hash = $record['hash'];
            if ($attempts >= $this->getMaxAttempts() || !is_string($hash)) {
                $this->cache->forget($key);

                return false;
            }

            // Verify the provided code against the stored hash.
            if (!password_verify($code, $hash)) {
                $attempts++;
                if ($attempts >= $this->getMaxAttempts()) {
                    $this->cache->forget($key);
                } else {
                    $record['attempts'] = $attempts;
                    $this->cache->put($key, $record, max(1, $expiresAt - time()));
                }

                return false;
            }

            // If the code is correct, remove the captcha from the cache.
            $this->cache->forget($key);

            return true;
        } finally {
            $lock->release();
        }
    }

    /**
     * Generate the image for the current captcha.
     * This generates an image as a binary string.
     *
     * @return string
     */
    protected function generateImage(): string
    {
        /** @var \GdImage */
        $image = imagecreatetruecolor(150, 30);

        // Color palette
        /** @var array<string, array{int<0, 255>, int<0, 255>, int<0, 255>, int}> $colorDefinitions array[R, G, B, Default] */
        $colorDefinitions = [
            'white'     => [255, 255, 255, 16777215],
            'black'     => [0, 0, 0, 0],
            'red'       => [255, 0, 0, 16711680],
            'yellow'    => [255, 255, 0, 16776960],
            'dark_grey' => [64, 64, 64, 4210752],
            'blue'      => [0, 0, 255, 255],
        ];

        // Create colors allocations
        /** @var array<string, int> $colors */
        $colors = [];
        foreach ($colorDefinitions as $name => [$red, $green, $blue, $default]) {
            $color = $this->allocateColor($image, $red, $green, $blue);
            if ($color === false) {
                $color = $default;
            }

            $colors[$name] = $color;
        }

        // Create white rectangle
        imagefilledrectangle($image, 0, 0, 150, 30, $colors['white']);

        // Add some lines
        for ($i = 0; $i < 2; $i++) {
            imageline($image, 0, rand() % 10, 10, rand() % 30, $colors['dark_grey']);
            imageline($image, 0, rand() % 30, 150, rand() % 30, $colors['red']);
            imageline($image, 0, rand() % 30, 150, rand() % 30, $colors['yellow']);
        }

        // Random dot colors
        $randomColors = [
            $colors['black'],
            $colors['red'],
            $colors['yellow'],
            $colors['dark_grey'],
            $colors['blue'],
        ];

        // Add some dots
        for ($i = 0; $i < 1000; $i++) {
            imagesetpixel($image, rand() % 200, rand() % 50, $randomColors[rand() % 5]);
        }

        // Calculate center of text
        $x = (int) round((150 - 0 - imagefontwidth(5) * strlen($this->code)) / 2 + 0 + 5);

        // Write string twice
        imagestring($image, 5, $x, 7, $this->code, $colors['black']);
        imagestring($image, 5, $x, 7, $this->code, $colors['black']);
        // Start output buffering
        ob_start();
        imagepng($image);

        // Get binary image data
        $this->image = (string) ob_get_clean();

        return $this->image;
    }

    /**
     * Allocate a color for the captcha image.
     *
     * @param \GdImage    $image
     * @param int<0, 255> $red
     * @param int<0, 255> $green
     * @param int<0, 255> $blue
     *
     * @return int|false
     */
    protected function allocateColor(\GdImage $image, int $red, int $green, int $blue): int|false
    {
        return imagecolorallocate($image, $red, $green, $blue);
    }

    /**
     * Get the cache store key for the current challenge ID.
     *
     * @param string $challengeId The opaque challenge identifier.
     *
     * @return string The cache store key.
     */
    protected function cacheKey(string $challengeId): string
    {
        return $this->profile->key . ':' . hash('sha256', $challengeId);
    }

    /**
     * Get the cache store lock key for the current challenge ID.
     *
     * @param string $challengeId The opaque challenge identifier.
     *
     * @return string The cache store lock key.
     */
    protected function lockKey(string $challengeId): string
    {
        return $this->profile->key . ':lock:' . hash('sha256', $challengeId);
    }

    /**
     * @return int The time-to-live (TTL) for the captcha in seconds.
     */
    protected function getTtl(): int
    {
        return $this->profile->ttl;
    }

    /**
     * Get the maximum number of allowed attempts for the captcha.
     *
     * @return int The maximum number of allowed attempts.
     */
    protected function getMaxAttempts(): int
    {
        return $this->profile->maxAttempts;
    }

    /**
     * Get the lease duration for the cache store lock used during verification.
     *
     * @return int The cache lock lease duration in seconds.
     */
    protected function getLockSeconds(): int
    {
        return $this->profile->lockSeconds;
    }
}
