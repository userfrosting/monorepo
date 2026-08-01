<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit\ServicesProvider;

use PHPUnit\Framework\TestCase;
use UserFrosting\Event\AppInitiatedEvent;
use UserFrosting\Event\BakeryInitiatedEvent;
use UserFrosting\Sprinkle\Core\Database\Models\Session;
use UserFrosting\Sprinkle\Core\Listeners\ModelInitiated;
use UserFrosting\Testing\ContainerStub;

class ModelInitiatedTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Reset $ci to default for next test
        Session::$ci = null;
    }

    public function testModelInitiatedWithApp(): void
    {
        $ci = ContainerStub::create();
        $event = new AppInitiatedEvent();
        $listener = new ModelInitiated($ci);
        $model = new Session();

        $this->assertNull($model::$ci);
        $listener($event);
        $this->assertSame($ci, $model::$ci); // @phpstan-ignore-line

        // Manually remove $ci for next test
        $model::$ci = null;
    }

    public function testModelInitiatedWithBakery(): void
    {
        $ci = ContainerStub::create();
        $event = new BakeryInitiatedEvent();
        $listener = new ModelInitiated($ci);
        $model = new Session();

        $this->assertNull($model::$ci);
        $listener($event);
        $this->assertSame($ci, $model::$ci); // @phpstan-ignore-line

        // Manually remove $ci for next test
        $model::$ci = null;
    }
}
