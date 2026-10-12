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

use Psr\Http\Message\ResponseInterface;

/**
 * Contract for generating and verifying captcha challenges.
 */
interface CaptchaInterface
{
    /**
     * Generate a new captcha challenge and its image.
     *
     * @return string The opaque challenge identifier to send to the client.
     */
    public function generate(): string;

    /**
     * Get the generated captcha code.
     *
     * @return string The clear-text code represented by the current image.
     */
    public function getCode(): string;

    /**
     * Get the generated captcha image.
     *
     * @return string The PNG image as a binary string.
     */
    public function getImage(): string;

    /**
     * Get the current opaque challenge identifier.
     *
     * @return string The challenge identifier, or an empty string before generation.
     */
    public function getChallengeId(): string;

    /**
     * Verify and consume a captcha challenge.
     * Successful verification consumes the challenge and prevents replay.
     *
     * @param string      $code        The code supplied by the user.
     * @param string|null $challengeId The opaque challenge identifier.
     *
     * @return bool True when the code is valid and the challenge is consumed.
     */
    public function verifyCode(string $code, ?string $challengeId = null): bool;

    /**
     * Get the cookie name used to transport the challenge identifier.
     *
     * @return string The name of the cookie used to transport the challenge identifier.
     */
    public function getCookieName(): string;

    /**
     * Add the current challenge cookie to an HTTP response.
     *
     * @param ResponseInterface $response The HTTP response to which the cookie should be added.
     *
     * @return ResponseInterface The modified HTTP response with the challenge cookie.
     */
    public function withCookie(ResponseInterface $response): ResponseInterface;
}
