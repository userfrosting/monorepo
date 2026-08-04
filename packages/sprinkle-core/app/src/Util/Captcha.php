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

use UserFrosting\Session\Session;

/**
 * Implements the captcha for user registration.
 */
class Captcha
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
     * Create a new captcha.
     *
     * @param Session $session We use the session object so that the hashed captcha token will automatically appear in the session.
     * @param string  $key
     */
    public function __construct(
        protected Session $session,
        protected string $key = 'captcha'
    ) {
    }

    /**
     * Generates a new captcha for the user registration form.
     *
     * This generates a random 5-character captcha and stores it in the session
     * with an md5 hash. Also, generates the corresponding captcha image.
     */
    public function generateRandomCode(): void
    {
        $md5_hash = md5((string) rand(0, 99999));
        $this->code = substr($md5_hash, 25, 5);
        $enc = md5($this->code);

        // Store the generated captcha value to the session
        $this->session->set($this->key, $enc);

        $this->generateImage();
    }

    /**
     * Returns the captcha code.
     *
     * @return string
     */
    public function getCaptcha(): string
    {
        return $this->code;
    }

    /**
     * Returns the captcha image.
     *
     * @return string
     */
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * Check that the specified code, when hashed, matches the code in the session.
     * Also, stores the specified code in the session with an md5 hash.
     *
     * @param string $code
     *
     * @return bool
     */
    public function verifyCode(string $code): bool
    {
        return md5($code) === $this->session->get($this->key, []);
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

        // Color pallette
        /** @var int */
        $white = imagecolorallocate($image, 255, 255, 255);
        /** @var int */
        $black = imagecolorallocate($image, 0, 0, 0);
        /** @var int */
        $red = imagecolorallocate($image, 255, 0, 0);
        /** @var int */
        $yellow = imagecolorallocate($image, 255, 255, 0);
        /** @var int */
        $dark_grey = imagecolorallocate($image, 64, 64, 64);

        // Create white rectangle
        imagefilledrectangle($image, 0, 0, 150, 30, $white);

        // Add some lines
        for ($i = 0; $i < 2; $i++) {
            imageline($image, 0, rand() % 10, 10, rand() % 30, $dark_grey);
            imageline($image, 0, rand() % 30, 150, rand() % 30, $red);
            imageline($image, 0, rand() % 30, 150, rand() % 30, $yellow);
        }

        // RandTab color pallette
        /** @var int[] */
        $randc = [
            0 => imagecolorallocate($image, 0, 0, 0),
            1 => imagecolorallocate($image, 255, 0, 0),
            2 => imagecolorallocate($image, 255, 255, 0),
            3 => imagecolorallocate($image, 64, 64, 64),
            4 => imagecolorallocate($image, 0, 0, 255),
        ];

        //add some dots
        for ($i = 0; $i < 1000; $i++) {
            imagesetpixel($image, rand() % 200, rand() % 50, $randc[rand() % 5]);
        }

        //calculate center of text
        $x = (int) round((150 - 0 - imagefontwidth(5) * strlen($this->code)) / 2 + 0 + 5);

        //write string twice
        imagestring($image, 5, $x, 7, $this->code, $black);
        imagestring($image, 5, $x, 7, $this->code, $black);
        //start ob
        ob_start();
        imagepng($image);

        //get binary image data
        $this->image = (string) ob_get_clean();

        return $this->image;
    }

    /**
     * Get the value of key.
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Set the value of key.
     *
     * @return static
     */
    public function setKey(string $key): static
    {
        $this->key = $key;

        return $this;
    }
}
