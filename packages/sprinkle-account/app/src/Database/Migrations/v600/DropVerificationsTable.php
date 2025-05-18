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
use UserFrosting\Sprinkle\Core\Database\Migration;

/**
 * Verifications table drop migration
 * Version 6.0.0.
 */
class DropVerificationsTable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(): void
    {
        $this->schema->dropIfExists('verifications');
    }

    /**
     * {@inheritdoc}
     */
    public function down(): void
    {
        if (!$this->schema->hasTable('verifications')) {
            $this->schema->create('verifications', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned();
                $table->string('hash');
                $table->boolean('completed')->default(0);
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->engine = 'InnoDB';
                $table->collation = 'utf8_unicode_ci';
                $table->charset = 'utf8';
                $table->foreign('user_id')->references('id')->on('users');
                $table->index('user_id');
                $table->index('hash');
            });
        }
    }
}
