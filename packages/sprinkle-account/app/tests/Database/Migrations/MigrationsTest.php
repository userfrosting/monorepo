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

use DateTimeImmutable;
use Illuminate\Database\Schema\Builder;
use UserFrosting\Sprinkle\Account\Database\Factories\UserFactory;
use UserFrosting\Sprinkle\Account\Database\Migrations\v400\ActivitiesTable;
use UserFrosting\Sprinkle\Account\Database\Migrations\v610\ActivitiesV2Table;
use UserFrosting\Sprinkle\Account\Database\Migrations\v610\AnonymizeSoftDeletedRecords;
use UserFrosting\Sprinkle\Account\Database\Migrations\v610\GroupAndRoleSoftDeletes;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Database\Seeds\DefaultGroups;
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
        $builder = $this->getService(Builder::class);

        /** @var Migrator */
        $migrator = $this->getService(Migrator::class);

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

    public function testAnonymizeSoftDeletedRecords(): void
    {
        /** @var Builder */
        $builder = $this->getService(Builder::class);

        /** @var Migrator */
        $migrator = $this->getService(Migrator::class);
        $migrator->reset();
        $migrator->migrate();

        $db = $builder->getConnection();
        $now = date('Y-m-d H:i:s');
        $db->table('users')->insert(['user_name' => 'old', 'email' => 'old@example.com', 'first_name' => 'Old', 'last_name' => 'User', 'password' => 'x', 'deleted_at' => $now]);
        $db->table('users')->insert(['user_name' => 'live', 'email' => 'live@example.com', 'first_name' => 'Live', 'last_name' => 'User', 'password' => 'x']);
        $db->table('groups')->insert(['slug' => 'oldg', 'name' => 'Old', 'deleted_at' => $now]);
        $db->table('roles')->insert(['slug' => 'oldr', 'name' => 'Old', 'deleted_at' => $now]);

        $migration = new AnonymizeSoftDeletedRecords($builder);
        $migration->up();
        $migration->up(); // Run again to ensure idempotency
        $migration->down();

        /** @var \stdClass $user */
        $user = $db->table('users')->whereNotNull('deleted_at')->first();
        $this->assertSame('deleted_user_' . $user->id, $user->user_name);
        $this->assertSame('USER.DELETED', $user->first_name);
        $this->assertSame('live', $db->table('users')->whereNull('deleted_at')->value('user_name'));
        $this->assertSame('GROUP.DELETED', $db->table('groups')->whereNotNull('deleted_at')->value('name'));
        $this->assertStringStartsWith('deleted_role_', $db->table('roles')->whereNotNull('deleted_at')->value('slug'));

        $migrator->rollback();
    }

    public function testActivitiesV2MigrationIsIdempotent(): void
    {
        /** @var Builder */
        $builder = $this->getService(Builder::class);

        /** @var Migrator */
        $migrator = $this->getService(Migrator::class);
        $migrator->reset();
        $migrator->migrate();

        $migration = new ActivitiesV2Table($builder);
        $migration->up();

        $this->assertTrue($builder->hasColumn('activities', 'context_type'));

        $migrator->rollback();
    }

    public function testActivitiesV2MigrationRemovesNullUserActivitiesOnDowngrade(): void
    {
        /** @var Builder */
        $builder = $this->getService(Builder::class);

        /** @var Migrator */
        $migrator = $this->getService(Migrator::class);
        $migrator->reset();
        $migrator->migrate();

        $builder->getConnection()->table('activities')->insert([
            'user_id'     => null,
            'type'        => 'test',
            'occurred_at' => new DateTimeImmutable(),
        ]);

        (new DefaultGroups())->run();
        /** @var User $user */
        $user = UserFactory::new()->create();
        $builder->getConnection()->table('activities')->insert([
            'user_id'     => $user->id,
            'type'        => 'test',
            'occurred_at' => new DateTimeImmutable(),
        ]);

        try {
            (new ActivitiesV2Table($builder))->down();
            $this->assertSame(1, $builder->getConnection()->table('activities')->count());
            $this->assertSame($user->id, $builder->getConnection()->table('activities')->value('user_id'));
        } finally {
            $builder->getConnection()->table('activities')->delete();
            $migrator->rollback();
        }
    }

    public function testGroupRoleSoftDeletesMigrationIsIdempotent(): void
    {
        /** @var Builder */
        $builder = $this->getService(Builder::class);

        /** @var Migrator */
        $migrator = $this->getService(Migrator::class);
        $migrator->reset();
        $migrator->migrate();

        $migration = new GroupAndRoleSoftDeletes($builder);
        $migration->up();

        $this->assertTrue($builder->hasColumn('groups', 'deleted_at'));
        $this->assertTrue($builder->hasColumn('roles', 'deleted_at'));

        $migrator->rollback();
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
                'context_id',
                'context_type',
                'subject_id',
                'subject_type',
                'metadata',
                'properties',
            ],
            'groups'           => [
                'id',
                'slug',
                'name',
                'description',
                'icon',
                'deleted_at',
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
                'deleted_at',
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
        ];
    }
}
