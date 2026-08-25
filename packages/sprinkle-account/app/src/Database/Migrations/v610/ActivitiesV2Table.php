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
use UserFrosting\Sprinkle\Core\Database\Migration;

/**
 * Activities table migration
 * Version 6.1.0.
 *
 * Adds context and subject columns to the activities table to support
 * polymorphic relationships. Following this version, "type" will be used to
 * identify the type of activity using a string identifier. "subject" identifies
 * the model the action was performed on, while "context" identifies an
 * additional related model. "metadata" stores translation data and
 * "properties" stores changed subject values.
 */
class ActivitiesV2Table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(): void
    {
        if (!$this->schema->hasColumn('activities', 'context_type')) {
            $this->schema->table('activities', function (Blueprint $table) {
                $table->string('context_type', 100)->nullable();
                $table->char('context_id', 36)->nullable();
                $table->string('subject_type', 100)->nullable();
                $table->char('subject_id', 36)->nullable();
                $table->json('metadata')->nullable();
                $table->json('properties')->nullable();

                $table->index(['context_type', 'context_id']);
                $table->index(['subject_type', 'subject_id']);

                // Make user_id nullable to support activities that are not associated with a user (eg. system activities).
                $table->unsignedInteger('user_id')->nullable()->change();
            });
        }
    }

    /**
     * {@inheritdoc}
     */
    public function down(): void
    {
        if ($this->schema->hasColumn('activities', 'context_type')) {
            $this->schema->withoutForeignKeyConstraints(function () {
                $this->schema->table('activities', function (Blueprint $table) {
                    $table->dropColumn([
                        'context_type',
                        'context_id',
                        'subject_type',
                        'subject_id',
                        'metadata',
                        'properties',
                    ]);

                    $table->unsignedInteger('user_id')->nullable(false)->change();
                });
            });
        }
    }
}
