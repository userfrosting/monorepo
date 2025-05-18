<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Database\Migrations\v500;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\SQLiteConnection;
use UserFrosting\Sprinkle\Account\Database\Migrations\v400\UsersTable;
use UserFrosting\Sprinkle\Core\Database\Migration;

/**
 * User table upgrade migration
 * Drop the `theme` and `last_activity_id` columns.
 * Version 5.0.0.
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
        // N.B.: SQLite doesn't support multiple calls to dropColumn / renameColumn in a single modification.
        if ($this->schema->hasColumn('users', 'theme')) {
            $this->schema->table('users', function (Blueprint $table) {
                $table->dropColumn('theme');
            });
        }

        if ($this->schema->hasColumn('users', 'last_activity_id')) {
            $this->schema->table('users', function (Blueprint $table) {
                /**
                 * sqlite can't drop foreign key without dropping the entire table
                 * since Laravel 5.7. Skip drop if an sqlite connection is detected.
                 *
                 * @see https://github.com/laravel/framework/issues/25475
                 */
                if (!$this->schema->getConnection() instanceof SQLiteConnection) {
                    $table->dropForeign('users_last_activity_id_foreign');
                }

                $table->dropColumn('last_activity_id');
            });
        }
    }

    /**
     * {@inheritdoc}
     */
    public function down(): void
    {
        $this->schema->table('users', function (Blueprint $table) {
            $table->string('theme', 100)->nullable()->comment('The user theme.');
            $table->integer('last_activity_id')->unsigned()->nullable()->comment('The id of the last activity performed by this user.');
            $table->foreign('last_activity_id')->references('id')->on('activities');
        });
    }
}
