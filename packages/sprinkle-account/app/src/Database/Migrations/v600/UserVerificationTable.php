<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Database\Migrations\v600;

use Illuminate\Database\Schema\Blueprint;
use UserFrosting\Sprinkle\Account\Database\Migrations\v400\UsersTable;
use UserFrosting\Sprinkle\Core\Database\Migration;

/**
 * User verifications table migration
 * Manages requests for user verification via one time passwords (OTPs).
 * Version 6.0.0.
 */
class UserVerificationTable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public static $dependencies = [
        UsersTable::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function up(): void
    {
        if (!$this->schema->hasTable('user_verifications')) {
            $this->schema->create('user_verifications', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned();
                $table->string('code');
                $table->timestamp('expires_at');
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
                $table->foreign('user_id')->references('id')->on('users');
                $table->index('user_id');
                $table->index('code');
            });
        }
    }

    /**
     * {@inheritdoc}
     */
    public function down(): void
    {
        $this->schema->drop('user_verifications');
    }
}
