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

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use UserFrosting\Sprinkle\Account\Database\Migrations\v400\UsersTable;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Core\Database\Migration;

/**
 * Update the users table to add a `password_last_set` column.
 * Version 6.0.0.
 */
class UpdateUsersTable extends Migration
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
        if (!$this->schema->hasColumn('users', 'password_last_set')) {
            $this->schema->table('users', function (Blueprint $table) {
                $table->timestamp('password_last_set')->after('password')->nullable();
            });

            // Set the password_last_set to the current time for all users
            User::query()->update(['password_last_set' => Carbon::now()]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function down(): void
    {
        if ($this->schema->hasColumn('users', 'password_last_set')) {
            $this->schema->withoutForeignKeyConstraints(function () {
                $this->schema->table('users', function (Blueprint $table) {
                    $table->dropColumn('password_last_set');
                });
            });
        }
    }
}
