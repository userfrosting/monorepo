<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit\Sprunje;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use UserFrosting\Sprinkle\Core\Database\Builder as UfBuilder;
use UserFrosting\Sprinkle\Core\Database\Models\Model as UfModel;
use UserFrosting\Sprinkle\Core\Exceptions\ValidationException;
use UserFrosting\Sprinkle\Core\Sprunje\Sprunje;
use UserFrosting\Sprinkle\Core\Sprunje\SprunjeException;
use UserFrosting\Testing\ContainerStub;

/**
 * Tests a basic Sprunje.
 */
class SprunjeTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testFailedOptionValidation(): void
    {
        $this->expectException(ValidationException::class);

        // @phpstan-ignore-next-line (That's the point of this test)
        $sprunje = new SprunjeStub([
            'filters' => true,
        ]);
    }

    public function testConstructWithModelInjection(): void
    {
        $ci = ContainerStub::create();
        /** @var ModelSprunjeStub */
        $sprunje = $ci->get(ModelSprunjeStub::class);
        $builder = $sprunje->getQuery();

        // N.B.: Other mock assertions done in SprunjeTestModelStub
        $this->assertInstanceOf(EloquentBuilder::class, $builder);
    }

    public function testSetQuery(): void
    {
        $builder = Mockery::mock(EloquentBuilder::class);

        $sprunje = new SprunjeStub();
        $this->assertNotSame($builder, $sprunje->getQuery());
        $this->assertSame($builder, $sprunje->setQuery($builder)->getQuery());
    }

    public function testExtendQuery(): void
    {
        $sprunje = new SprunjeStub();
        $builder = $sprunje->getQuery();
        $builder->shouldReceive('where')->with('foo', 'bar')->once(); // @phpstan-ignore-line
        $sprunje->extendQuery(function (QueryBuilder $query) {
            $query->where('foo', 'bar');

            return $query;
        });
    }

    public function testSprunjeApplyFilters(): void
    {
        $sprunje = new SprunjeStub([
            'filters' => [
                'species' => 'Tyto',
            ],
        ]);

        /** @var UfBuilder&\Mockery\MockInterface */
        $builder = $sprunje->getQuery();

        // Need to mock the new Builder instance that Laravel spawns in the where() closure.
        // See https://stackoverflow.com/questions/20701679/mocking-callbacks-in-laravel-4-mockery
        $subQuery = Mockery::mock(UfBuilder::class)
            ->makePartial()
            ->shouldReceive('orWhere')->with('species', 'LIKE', '%Tyto%')->once()->andReturnSelf()
            ->getMock();
        $builder->shouldReceive('newQuery')->andReturn($subQuery);

        $sprunje->applyFilters($builder);
    }

    public function testSprunjeApplyFiltersForException(): void
    {
        $sprunje = new SprunjeStub([
            'filters' => [
                'not_species' => 'Tyto',
            ],
        ]);
        $this->expectException(SprunjeException::class);
        $sprunje->applyFilters($sprunje->getQuery());
    }

    public function testSprunjeApplySorts(): void
    {
        $sprunje = new SprunjeStub([
            'sorts' => [
                'species' => 'asc',
                'name'    => 'asc',
            ],
        ]);
        $builder = $sprunje->getQuery();
        $builder->shouldReceive('orderBy')->with('species', 'asc')->once(); // @phpstan-ignore-line
        $builder->shouldReceive('foo')->with('name', 'asc')->once(); // @phpstan-ignore-line (Foo is from sprunje sortName)
        $sprunje->applySorts($builder);
    }

    public function testSprunjeApplySortsForException(): void
    {
        $sprunje = new SprunjeStub([
            'sorts' => [
                'not_species' => 'asc',
            ],
        ]);
        $this->expectException(SprunjeException::class);
        $sprunje->applySorts($sprunje->getQuery());
    }

    public function testSprunjeApplyPagination(): void
    {
        $sprunje = new SprunjeStub([
            'page' => 2,
            'size' => 10,
        ]);
        $builder = $sprunje->getQuery();
        $builder->shouldReceive('skip')->with(20)->once()->andReturnSelf(); // @phpstan-ignore-line
        $builder->shouldReceive('take')->with(10)->once()->andReturnSelf(); // @phpstan-ignore-line
        $sprunje->applyPagination($builder);
    }

    public function testGetModels(): void
    {
        $data = [
            ['id' => '1', 'name' => 'Foo'],
            ['id' => '2', 'name' => 'Bar'],
        ];
        $sprunje = new SprunjeStub();
        $builder = $sprunje->getQuery();
        $builder->shouldReceive('count')->once()->andReturn(10); // @phpstan-ignore-line
        $builder->shouldReceive('count')->once()->andReturn(5); // @phpstan-ignore-line
        $builder->shouldReceive('get')->once()->andReturn($data); // @phpstan-ignore-line
        $model = $sprunje->getModels();
        $this->assertSame(10, $model[0]);
        $this->assertSame(5, $model[1]);
        $this->assertInstanceOf(Collection::class, $model[2]); // @phpstan-ignore-line
        $this->assertCount(2, $model[2]);
    }

    public function testGetListable(): void
    {
        $data = [
            ['species' => 'Foo'],
            ['species' => 'Bar'],
        ];
        $expectation = [
            ['value' => 'Foo', 'text' => 'Foo'],
            ['value' => 'Bar', 'text' => 'Bar'],
        ];
        $sprunje = new SprunjeStub();
        $builder = $sprunje->getQuery();
        $builder->shouldReceive('select')->with('species')->once()->andReturnSelf(); // @phpstan-ignore-line
        $builder->shouldReceive('distinct')->once()->andReturnSelf(); // @phpstan-ignore-line
        $builder->shouldReceive('orderBy')->with('species', 'asc')->once()->andReturnSelf(); // @phpstan-ignore-line
        $builder->shouldReceive('get')->once()->andReturn(new Collection($data)); // @phpstan-ignore-line
        $this->assertSame([
            'species' => $expectation,
            'name'    => ['name' => 'foobar'],
        ], $sprunje->getListable());
    }

    public function testGetArray(): void
    {
        $data = [
            ['id' => '1', 'name' => 'Foo'],
            ['id' => '2', 'name' => 'Bar'],
        ];
        $listable = [
            ['species' => 'Foo'],
            ['species' => 'Bar'],
        ];
        $listableExpectation = [
            ['value' => 'Foo', 'text' => 'Foo'],
            ['value' => 'Bar', 'text' => 'Bar'],
        ];
        $sprunje = new SprunjeStub();
        $builder = $sprunje->getQuery();
        $builder->shouldReceive('count')->once()->andReturn(10); // @phpstan-ignore-line
        $builder->shouldReceive('count')->once()->andReturn(5); // @phpstan-ignore-line
        $builder->shouldReceive('get')->once()->andReturn($data); // @phpstan-ignore-line
        $builder->shouldReceive('select')->with('species')->once()->andReturnSelf(); // @phpstan-ignore-line
        $builder->shouldReceive('distinct')->once()->andReturnSelf(); // @phpstan-ignore-line
        $builder->shouldReceive('orderBy')->with('species', 'asc')->once()->andReturnSelf(); // @phpstan-ignore-line
        $builder->shouldReceive('get')->once()->andReturn(new Collection($listable)); // @phpstan-ignore-line
        $result = $sprunje->getArray();
        $this->assertSame(10, $result['count']);
        $this->assertSame(5, $result['count_filtered']);
        $this->assertSame($data, $result['rows']);
        $this->assertSame([
            'species' => $listableExpectation,
            'name'    => ['name' => 'foobar'],
        ], $result['listable']);
    }

    public function testApplyTransformations(): void
    {
        $sprunje = new TransformedSprunjeStub([]);

        $builder = $sprunje->getQuery();
        // @phpstan-ignore-next-line
        $builder->shouldReceive('count')->andReturn(2);
        // @phpstan-ignore-next-line
        $builder->shouldReceive('get')->andReturn([
            ['id' => '1', 'name' => 'Foo'],
            ['id' => '2', 'name' => 'Bar'],
        ]);

        $result = $sprunje->getModels();

        $this->assertSame([
            ['id' => '1', 'name' => 'FooFoo'],
            ['id' => '2', 'name' => 'BarBar'],
        ], $result[2]->toArray());
    }
}

class SprunjeStub extends Sprunje
{
    protected array $filterable = [
        'species',
        'name',
    ];

    protected array $sortable = [
        'species',
        'name',
    ];

    protected array $listable = [
        'species',
        'name',
    ];

    protected function baseQuery()
    {
        // We use a partial mock for Builder, because we need to be able to run some of its actual methods.
        // For example, we need to be able to run the `where` method with a closure.
        $builder = Mockery::mock(UfBuilder::class);
        $builder->makePartial();

        return $builder;
    }

    protected function sortName(EloquentBuilder|QueryBuilder|Relation $query, string $direction): static
    {
        $query->foo('name', $direction); // @phpstan-ignore-line

        return $this;
    }

    protected function filterName(EloquentBuilder|QueryBuilder|Relation $query, string $value): static
    {
        $query->filter($value); // @phpstan-ignore-line

        return $this;
    }

    /** @return array<string, string> */
    protected function listName(): array
    {
        return ['name' => 'foobar'];
    }
}

class TransformedSprunjeStub extends SprunjeStub
{
    /**
     * @param Collection<int, \Illuminate\Database\Eloquent\Model> $collection
     *
     * @return Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    protected function applyTransformations(Collection $collection): Collection
    {
        $collection = $collection->map(function ($item, $key) {
            $item['name'] = $item['name'] . $item['name'];

            return $item;
        });

        return $collection;
    }
}

/**
 * Sprunje Stub with model injection.
 */
class ModelSprunjeStub extends Sprunje
{
    public function __construct(protected SprunjeTestModelStub $model, array $options = [])
    {
        parent::__construct($options);
    }

    protected function baseQuery()
    {
        return $this->model;
    }
}

class SprunjeTestModelStub extends UfModel
{
    protected $table = 'table';

    /**
     * Mock builder, used in `testConstructWithModelInjection`.
     */
    protected function newBaseQueryBuilder()
    {
        /** @var Mockery\MockInterface&UfBuilder */
        $builder = Mockery::mock(UfBuilder::class)
            ->makePartial()
            ->shouldReceive('from')->with('table')->once()
            ->getMock();

        return $builder;
    }
}
