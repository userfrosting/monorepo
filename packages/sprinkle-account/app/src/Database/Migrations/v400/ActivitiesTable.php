<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Database\Migrations\v400;

use Illuminate\Database\Schema\Blueprint;
use UserFrosting\Sprinkle\Core\Database\Migration;

/**
 * Activities table migration
 * Version 4.0.0.
 */
class ActivitiesTable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(): void
    {
        if (!$this->schema->hasTable('activities')) {
            $this->schema->create('activities', function (Blueprint $table) {
                $table->increments('id');
                $table->string('ip_address', 45)->nullable();
                $table->integer('user_id')->unsigned();
                $table->string('type', 255)->comment('An identifier used to track the type of activity.');
                $table->timestamp('occurred_at')->nullable();
                $table->text('description')->nullable();

                $table->engine = 'InnoDB';
                $table->collation = 'utf8_unicode_ci';
                $table->charset = 'utf8';
                //$table->foreign('user_id')->references('id')->on('users');
                $table->index('user_id');
            });
        }
    }

    /**
     * {@inheritdoc}
     */
    public function down(): void
    {
        $this->schema->drop('activities');
    }
}
