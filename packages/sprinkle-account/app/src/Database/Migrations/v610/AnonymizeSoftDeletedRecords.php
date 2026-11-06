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

use UserFrosting\Sprinkle\Core\Database\Migration;

/**
 * Anonymize existing soft deleted users, groups and roles.
 * Version 6.1.0.
 *
 * Soft deleted records keep their unique identifiers (username, email, slug),
 * which prevents creating a new record with the same values. This
 * anonymizes them so they can be reused. The anonymization is NOT reverted by
 * `down()`, as the original data is lost.
 */
class AnonymizeSoftDeletedRecords extends Migration
{
    /**
     * {@inheritdoc}
     */
    public static $dependencies = [
        GroupAndRoleSoftDeletes::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function up(): void
    {
        $db = $this->schema->getConnection();

        $db->table('users')->whereNotNull('deleted_at')->orderBy('id')->each(function ($user) use ($db) {
            $db->table('users')->where('id', $user->id)->update([
                'user_name'    => 'deleted_user_' . $user->id,
                'email'        => 'deleted_user_' . $user->id . '@deleted.invalid',
                'first_name'   => 'USER.DELETED',
                'last_name'    => '',
                'password'     => bin2hex(random_bytes(32)),
                'flag_enabled' => 0,
            ]);
        });

        foreach (['groups' => 'group', 'roles' => 'role'] as $table => $type) {
            $db->table($table)->whereNotNull('deleted_at')->orderBy('id')->each(function ($row) use ($db, $table, $type) {
                $db->table($table)->where('id', $row->id)->update([
                    'slug'        => 'deleted_' . $type . '_' . $row->id,
                    'name'        => strtoupper($type) . '.DELETED',
                    'description' => '',
                ]);
            });
        }
    }

    /**
     * {@inheritdoc}
     */
    public function down(): void
    {
        // Anonymization can't be reverted.
    }
}
