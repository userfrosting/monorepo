<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\Database\Migrations;

use Illuminate\Database\Schema\Builder;
use UserFrosting\Sprinkle\Account\Database\Migrations\v400\ActivitiesTable;
use UserFrosting\Sprinkle\Account\Tests\AccountTestCase;
use UserFrosting\Sprinkle\Core\Database\Migrator\Migrator;

/**
 * ActivitiesTable Migration Test.
 */
class MigrationsTest extends AccountTestCase
{
    public function testMigrations(): void
    {
        /** @var Builder */
        $builder = $this->ci->get(Builder::class);

        /** @var Migrator */
        $migrator = $this->ci->get(Migrator::class);

        // Initiate migrations
        $migrator->reset();
        $migrator->migrate();

        // Assert state for each tables
        foreach ($this->tablesProvider() as $table => $expectation) {
            $result = $builder->getColumnListing($table);
            sort($expectation);
            sort($result);
            $this->assertSame($expectation, $result);
        }

        // Reset database
        $migrator->rollback();

        // Redo assertions for each (now empty) table
        foreach ($this->tablesProvider() as $table => $columns) {
            $this->assertSame([], $builder->getColumnListing($table));
        }
    }

    /** @return array<string, string[]> */
    public function tablesProvider(): array
    {
        return [
            'activities'       => [
                'description',
                'id',
                'ip_address',
                'occurred_at',
                'type',
                'user_id',
            ],
            'groups'           => [
                'id',
                'slug',
                'name',
                'description',
                'icon',
                'created_at',
                'updated_at',
            ],
            'password_resets'  => [
                'id',
                'user_id',
                'hash',
                'completed',
                'expires_at',
                'completed_at',
                'created_at',
                'updated_at',
            ],
            'permission_roles' => [
                'permission_id',
                'role_id',
                'created_at',
                'updated_at',
            ],
            'permissions'      => [
                'id',
                'slug',
                'name',
                'conditions',
                'description',
                'created_at',
                'updated_at',
            ],
            'persistences'     => [
                'id',
                'user_id',
                'token',
                'persistent_token',
                'expires_at',
                'created_at',
                'updated_at',
            ],
            'roles'            => [
                'id',
                'slug',
                'name',
                'description',
                'created_at',
                'updated_at',
            ],
            'role_users'       => [
                'user_id',
                'role_id',
                'created_at',
                'updated_at',
            ],
            'users'            => [
                'id',
                'user_name',
                'email',
                'first_name',
                'last_name',
                'locale',
                'group_id',
                'flag_verified',
                'flag_enabled',
                'password',
                'password_last_set',
                'deleted_at',
                'created_at',
                'updated_at',
            ],
            'verifications'    => [
                'id',
                'user_id',
                'hash',
                'completed',
                'expires_at',
                'completed_at',
                'created_at',
                'updated_at',
            ],
        ];
    }
}
