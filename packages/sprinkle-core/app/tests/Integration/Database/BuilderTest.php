<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Database;

use Illuminate\Database\Schema\Builder;
use UserFrosting\Sprinkle\Core\Database\Models\Model;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase as TestCase;

/**
 * Test custom relations in `/src/Database/Relations`.
 */
class BuilderTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->createData();
    }

    protected function createData(): void
    {
        $schema = $this->ci->get(Builder::class);
        $schema->create('objects', function ($table) {
            $table->string('name');
            $table->string('description')->nullable();
        });

        $object = new TestObject(['name' => 'foo', 'description' => 'The Foo']);
        $object->save();

        $object = new TestObject(['name' => 'bar', 'description' => 'The Bar']);
        $object->save();
    }

    /**
     * Tear down the database schema.
     */
    public function tearDown(): void
    {
        $schema = $this->ci->get(Builder::class);
        $schema->drop('objects');

        parent::tearDown();
    }

    public function testGet(): void
    {
        /** @var \Illuminate\Support\Collection<int, TestObject> */
        $objects = TestObject::get();
        $this->assertEquals([
            ['name' => 'foo', 'description' => 'The Foo'],
            ['name' => 'bar', 'description' => 'The Bar'],
        ], $objects->toArray());
    }

    public function testExclude(): void
    {
        $objects = TestObject::exclude('description')->get();
        $this->assertEquals([
            ['name' => 'foo'],
            ['name' => 'bar'],
        ], $objects->toArray());
    }

    public function testLike(): void
    {
        /** @var \Illuminate\Support\Collection<int, TestObject> */
        $objects = TestObject::like('name', 'oo')->get();
        $this->assertEquals([
            ['name' => 'foo', 'description' => 'The Foo'],
        ], $objects->toArray());

        /** @var \Illuminate\Support\Collection<int, TestObject> */
        $objects = TestObject::like('description', 'null')->get();
        $this->assertEquals([], $objects->toArray());

        /** @var \Illuminate\Support\Collection<int, TestObject> */
        $objects = TestObject::like('description', 'The')->get();
        $this->assertEquals([
            ['name' => 'foo', 'description' => 'The Foo'],
            ['name' => 'bar', 'description' => 'The Bar'],
        ], $objects->toArray());
    }

    public function testOrLike(): void
    {
        /** @var \Illuminate\Support\Collection<int, TestObject> */
        $objects = TestObject::orLike('name', 'oo')->orLike('name', 'bar')->get();
        $this->assertEquals([
            ['name' => 'foo', 'description' => 'The Foo'],
            ['name' => 'bar', 'description' => 'The Bar'],
        ], $objects->toArray());

        /** @var \Illuminate\Support\Collection<int, TestObject> */
        $objects = TestObject::orLike('description', 'null')->orLike('description', 'New')->get();
        $this->assertEquals([], $objects->toArray());

        /** @var \Illuminate\Support\Collection<int, TestObject> */
        $objects = TestObject::orLike('description', 'Foo')->orLike('description', 'Bar')->get();
        $this->assertEquals([
            ['name' => 'foo', 'description' => 'The Foo'],
            ['name' => 'bar', 'description' => 'The Bar'],
        ], $objects->toArray());
    }

    public function testBeginsWith(): void
    {
        /** @var \Illuminate\Support\Collection<int, TestObject> */
        $objects = TestObject::beginsWith('name', 'f')->get();
        $this->assertEquals([
            ['name' => 'foo', 'description' => 'The Foo'],
        ], $objects->toArray());

        /** @var \Illuminate\Support\Collection<int, TestObject> */
        $objects = TestObject::beginsWith('description', 'Foo')->get();
        $this->assertEquals([], $objects->toArray());

        /** @var \Illuminate\Support\Collection<int, TestObject> */
        $objects = TestObject::beginsWith('description', 'The')->get();
        $this->assertEquals([
            ['name' => 'foo', 'description' => 'The Foo'],
            ['name' => 'bar', 'description' => 'The Bar'],
        ], $objects->toArray());
    }

    public function testEndsWith(): void
    {
        /** @var \Illuminate\Support\Collection<int, TestObject> */
        $objects = TestObject::endsWith('name', 'o')->get();
        $this->assertEquals([
            ['name' => 'foo', 'description' => 'The Foo'],
        ], $objects->toArray());

        /** @var \Illuminate\Support\Collection<int, TestObject> */
        $objects = TestObject::endsWith('description', 'The')->get();
        $this->assertEquals([], $objects->toArray());
    }
}

/**
 * @property int         $id
 * @property string      $name
 * @property string|null $description
 */
class TestObject extends Model
{
    protected $table = 'objects';
    public $timestamps = false;
    protected $primaryKey = null;
    public $incrementing = false;
    protected $fillable = [
        'name',
        'description',
    ];
}
