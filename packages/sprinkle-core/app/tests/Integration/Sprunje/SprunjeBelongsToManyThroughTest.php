<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Sprunje;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use UserFrosting\Sprinkle\Core\Database\Migration;
use UserFrosting\Sprinkle\Core\Database\Models\Model as UfModel;
use UserFrosting\Sprinkle\Core\Database\Relations\BelongsToManyThrough;
use UserFrosting\Sprinkle\Core\Sprunje\Sprunje;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase;

/**
 * The goal of this test is to make sure "roles_via" is present in the sprunje
 * data when using BelongsToManyThrough relation.
 */
class SprunjeBelongsToManyThroughTest extends CoreTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // Run custom migration up
        /** @var Builder */
        $builder = $this->getService(Builder::class);
        (new TestTableUsersMigration($builder))->up();
        (new TestRolesTableMigration($builder))->up();
        (new TestPermissionsTableMigration($builder))->up();
        (new TestRoleUsersTableMigration($builder))->up();
        (new TestRolePermissionsTableMigration($builder))->up();

        // Insert some data
        $this->createData();
    }

    public function tearDown(): void
    {
        // Run custom migration down
        /** @var Builder */
        $builder = $this->getService(Builder::class);
        (new TestTableUsersMigration($builder))->down();
        (new TestRolesTableMigration($builder))->down();
        (new TestPermissionsTableMigration($builder))->down();
        (new TestRoleUsersTableMigration($builder))->down();
        (new TestRolePermissionsTableMigration($builder))->down();

        parent::tearDown();
    }

    protected function createData(): void
    {
        (new TestSprunjeUserModel([
            'id'          => 1,
            'name'        => 'foo',
        ]))->save();

        (new TestSprunjeRoleModel([
            'id'              => 1,
            'name'            => 'Role of Foo',
        ]))->save();

        (new TestSprunjePermissionModel([
            'id'              => 1,
            'name'            => 'Permission for Role of Foo',
        ]))->save();

        (new TestSprunjeRoleUserModel([
            'user_id'         => 1,
            'role_id'         => 1,
        ]))->save();

        (new TestSprunjeRolePermissionsModel([
            'permission_id'   => 1,
            'role_id'         => 1,
        ]))->save();
    }

    public function testBaseSprunje(): void
    {
        /** @var TestSprunjeUserModel */
        $user = TestSprunjeUserModel::find(1);
        $sprunje = new TestBelongsToManyThroughSprunje($user);
        $data = $sprunje->getArray();

        $this->assertEquals([
            'count'          => 1,
            'count_filtered' => 1,
            'rows'           => [
                [
                    'id'            => 1,
                    'name'          => 'Permission for Role of Foo',
                    'roles_via'     => [
                        [
                            'id'    => 1,
                            'name'  => 'Role of Foo',
                            'pivot' => [
                                'permission_id' => 1,
                                'role_id'       => 1,
                            ],
                        ],
                    ],
                    'pivot' => [
                        'user_id'       => 1,
                        'permission_id' => 1,
                    ]
                ],
            ],
            'listable'       => [],
            'sortable'       => [],
            'filterable'     => [],
        ], $data);
    }
}

/**
 * Custom sprunje for testing. Will return a list of permissions the user has.
 */
class TestBelongsToManyThroughSprunje extends Sprunje
{
    public function __construct(protected TestSprunjeUserModel $user)
    {
        parent::__construct();
    }

    protected function baseQuery()
    {
        return $this->user->permissions()->withVia('roles_via');
    }
}

/**
 * Custom models for testing, and adding test data to database.
 */
class TestSprunjeUserModel extends UfModel
{
    protected $table = 'test_sprunje_users';
    protected $fillable = ['name'];

    /** @var bool */
    public $timestamps = false;

    public function permissions(): BelongsToManyThrough
    {
        return $this->belongsToManyThrough(
            TestSprunjePermissionModel::class,
            TestSprunjeRoleModel::class,
            firstJoiningTable: 'test_sprunje_role_users',
            firstForeignPivotKey: 'user_id',
            firstRelatedKey: 'role_id',
            secondJoiningTable: 'test_sprunje_role_permissions',
            secondForeignPivotKey: 'role_id',
            secondRelatedKey: 'permission_id',
        );
    }
}

class TestSprunjeRoleModel extends UfModel
{
    protected $table = 'test_sprunje_roles';
    protected $fillable = ['name'];

    /** @var bool */
    public $timestamps = false;
}

class TestSprunjePermissionModel extends UfModel
{
    protected $table = 'test_sprunje_permissions';
    protected $fillable = ['name'];

    /** @var bool */
    public $timestamps = false;
}

class TestSprunjeRoleUserModel extends UfModel
{
    protected $table = 'test_sprunje_role_users';
    protected $fillable = ['user_id', 'role_id'];
    protected $primaryKey = null;
    public $incrementing = false;

    /** @var bool */
    public $timestamps = false;
}

class TestSprunjeRolePermissionsModel extends UfModel
{
    protected $table = 'test_sprunje_role_permissions';
    protected $fillable = ['permission_id', 'role_id'];
    protected $primaryKey = null;
    public $incrementing = false;

    /** @var bool */
    public $timestamps = false;
}

/**
 * Custom migration for testing.
 */
class TestTableUsersMigration extends Migration
{
    public function up(): void
    {
        $this->schema->create('test_sprunje_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });
    }

    public function down(): void
    {
        $this->schema->drop('test_sprunje_users');
    }
}

class TestRolesTableMigration extends Migration
{
    public function up(): void
    {
        $this->schema->create('test_sprunje_roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });
    }

    public function down(): void
    {
        $this->schema->drop('test_sprunje_roles');
    }
}

class TestPermissionsTableMigration extends Migration
{
    public function up(): void
    {
        $this->schema->create('test_sprunje_permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });
    }

    public function down(): void
    {
        $this->schema->drop('test_sprunje_permissions');
    }
}

class TestRoleUsersTableMigration extends Migration
{
    public function up(): void
    {
        $this->schema->create('test_sprunje_role_users', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->integer('role_id')->unsigned();
        });
    }

    public function down(): void
    {
        $this->schema->drop('test_sprunje_role_users');
    }
}

class TestRolePermissionsTableMigration extends Migration
{
    public function up(): void
    {
        $this->schema->create('test_sprunje_role_permissions', function (Blueprint $table) {
            $table->integer('permission_id')->unsigned();
            $table->integer('role_id')->unsigned();
        });
    }

    public function down(): void
    {
        $this->schema->drop('test_sprunje_role_permissions');
    }
}
