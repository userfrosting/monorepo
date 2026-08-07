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
 * identify the type of activity using a string identifier, while "context" and
 * "subject" will be used to identify the related entities. "description" is
 * kept for backward compatibility and won't be used anymore, as it's replaced
 * by "metadata" which is a JSON column that can store any additional
 * information related to the activity.
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

                $table->index(['context_type', 'context_id']);
                $table->index(['subject_type', 'subject_id']);
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
                    ]);
                });
            });
        }
    }
}
