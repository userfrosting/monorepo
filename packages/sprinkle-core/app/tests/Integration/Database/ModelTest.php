<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Core\Database\Migration;
use UserFrosting\Sprinkle\Core\Database\Models\Throttle;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase;

/**
 * Test for bug with `withTrashed` in `findUnique` not available when `SoftDeletes` trait is not included in a model.
 * @see https://chat.userfrosting.com/channel/support?msg=aAYvdwczSvBMzriJ6
 *
 * We'll use Throttle as out test model for this test.
 */
class ModelTest extends CoreTestCase
{
    use RefreshDatabase;

    /**
     * Setup the database schema.
     */
    public function setUp(): void
    {
        parent::setUp();

        // Setup test database
        $this->refreshDatabase();
    }

    public function testAttributeExists(): void
    {
        $model = new Throttle(['type' => 'test']);

        // IP is not an attribute when model is created.
        $this->assertTrue($model->attributeExists('type'));
        $this->assertFalse($model->attributeExists('ip'));
        $this->assertFalse($model->attributeExists('foo'));

        // IP is still not set when saved.
        $model->save();
        $this->assertTrue($model->attributeExists('type'));
        $this->assertFalse($model->attributeExists('ip'));
        $this->assertFalse($model->attributeExists('foo'));

        // IP is set when fetched from the db.
        $model->refresh();
        $this->assertTrue($model->attributeExists('type'));
        $this->assertTrue($model->attributeExists('ip'));
        $this->assertFalse($model->attributeExists('foo'));
    }

    /**
     * User Model does have the soft Delete
     */
    public function testFindUnique(): void
    {
        $model = new Throttle(['type' => 'test']);
        $model->save();

        $result = Throttle::findUnique('TeSt', 'type', true);
        $this->assertEquals('test', $result?->type);

        // Delete and try again
        $model->delete();
        $result = Throttle::findUnique('TeSt', 'type', true);
        $this->assertNull($result);
    }

    public function testFindUniqueWithTrashed(): void
    {
        // Run custom migration up
        /** @var Builder */
        $builder = $this->getService(Builder::class);
        $migration = new TestTables($builder);
        $migration->up();

        $model = new PizzaModel(['type' => 'test']);
        $model->save();

        $result = PizzaModel::findUnique('TeSt', 'type', true);
        $this->assertEquals('test', $result?->type);

        $model->delete();
        $result = PizzaModel::findUnique('TeSt', 'type', true);
        $this->assertEquals('test', $result?->type);

        $result = PizzaModel::findUnique('TeSt', 'type', false);
        $this->assertNull($result);

        // Revert custom migration
        $migration->down();
    }

    public function testRelationExists(): void
    {
        // Run custom migration up
        /** @var Builder */
        $builder = $this->getService(Builder::class);
        $migration = new TestTables($builder);
        $migration->up();

        $topping = new PizzaToppingModel();
        $topping->save();

        $model = new PizzaModel(['type' => 'test']);
        $model->topping()->associate($topping);

        // IP is not an attribute when model is created.
        $this->assertFalse($model->relationExists('user'));
        $this->assertTrue($model->relationExists('topping'));

        // Revert custom migration
        $migration->down();
    }

    public function testStore(): void
    {
        $model = new Throttle(['type' => 'test']);
        $id = $model->store();
        $this->assertSame(1, $id);
    }
}

class PizzaModel extends Throttle
{
    protected $table = 'test_pizza';

    use SoftDeletes;

    public function topping(): BelongsTo
    {
        return $this->belongsTo(PizzaToppingModel::class, 'id', 'topping_id');
    }
}

class PizzaToppingModel extends Throttle
{
    protected $table = 'test_topping';
}

/**
 * We need our own migration, as none of the models in the Core sprinkle have the `SoftDeletes` trait.
 */
class TestTables extends Migration
{
    public function up(): void
    {
        $this->schema->create('test_pizza', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type');
            $table->integer('topping_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        $this->schema->create('test_topping', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $this->schema->drop('test_pizza');
        $this->schema->drop('test_topping');
    }
}
