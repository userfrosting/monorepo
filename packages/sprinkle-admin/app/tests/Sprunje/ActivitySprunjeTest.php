<?php

declare(strict_types=1);

/*
 * UserFrosting Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-admin
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Admin\Tests\Sprunje;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use stdClass;
use UserFrosting\I18n\DictionaryInterface;
use UserFrosting\I18n\Translator;
use UserFrosting\Sprinkle\Account\Database\Models\Activity;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Log\AccountActivityTypes;
use UserFrosting\Sprinkle\Account\Log\ActivityTypeRegistryInterface;
use UserFrosting\Sprinkle\Admin\Sprunje\ActivitySprunje;
use UserFrosting\Sprinkle\Admin\Tests\AdminTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

/**
 * Tests a ActivitySprunje.
 */
class ActivitySprunjeTest extends AdminTestCase
{
    use RefreshDatabase;
    use MockeryPHPUnitIntegration;

    /** @var EloquentCollection<int, User> */
    protected EloquentCollection $users;

    public function setUp(): void
    {
        parent::setUp();

        // Set database up.
        $this->refreshDatabase();
        $this->createData();
    }

    protected function createData(): void
    {
        $this->users = User::factory()
                    ->count(2)
                    ->sequence(fn ($sequence) => [
                        'first_name' => 'First ' . $sequence->index,
                        'last_name'  => 'Name ' . $sequence->index,
                    ])
                    ->hasActivities(3) // @phpstan-ignore-line
                    ->create();
    }

    public function testBaseSprunje(): void
    {
        /** @var ActivitySprunje */
        $sprunje = $this->getService(ActivitySprunje::class);
        $data = $sprunje->getArray();

        $this->assertEquals(6, $data['count']);
        $this->assertEquals(6, $data['count_filtered']);
        $this->assertCount(6, $data['rows']); // @phpstan-ignore-line
        $this->assertEquals([], $data['listable']);
    }

    public function testWithPagination(): void
    {
        /** @var ActivitySprunje */
        $sprunje = $this->getService(ActivitySprunje::class);
        $sprunje->setOptions([
            'size' => 1,
            'page' => 1, // First page is 0, so second row will be displayed.
        ]);
        $data = $sprunje->getArray();

        $this->assertEquals(6, $data['count']);
        $this->assertEquals(6, $data['count_filtered']);
        $this->assertCount(1, $data['rows']); // @phpstan-ignore-line
    }

    public function testWithUserSort(): void
    {
        /** @var ActivitySprunje */
        $sprunje = $this->getService(ActivitySprunje::class);
        $sprunje->setOptions([
            'sorts' => ['user' => 'desc'],
        ]);
        $data = $sprunje->getArray();

        $this->assertEquals(6, $data['count']);
        $this->assertEquals(6, $data['count_filtered']);
        $this->assertCount(6, $data['rows']); // @phpstan-ignore-line
        $this->assertEquals($this->users[1]->id, $data['rows'][0]['user']['id']); // @phpstan-ignore-line
        $this->assertEquals($this->users[1]->id, $data['rows'][1]['user']['id']); // @phpstan-ignore-line
        $this->assertEquals($this->users[1]->id, $data['rows'][2]['user']['id']); // @phpstan-ignore-line
        $this->assertEquals($this->users[0]->id, $data['rows'][3]['user']['id']); // @phpstan-ignore-line
        $this->assertEquals($this->users[0]->id, $data['rows'][4]['user']['id']); // @phpstan-ignore-line
        $this->assertEquals($this->users[0]->id, $data['rows'][5]['user']['id']); // @phpstan-ignore-line

        $sprunje->setOptions([
            'sorts' => ['user' => 'asc'],
        ]);
        $data = $sprunje->getArray();
        $this->assertEquals($this->users[0]->id, $data['rows'][0]['user']['id']); // @phpstan-ignore-line
        $this->assertEquals($this->users[0]->id, $data['rows'][1]['user']['id']); // @phpstan-ignore-line
        $this->assertEquals($this->users[0]->id, $data['rows'][2]['user']['id']); // @phpstan-ignore-line
        $this->assertEquals($this->users[1]->id, $data['rows'][3]['user']['id']); // @phpstan-ignore-line
        $this->assertEquals($this->users[1]->id, $data['rows'][4]['user']['id']); // @phpstan-ignore-line
        $this->assertEquals($this->users[1]->id, $data['rows'][5]['user']['id']); // @phpstan-ignore-line
    }

    public function testWithOccurredAtSort(): void
    {
        /** @var ActivitySprunje */
        $sprunje = $this->getService(ActivitySprunje::class);
        $sprunje->setOptions([
            'sorts' => ['occurred_at' => 'desc'],
        ]);
        $data = $sprunje->getArray();

        $this->assertEquals(6, $data['count']);
        $this->assertEquals(6, $data['count_filtered']);
        $this->assertCount(6, $data['rows']); // @phpstan-ignore-line
        $this->assertEquals(6, $data['rows'][0]['id']); // @phpstan-ignore-line

        $sprunje->setOptions([
            'sorts' => ['occurred_at' => 'asc'],
        ]);
        $data = $sprunje->getArray();
        $this->assertEquals(1, $data['rows'][0]['id']); // @phpstan-ignore-line
    }

    public function testWithMultipleSorts(): void
    {
        /** @var ActivitySprunje */
        $sprunje = $this->getService(ActivitySprunje::class);
        $sprunje->setOptions([
            'sorts' => [
                'occurred_at' => 'desc',
                'user'        => 'asc'
            ],
        ]);
        $data = $sprunje->getArray();

        $this->assertEquals(6, $data['count']);
        $this->assertEquals(6, $data['count_filtered']);
        $this->assertCount(6, $data['rows']); // @phpstan-ignore-line
        $this->assertEquals(6, $data['rows'][0]['id']); // @phpstan-ignore-line
    }

    public function testWithUserFilter(): void
    {
        /** @var ActivitySprunje */
        $sprunje = $this->getService(ActivitySprunje::class);
        $sprunje->setOptions([
            'filters' => ['user' => $this->users[0]->email], // @phpstan-ignore-line
        ]);
        $data = $sprunje->getArray();

        $this->assertEquals(6, $data['count']);
        $this->assertEquals(3, $data['count_filtered']);
        $this->assertCount(3, $data['rows']); // @phpstan-ignore-line

        // Filter by name
        $sprunje->setOptions([
            'filters' => ['user' => $this->users[0]->first_name], // @phpstan-ignore-line
        ]);
        $this->assertEquals(3, $sprunje->getArray()['count_filtered']);

        // Filter by last name
        $sprunje->setOptions([
            'filters' => ['user' => 'Name '], // @phpstan-ignore-line
        ]);
        $this->assertEquals(6, $sprunje->getArray()['count_filtered']);
    }

    public function testActivityDescriptions(): void
    {
        $userId = $this->users[0]->id;
        /** @var Activity $registered */
        $registered = Activity::factory()->create([
            'user_id'     => $userId,
            'type'        => AccountActivityTypes::UPDATE_FIELD->value,
            'metadata'    => ['field' => 'email'],
            'description' => 'Legacy registered description',
        ]);
        /** @var Activity $legacy */
        $legacy = Activity::factory()->create([
            'user_id'     => $userId,
            'type'        => 'unregistered_event',
            'description' => 'Legacy description',
        ]);
        /** @var Activity $raw */
        $raw = Activity::factory()->create([
            'user_id'     => $userId,
            'type'        => 'unknown_event',
            'description' => null,
        ]);

        /** @var ActivitySprunje */
        $sprunje = $this->getService(ActivitySprunje::class);
        $rows = $sprunje->getArray()['rows'];

        $rowsById = [];
        foreach ($rows as $row) {
            $rowsById[$row['id']] = $row;
        }

        $this->assertSame('Account field email updated', $rowsById[$registered->id]['description']);
        $this->assertSame('Legacy description', $rowsById[$legacy->id]['description']);
        $this->assertSame('unknown_event', $rowsById[$raw->id]['description']);
    }

    public function testConventionFallbackUsesMockedDictionary(): void
    {
        $activity = new Activity([
            'type'        => 'legacy_event',
            'description' => 'Legacy convention description',
        ]);

        /** @var Mockery\MockInterface&ActivityTypeRegistryInterface $registry */
        $registry = Mockery::mock(ActivityTypeRegistryInterface::class)
            ->shouldReceive('getI18nKey')->with('legacy_event')->once()->andReturnNull()
            ->getMock();

        /** @var Mockery\MockInterface&DictionaryInterface $dictionary */
        $dictionary = Mockery::mock(DictionaryInterface::class)
            ->shouldReceive('has')->with('ACTIVITY.TYPE.legacy_event')->once()->andReturnTrue()
            ->getMock();

        /** @var Mockery\MockInterface&Translator $translator */
        $translator = Mockery::mock(Translator::class)
            ->shouldReceive('getDictionary')->once()->andReturn($dictionary)
            ->shouldReceive('translate')->once()->with('ACTIVITY.TYPE.legacy_event', [
                'context' => null,
                'subject' => null,
            ])->andReturn('Legacy event')->getMock();

        $sprunje = new TestableActivitySprunje(new Activity(), $registry, $translator);
        $sprunje->transform(new Collection([$activity]));

        $this->assertSame('Legacy event', $activity->getAttribute('description'));
    }

    public function testActivityDescriptionPlaceholdersIncludeRelations(): void
    {
        $context = new stdClass();
        $subject = new stdClass();
        $activity = new Activity([
            'type'     => 'test_event',
            'metadata' => [
                'field'   => 'email',
                'context' => 'metadata context',
                'subject' => 'metadata subject',
            ],
        ]);
        $activity->setRelation('context', $context);
        $activity->setRelation('subject', $subject);

        /** @var Mockery\MockInterface&ActivityTypeRegistryInterface $registry */
        $registry = Mockery::mock(ActivityTypeRegistryInterface::class)
            ->shouldReceive('getI18nKey')->with('test_event')->once()->andReturn('TEST.ACTIVITY')
            ->getMock();

        /** @var Mockery\MockInterface&DictionaryInterface $dictionary */
        $dictionary = Mockery::mock(DictionaryInterface::class)
            ->shouldReceive('has')->with('TEST.ACTIVITY')->once()->andReturnTrue()
            ->getMock();

        /** @var Mockery\MockInterface&Translator $translator */
        $translator = Mockery::mock(Translator::class)
            ->shouldReceive('getDictionary')->once()->andReturn($dictionary)
            ->shouldReceive('translate')->once()->with('TEST.ACTIVITY', Mockery::on(function (array $placeholders) use ($context, $subject): bool {
                return $placeholders === [
                    'field'   => 'email',
                    'context' => $context,
                    'subject' => $subject,
                ];
            }))->andReturn('translated')->getMock();

        $sprunje = new TestableActivitySprunje(new Activity(), $registry, $translator);
        $sprunje->transform(new Collection([$activity]));

        $this->assertSame('translated', $activity->getAttribute('description'));
    }
}

class TestableActivitySprunje extends ActivitySprunje
{
    /**
     * @param Collection<int, Model> $collection
     *
     * @return Collection<int, Model>
     */
    public function transform(Collection $collection): Collection
    {
        return $this->applyTransformations($collection);
    }
}
