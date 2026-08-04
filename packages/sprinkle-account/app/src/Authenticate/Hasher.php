<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Authenticate;

use UserFrosting\Sprinkle\Account\Authenticate\Interfaces\HasherInterface;

/**
 * Password hashing and validation class.
 */
class Hasher implements HasherInterface
{
    /**
     * @var int Default crypt cost factor.
     */
    protected int $cost = 10;

    /**
     * Returns the hashing type for a specified password hash.
     *
     * Automatically detects the hash type: "sha1" (for UserCake legacy accounts), "legacy" (for 0.1.x accounts), and "modern" (used for new accounts).
     *
     * @param string $password the hashed password.
     *
     * @return string "sha1"|"legacy"|"modern".
     */
    public function getHashType(string $password): string
    {
        // If the password in the db is 65 characters long, we have an sha1-hashed password.
        if (strlen($password) === 65) {
            return 'sha1';
        } elseif (strlen($password) === 82) {
            return 'legacy';
        }

        return 'modern';
    }

    /**
     * Hashes a plaintext password using bcrypt.
     *
     * @param string $password the plaintext password.
     *
     * @return string the hashed password.
     */
    public function hash(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, [
            'cost' => $this->getCount(),
        ]);
    }

    /**
     * Verify a plaintext password against the user's hashed password.
     *
     * @param string $password The plaintext password to verify.
     * @param string $hash     The hash to compare against.
     *
     * @return bool True if the password matches, false otherwise.
     */
    public function verify(string $password, string $hash): bool
    {
        $hashType = self::getHashType($hash);

        if ($hashType === 'sha1') {
            // Legacy UserCake passwords
            $salt = substr($hash, 0, 25);		// Extract the salt from the hash
            $inputHash = $salt . sha1($salt . $password);

            return hash_equals($inputHash, $hash) === true;
        } elseif ($hashType === 'legacy') {
            // Homegrown implementation (assuming that current install has been using a cost parameter of 12)
            // Used for manual implementation of bcrypt.
            // Note that this legacy hashing put the salt at the _end_ for some reason.
            $salt = substr($hash, 60);
            $inputHash = crypt($password, '$2y$12$' . $salt);
            $correctHash = substr($hash, 0, 60);

            return hash_equals($inputHash, $correctHash) === true;
        }

        // Modern implementation
        return password_verify($password, $hash);
    }

    /**
     * Get default crypt cost factor.
     *
     * @return int
     */
    public function getCount(): int
    {
        return $this->cost;
    }

    /**
     * Set default crypt cost factor.
     *
     * @param int $cost Default crypt cost factor.
     *
     * @return static
     */
    public function setCount(int $cost): static
    {
        $this->cost = $cost;

        return $this;
    }
}
