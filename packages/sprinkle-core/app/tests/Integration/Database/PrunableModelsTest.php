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

use Carbon\Carbon;
use Illuminate\Database\Connection;
use LogicException;
use UserFrosting\Sprinkle\Core\Database\Models\Session;
use UserFrosting\Sprinkle\Core\Database\Models\Throttle;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase;
use UserFrosting\Sprinkle\Core\Throttle\Throttler;
use UserFrosting\Sprinkle\Core\Throttle\ThrottleRule;

class PrunableModelsTest extends CoreTestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
    }

    public function testSessionPruningUsesConfiguredLifetime(): void
    {
        /** @var Connection */
        $connection = $this->getService(Connection::class);
        $connection->table('sessions')->insert([
            [
                'id'            => 'expired',
                'payload'       => '',
                'last_activity' => Carbon::now()->subMinutes(121)->timestamp,
            ],
            [
                'id'            => 'active',
                'payload'       => '',
                'last_activity' => Carbon::now()->subMinutes(119)->timestamp,
            ],
        ]);

        $this->assertSame(1, (new Session())->pruneAll());
        $this->assertNull(Session::find('expired'));
        $this->assertNotNull(Session::find('active'));
    }

    public function testSessionPruningRequiresInitializedContainer(): void
    {
        $container = Session::$ci;
        Session::$ci = null;

        try {
            $this->expectException(LogicException::class);
            (new Session())->prunable();
        } finally {
            Session::$ci = $container;
        }
    }

    public function testThrottlePruningPreservesEventsWithinConfiguredWindows(): void
    {
        /** @var Throttler */
        $throttler = $this->getService(Throttler::class);
        $throttler->addThrottleRule('disabled', null);
        $throttler->addThrottleRule('short', new ThrottleRule('ip', 60, [2 => 10]));
        $throttler->addThrottleRule('long', new ThrottleRule('ip', 120, [2 => 30, 3 => 15]));

        /** @var Connection */
        $connection = $this->getService(Connection::class);
        $connection->table('throttles')->insert([
            [
                'type'       => 'long',
                'created_at' => Carbon::now()->subDays(31),
                'updated_at' => Carbon::now()->subDays(31),
            ],
            [
                'type'       => 'long',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

        $this->assertSame(1, (new Throttle())->pruneAll());
        $this->assertSame(1, Throttle::count());
    }

    public function testThrottlePruningRequiresInitializedContainer(): void
    {
        $container = Throttle::$ci;
        Throttle::$ci = null;

        try {
            $this->expectException(LogicException::class);
            (new Throttle())->prunable();
        } finally {
            Throttle::$ci = $container;
        }
    }
}
