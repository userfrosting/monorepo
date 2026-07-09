<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Database\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserVerificationInterface;
use UserFrosting\Sprinkle\Core\Database\Models\Model;

/**
 * User Email Verification Model.
 * A self contained model for email based user verification.
 *
 * Represents a pending email verification for a new user account.
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class UserVerification extends Model implements UserVerificationInterface
{
    /**
     * @var string The name of the table for the current model.
     */
    protected $table = 'user_verifications';

    /**
     * @var string The algorithm used to hash the verification code.
     */
    protected string $algorithm = 'sha512';

    /**
     * @var array<int, string> The attributes that are mass assignable.
     */
    protected $fillable = [
        'code',
        'user_id',
        'expires_at',
        'completed_at',
    ];

    /**
     * @var string[] The attributes that should be cast.
     */
    // @phpstan-ignore-next-line
    protected $casts = [
        'user_id'   => 'integer',
    ];

    /**
     * {@inheritDoc}
     */
    public function user(): BelongsTo
    {
        /** @var string */
        $relation = static::$ci?->get(UserInterface::class);

        return $this->belongsTo($relation, 'user_id');
    }

    /**
     * User Scope.
     *
     * @param Builder       $query
     * @param UserInterface $user
     */
    protected function scopeForUser(Builder $query, UserInterface $user): void
    {
        $query->where('user_id', $user->id);
    }

    /**
     * Expired verifications Scope.
     *
     * @param Builder $query
     */
    protected function scopeExpired(Builder $query): void
    {
        $query->where('completed_at', null)->where('expires_at', '<', Carbon::now());
    }

    /**
     * Expired verifications Scope.
     *
     * @param Builder $query
     */
    protected function scopeNotExpired(Builder $query): void
    {
        $query->where('completed_at', null)->where('expires_at', '>=', Carbon::now());
    }

    /**
     * {@inheritDoc}
     */
    public function storeCode(UserInterface $user, string $code, int $timeout): self
    {
        $record = new $this([
            'code'         => $code,
            'user_id'      => $user->id,
            'expires_at'   => Carbon::now()->addSeconds($timeout),
            'completed_at' => null,
        ]);
        $record->save();

        return $record;
    }

    /**
     * {@inheritDoc}
     */
    public function validateCode(UserInterface $user, string|int $code): bool
    {
        // Match the user with the verification code in the database
        /** @var self|null */
        // @phpstan-ignore-next-line
        $verification = $this->forUser($user)->notExpired()->first();

        // If not match are found, return false
        if ($verification === null) {
            return false;
        }

        // Check if the code matches
        if ($verification->code === $this->hashCode($code)) {
            // Mark the token as completed
            $verification->completed_at = Carbon::now();
            $verification->save();

            return true;
        }

        return false;
    }

    /**
     * Code column mutator.
     * Hash the code before saving it to the database.
     *
     * @return Attribute
     */
    protected function code(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => $this->hashCode($value),
        );
    }

    /**
     * Helper method to remove any expired, unused verifications.
     */
    public function clearExpired(): void
    {
        $this->expired()->delete();
    }

    /**
     * Hash the verification code using the specified algorithm.
     *
     * @param string|int $code
     *
     * @return string
     */
    protected function hashCode(string|int $code): string
    {
        return hash($this->algorithm, (string) $code);
    }
}
