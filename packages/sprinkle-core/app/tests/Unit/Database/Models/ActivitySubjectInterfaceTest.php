<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit\Database\Models;

use PHPUnit\Framework\TestCase;
use UserFrosting\Sprinkle\Core\Database\Models\Interfaces\MorphableModelInterface;
use UserFrosting\Sprinkle\Core\Database\Models\MigrationTable;
use UserFrosting\Sprinkle\Core\Database\Models\Session;
use UserFrosting\Sprinkle\Core\Database\Models\Throttle;

class ActivitySubjectInterfaceTest extends TestCase
{
    public function testBuiltinModelsImplementActivitySubjectInterface(): void
    {
        $models = [
            new MigrationTable(),
            new Session(),
            new Throttle(),
        ];

        foreach ($models as $model) {
            // @phpstan-ignore-next-line method.alreadyNarrowedType
            $this->assertInstanceOf(MorphableModelInterface::class, $model);
        }
    }
}
