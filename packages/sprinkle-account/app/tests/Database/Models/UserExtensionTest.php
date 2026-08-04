<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\Database\Models;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use UserFrosting\Sprinkle\Account\Database\Models\Group;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Tests\AccountTestCase;
use UserFrosting\Sprinkle\Core\Database\Migration;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

/**
 * Test integration when extending the User Model with as custom auxiliary model
 * @see https://learn.userfrosting.com/recipes/extending-the-user-model
 * @see https://github.com/userfrosting/UserFrosting/issues/1252
 */
class UserExtensionTest extends AccountTestCase
{
    use MockeryPHPUnitIntegration;
    use RefreshDatabase;

    /**
     * Setup the database schema.
     */
    public function setUp(): void
    {
        parent::setUp();

        // Setup test database
        $this->refreshDatabase();

        // Run custom migration up
        /** @var Builder */
        $builder = $this->ci->get(Builder::class);
        $migration = new MemberMigration($builder);
        $migration->up();

        $this->ci->set(UserInterface::class, Member::class);
    }

    protected function tearDown(): void
    {
        // Run custom migration down
        /** @var Builder */
        $builder = $this->ci->get(Builder::class);
        $migration = new MemberMigration($builder);
        $migration->down();

        parent::tearDown();
    }

    /**
     * Test User/Member relations setup are working, even if the class is extended
     */
    public function testRelations(): void
    {
        $member = new Member([
            'user_name'  => 'testing',
            'email'      => 'test@test.test',
            'first_name' => 'Test',
            'last_name'  => 'Ing',
            'password'   => 'secret',
            'city'       => 'Ze City',
        ]);
        $member->save();

        // Refetch member and assert values
        /** @var Member */
        $member = Member::find($member->id);
        $this->assertSame('Ze City', $member->city);

        // Fetch each relation - They each will be empty
        // A real query is required to trigger a SQL Exception
        $this->assertCount(0, $member->activities()->get());
        $this->assertCount(0, $member->group()->get());
        $this->assertCount(0, $member->persistences()->get());
        $this->assertCount(0, $member->permissions()->get());
        $this->assertCount(0, $member->roles()->get());
    }

    /**
     * Test Permission relation setup are working, even if the class is extended
     */
    public function testPermission(): void
    {
        $permission = new Permission([
            'name'       => 'Test',
            'slug'       => 'test',
            'conditions' => 'always()',
        ]);
        $this->assertCount(0, $permission->users()->get());
    }

    /**
     * Test Role relation setup are working, even if the class is extended
     */
    public function testRole(): void
    {
        $role = new Role([
            'name'  => 'Test',
            'slug'  => 'test',
        ]);

        // Assert relationship works as expected
        $this->assertCount(0, $role->users()->get());
    }

    public function testUserScopeForRoleWithJoin(): void
    {
        $role = new Role([
            'name'  => 'Test',
            'slug'  => 'test',
        ]);
        $role->save();

        // Assert "forUser" scope works as expected
        /** @var Member */
        $user = Member::factory()->hasAttached($role)->create();

        /** @var Role */
        $result = Role::forUser($user)->first(); // @phpstan-ignore-line
        $this->assertSame($role->id, $result->id);
    }

    /**
     * Test with Group
     */
    public function testGroup(): void
    {
        $group = new ZeGroup([
            'slug'  => 'testing',
            'name'  => 'Test Group',
        ]);
        $group->save();

        // Fetch each relation - They each will be empty
        // A real query is required to trigger a SQL Exception
        $this->assertCount(0, $group->users()->get());
    }
}

class ZeGroup extends Group
{
}

class Member extends User
{
    protected $fillable = [
        'user_name',
        'first_name',
        'last_name',
        'email',
        'locale',
        'group_id',
        'flag_verified',
        'flag_enabled',
        'last_activity_id',
        'password',
        'deleted_at',
        'city',
        'country',
    ];

    protected string $auxType = MemberAux::class;

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new MemberAuxScope());
    }

    protected static function booted(): void
    {
        static::saved(function (Member $member) {
            $member->createAuxIfNotExists();
            if ($member->aux->id !== 0) {
                $member->aux->id = $member->id;
            }
            $member->aux->save();
        });
    }

    public function setCityAttribute(string $value): void
    {
        $this->createAuxIfNotExists();
        $this->aux->city = $value;
    }

    public function setCountryAttribute(string $value): void
    {
        $this->createAuxIfNotExists();
        $this->aux->country = $value;
    }

    public function aux(): HasOne
    {
        return $this->hasOne($this->auxType, 'id');
    }

    protected function createAuxIfNotExists(): void
    {
        if ($this->auxType !== '' && is_null($this->aux)) {
            $aux = new $this->auxType();
            $this->setRelation('aux', $aux);
        }
    }
}

class MemberAux extends Model
{
    public $timestamps = false;
    protected $table = 'members';
    protected $fillable = [
        'city',
        'country',
    ];
}

class MemberAuxScope implements Scope
{
    public function apply(EloquentBuilder $builder, Model $model)
    {
        $baseTable = $model->getTable();
        $auxTable = 'members';
        // @phpstan-ignore-next-line
        $builder->addSelect(
            "$baseTable.*",
            "$auxTable.city as city",
            "$auxTable.country as country"
        );
        // @phpstan-ignore-next-line
        $builder->leftJoin($auxTable, function ($join) use ($baseTable, $auxTable) {
            $join->on("$auxTable.id", '=', "$baseTable.id");
        });
    }
}

class MemberMigration extends Migration
{
    public function up(): void
    {
        $this->schema->create('members', function (Blueprint $table) {
            $table->increments('id');
            $table->string('city', 255)->nullable();
            $table->string('country', 255)->nullable();

            $table->engine = 'InnoDB';
            $table->collation = 'utf8_unicode_ci';
            $table->charset = 'utf8';
            $table->foreign('id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        $this->schema->drop('members');
    }
}
