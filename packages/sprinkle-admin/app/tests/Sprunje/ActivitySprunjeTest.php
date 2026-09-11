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
use UserFrosting\Sprinkle\Admin\Log\AdminAccountActivityTypes;
use UserFrosting\Sprinkle\Admin\Log\GroupActivityTypes;
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
        $this->assertArrayHasKey('label', $data['listable']); // @phpstan-ignore-line
        $this->assertContains([
            'value' => AccountActivityTypes::CREATE->value,
            'text'  => 'Account created',
        ], $data['listable']['label']); // @phpstan-ignore-line
        $this->assertContains('label', $data['sortable']); // @phpstan-ignore-line
        $this->assertContains('label', $data['filterable']); // @phpstan-ignore-line
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

    public function testWithLabelFilter(): void
    {
        $userId = $this->users[0]->id;
        $created = Activity::factory()->create([
            'user_id' => $userId,
            'type'    => AccountActivityTypes::CREATE->value,
        ]);
        $deleted = Activity::factory()->create([
            'user_id' => $userId,
            'type'    => AccountActivityTypes::DELETE->value,
        ]);

        /** @var ActivitySprunje */
        $sprunje = $this->getService(ActivitySprunje::class);
        $sprunje->setOptions([
            'filters' => [
                'label' => AccountActivityTypes::CREATE->value . '||' . AccountActivityTypes::DELETE->value,
            ],
        ]);
        $data = $sprunje->getArray();

        $this->assertEquals(8, $data['count']);
        $this->assertEquals(2, $data['count_filtered']);
        $this->assertEqualsCanonicalizing([$created->id, $deleted->id], array_column($data['rows'], 'id')); // @phpstan-ignore-line

        $sprunje->setOptions([
            'filters' => ['label' => 'Account created'],
        ]);
        $this->assertEquals(0, $sprunje->getArray()['count_filtered']);
    }

    public function testWithLocalizedLabelSortAndPagination(): void
    {
        $userId = $this->users[0]->id;
        Activity::factory()->create([
            'user_id' => $userId,
            'type'    => AccountActivityTypes::DELETE->value,
        ]);
        $groupCreated = Activity::factory()->create([
            'user_id' => $userId,
            'type'    => GroupActivityTypes::CREATE->value,
        ]);
        Activity::factory()->create([
            'user_id' => $userId,
            'type'    => 'sign_up',
        ]);
        $filter = implode('||', [
            AccountActivityTypes::DELETE->value,
            GroupActivityTypes::CREATE->value,
            'sign_up',
        ]);

        /** @var ActivitySprunje */
        $sprunje = $this->getService(ActivitySprunje::class);
        $sprunje->setOptions([
            'filters' => ['label' => $filter],
            'sorts'   => ['label' => 'asc'],
        ]);
        $data = $sprunje->getArray();

        $this->assertEquals(9, $data['count']);
        $this->assertEquals(3, $data['count_filtered']);
        $this->assertSame([
            AccountActivityTypes::DELETE->value,
            GroupActivityTypes::CREATE->value,
            'sign_up',
        ], array_column($data['rows'], 'type')); // @phpstan-ignore-line
        $this->assertSame('Account deleted', $data['rows'][0]['label']); // @phpstan-ignore-line

        $sprunje->setOptions([
            'sorts' => ['label' => 'desc'],
        ]);
        $data = $sprunje->getArray();
        $this->assertSame([
            'sign_up',
            GroupActivityTypes::CREATE->value,
            AccountActivityTypes::DELETE->value,
        ], array_column($data['rows'], 'type')); // @phpstan-ignore-line

        $sprunje->setOptions([
            'sorts'  => ['label' => 'asc'],
            'size'   => 1,
            'page'   => 1,
        ]);
        $data = $sprunje->getArray();
        $this->assertEquals(9, $data['count']);
        $this->assertEquals(3, $data['count_filtered']);
        $this->assertCount(1, $data['rows']); // @phpstan-ignore-line
        $this->assertSame($groupCreated->id, $data['rows'][0]['id']); // @phpstan-ignore-line
    }

    public function testLabelSortUsesIdAsDeterministicTieBreaker(): void
    {
        $userId = $this->users[0]->id;
        $first = Activity::factory()->create([
            'user_id' => $userId,
            'type'    => AccountActivityTypes::CREATE->value,
        ]);
        $second = Activity::factory()->create([
            'user_id' => $userId,
            'type'    => AccountActivityTypes::CREATE->value,
        ]);

        /** @var ActivitySprunje */
        $sprunje = $this->getService(ActivitySprunje::class);
        $sprunje->setOptions([
            'filters' => ['label' => AccountActivityTypes::CREATE->value],
            'sorts'   => ['label' => 'asc'],
        ]);
        $data = $sprunje->getArray();
        $this->assertSame([$first->id, $second->id], array_column($data['rows'], 'id')); // @phpstan-ignore-line

        $sprunje->setOptions([
            'sorts' => ['label' => 'desc'],
        ]);
        $data = $sprunje->getArray();
        $this->assertSame([$second->id, $first->id], array_column($data['rows'], 'id')); // @phpstan-ignore-line
    }

    public function testActivityDescriptions(): void
    {
        $userId = $this->users[0]->id;
        /** @var Activity $registered */
        $registered = Activity::factory()->create([
            'user_id'     => $userId,
            'type'        => AdminAccountActivityTypes::UPDATE_FIELD->value,
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
        $this->assertSame('Account field updated', $rowsById[$registered->id]['label']);
        $this->assertSame('Legacy description', $rowsById[$legacy->id]['description']);
        $this->assertSame('unregistered_event', $rowsById[$legacy->id]['label']);
        $this->assertSame('unknown_event', $rowsById[$raw->id]['description']);
        $this->assertSame('unknown_event', $rowsById[$raw->id]['label']);
        $this->assertArrayNotHasKey('metadata', $rowsById[$registered->id]);
    }

    public function testActorlessActivityIsVisible(): void
    {
        /** @var Activity */
        $activity = Activity::factory()->create([
            'user_id' => null,
            'type'    => 'actorless_event',
        ]);

        /** @var ActivitySprunje */
        $sprunje = $this->getService(ActivitySprunje::class);
        $rows = $sprunje->getArray()['rows'];
        $rowsById = [];
        foreach ($rows as $row) {
            $rowsById[$row['id']] = $row;
        }

        $this->assertArrayHasKey($activity->id, $rowsById);
        $this->assertNull($rowsById[$activity->id]['user']);
    }

    public function testConventionFallbackUsesMockedDictionary(): void
    {
        $activity = new Activity([
            'type'        => 'legacy_event',
            'description' => 'Legacy convention description',
        ]);

        /** @var Mockery\MockInterface&ActivityTypeRegistryInterface $registry */
        $registry = Mockery::mock(ActivityTypeRegistryInterface::class)
            ->shouldReceive('getLabelI18nKey')->with('legacy_event')->once()->andReturnNull()
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
            ->shouldReceive('getLabelI18nKey')->with('test_event')->once()->andReturnNull()
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

    public function testMissingLabelTranslationFallsBackToPersistedType(): void
    {
        $activity = new Activity([
            'type'        => 'test_event',
            'description' => 'Legacy description',
        ]);

        /** @var Mockery\MockInterface&ActivityTypeRegistryInterface $registry */
        $registry = Mockery::mock(ActivityTypeRegistryInterface::class)
            ->shouldReceive('getLabelI18nKey')->with('test_event')->once()->andReturn('TEST.LABEL')
            ->shouldReceive('getI18nKey')->with('test_event')->once()->andReturn('TEST.DESCRIPTION')
            ->getMock();

        /** @var Mockery\MockInterface&DictionaryInterface $dictionary */
        $dictionary = Mockery::mock(DictionaryInterface::class)
            ->shouldReceive('has')->with('TEST.LABEL')->once()->andReturnFalse()
            ->shouldReceive('has')->with('TEST.DESCRIPTION')->once()->andReturnTrue()
            ->getMock();

        /** @var Mockery\MockInterface&Translator $translator */
        $translator = Mockery::mock(Translator::class)
            ->shouldReceive('getDictionary')->once()->andReturn($dictionary)
            ->shouldReceive('translate')->once()->with('TEST.DESCRIPTION', [
                'context' => null,
                'subject' => null,
            ])->andReturn('Translated description')->getMock();

        $sprunje = new TestableActivitySprunje(new Activity(), $registry, $translator);
        $sprunje->transform(new Collection([$activity]));

        $this->assertSame('test_event', $activity->getAttribute('label'));
        $this->assertSame('Translated description', $activity->getAttribute('description'));
    }

    public function testCsvIncludesLabelAndTranslatedDescription(): void
    {
        Activity::factory()->create([
            'user_id'     => $this->users[0]->id,
            'type'        => AdminAccountActivityTypes::UPDATE_FIELD->value,
            'metadata'    => ['field' => 'email'],
            'description' => 'Legacy description',
        ]);

        /** @var ActivitySprunje */
        $sprunje = $this->getService(ActivitySprunje::class);
        $sprunje->setOptions([
            'filters' => ['label' => AdminAccountActivityTypes::UPDATE_FIELD->value],
        ]);
        $csv = $sprunje->getCsv()->toString();

        $this->assertStringContainsString('label', $csv);
        $this->assertStringContainsString('Account field updated', $csv);
        $this->assertStringContainsString('description', $csv);
        $this->assertStringContainsString('Account field email updated', $csv);
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
