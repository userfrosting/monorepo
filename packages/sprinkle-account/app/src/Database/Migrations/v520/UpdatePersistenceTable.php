<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Database\Migrations\v520;

use Illuminate\Database\Schema\Blueprint;
use UserFrosting\Sprinkle\Account\Database\Migrations\v400\PersistencesTable;
use UserFrosting\Sprinkle\Core\Database\Migration;

/**
 * Persistence table update migration
 * Changes `token` & `persistent_token` columns to support SHA-256 hashes.
 * Version 5.2.0.
 */
class UpdatePersistenceTable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public static $dependencies = [
        PersistencesTable::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function up(): void
    {
        // Clean table of old tokens, to allow for the new encoding.
        $this->emptyTable();

        $this->schema->table('persistences', function (Blueprint $table) {
            $table->char('token', 64)->change();
            $table->char('persistent_token', 64)->change();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function down(): void
    {
        // Clean table of tokens, not compatible with the old encoding.
        $this->emptyTable();

        $this->schema->table('persistences', function (Blueprint $table) {
            $table->string('token', 40)->change();
            $table->string('persistent_token', 40)->change();
        });
    }

    protected function emptyTable(): void
    {
        $this->schema->getConnection()->table('persistences')->truncate();
    }
}
