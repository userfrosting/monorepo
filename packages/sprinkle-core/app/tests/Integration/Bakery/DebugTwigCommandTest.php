<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Bakery;

use UserFrosting\Sprinkle\Core\Bakery\DebugTwigCommand;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase;
use UserFrosting\Testing\BakeryTester;

/**
 * Test DebugTwigCommand
 *
 * Warning: As with most bakery command testing, this test make sure all code
 * is executed and doesn't throw errors, but the actual display is not tested.
 */
class DebugTwigCommandTest extends CoreTestCase
{
    public function testCommand(): void
    {
        /** @var DebugTwigCommand */
        $command = $this->getService(DebugTwigCommand::class);
        $result = BakeryTester::runCommand($command);
        $this->assertSame(0, $result->getStatusCode());
    }
}
