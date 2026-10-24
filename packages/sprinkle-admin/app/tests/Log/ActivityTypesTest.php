<?php

declare(strict_types=1);

namespace UserFrosting\Sprinkle\Admin\Tests\Log;

use PHPUnit\Framework\TestCase;
use UserFrosting\Sprinkle\Admin\Log\AdminAccountActivityTypes;
use UserFrosting\Sprinkle\Admin\Log\AdminActivityTypes;
use UserFrosting\Sprinkle\Admin\Log\GroupActivityTypes;
use UserFrosting\Sprinkle\Admin\Log\RoleActivityTypes;

class ActivityTypesTest extends TestCase
{
    /**
     * @dataProvider activityTypesProvider
     *
     * @param class-string $activityType
     */
    public function testActivityTypeTranslations(string $activityType, string $prefix): void
    {
        foreach ($activityType::cases() as $case) {
            $this->assertSame($prefix . '.' . $case->name, $activityType::getI18nKey($case->value));
            $this->assertSame($prefix . '.LABEL.' . $case->name, $activityType::getLabelI18nKey($case->value));
        }

        $this->assertNull($activityType::getI18nKey('unknown_activity'));
        $this->assertNull($activityType::getLabelI18nKey('unknown_activity'));
    }

    /** @return array<string, array{class-string, string}> */
    public static function activityTypesProvider(): array
    {
        return [
            'admin account' => [AdminAccountActivityTypes::class, 'ACCOUNT.ACTIVITY'],
            'admin'         => [AdminActivityTypes::class, 'ADMIN.ACTIVITY'],
            'group'         => [GroupActivityTypes::class, 'GROUP.ACTIVITY'],
            'role'          => [RoleActivityTypes::class, 'ROLE.ACTIVITY'],
        ];
    }
}
