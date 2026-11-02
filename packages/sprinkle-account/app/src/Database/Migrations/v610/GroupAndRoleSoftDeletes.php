<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Database\Migrations\v610;

use Illuminate\Database\Schema\Blueprint;
use UserFrosting\Sprinkle\Account\Database\Migrations\v400\GroupsTable;
use UserFrosting\Sprinkle\Account\Database\Migrations\v400\RolesTable;
use UserFrosting\Sprinkle\Core\Database\Migration;

/**
 * Add soft deletion support to groups and roles.
 *
 * Version 6.1.0.
 */
class GroupAndRoleSoftDeletes extends Migration
{
    /**
     * {@inheritdoc}
     */
    public static $dependencies = [
        GroupsTable::class,
        RolesTable::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function up(): void
    {
        foreach (['groups', 'roles'] as $tableName) {
            if ($this->schema->hasTable($tableName) && !$this->schema->hasColumn($tableName, 'deleted_at')) {
                $this->schema->table($tableName, function (Blueprint $table) {
                    $table->softDeletes();
                });
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function down(): void
    {
        foreach (['groups', 'roles'] as $tableName) {
            if ($this->schema->hasTable($tableName) && $this->schema->hasColumn($tableName, 'deleted_at')) {
                $this->schema->table($tableName, function (Blueprint $table) {
                    $table->dropSoftDeletes();
                });
            }
        }
    }
}
