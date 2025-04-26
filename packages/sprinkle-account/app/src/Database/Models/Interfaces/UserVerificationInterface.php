<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Database\Models\Interfaces;

use DateTime;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use UserFrosting\Sprinkle\Core\Database\Models\Model;

/**
 * Interface for the `user_verification` database table and model.
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @mixin \Illuminate\Database\Eloquent\Model
 * @mixin \UserFrosting\Sprinkle\Core\Database\Models\Model
 *
 * @property int                $id
 * @property string             $code
 * @property int                $user_id
 * @property DateTime|null      $expires_at
 * @property DateTime|null      $completed_at
 * @property DateTime           $created_at
 * @property DateTime           $updated_at
 * @property UserInterface|null $user
 *
 * @method $this forUser(UserInterface $user) Link to the user scope
 * @method $this expired()                    Link to the expired scope
 * @method $this notExpired()                 Link to the not expired scope
 */
interface UserVerificationInterface
{
    /**
     * User associated with this verification request.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo;

    /**
     * Helper method to remove any expired, unused verifications.
     */
    public function clearExpired(): void;

    /**
     * Store the verification request for the given user, with the given code and timeout.
     *
     * @param UserInterface $user    The User to create the verification request for.
     * @param string        $code    The verification code.
     * @param int           $timeout The timeout in seconds for the verification request.
     */
    public function storeCode(UserInterface $user, string $code, int $timeout): self;

    /**
     * Validate the given verification code for the given user.
     *
     * @param UserInterface $user
     * @param string|int    $code
     *
     * @return bool True if the verification code is valid, false otherwise.
     */
    public function validateCode(UserInterface $user, string|int $code): bool;
}
