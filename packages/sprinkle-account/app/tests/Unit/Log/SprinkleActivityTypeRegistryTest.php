<?php

declare(strict_types=1);

namespace UserFrosting\Sprinkle\Account\Tests\Unit\Log;

use BackedEnum;
use LogicException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use stdClass;
use UserFrosting\Sprinkle\Account\Log\AccountActivityTypes;
use UserFrosting\Sprinkle\Account\Log\ActivityTypes;
use UserFrosting\Sprinkle\Account\Log\SprinkleActivityTypeRegistry;
use UserFrosting\Sprinkle\Account\Log\UserActivityTypes;
use UserFrosting\Sprinkle\Account\Sprinkle\Recipe\ActivityRecipe;
use UserFrosting\Sprinkle\SprinkleManager;
use UserFrosting\Sprinkle\SprinkleRecipe;
use UserFrosting\Support\Exception\BadClassNameException;
use UserFrosting\Support\Exception\BadInstanceOfException;

enum DuplicateActivityType: string implements ActivityTypes
{
    case DUPLICATE = 'sign_up';

    public static function getI18nKey(string $value): ?string
    {
        return null;
    }

    public static function getLabelI18nKey(string $value): ?string
    {
        return null;
    }
}

class SprinkleActivityTypeRegistryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testActivityTypesAreExpandedAndResolved(): void
    {
        $recipe = Mockery::mock(ActivityRecipe::class)
            ->shouldReceive('getActivityTypes')->andReturn([
                UserActivityTypes::class,
                AccountActivityTypes::class,
            ])->getMock();
        $nonActivitySprinkle = Mockery::mock(SprinkleRecipe::class);
        /** @var SprinkleManager&Mockery\MockInterface $manager */
        $manager = Mockery::mock(SprinkleManager::class)
            ->shouldReceive('getSprinkles')->andReturn([$nonActivitySprinkle, $recipe])->getMock();

        $registry = new SprinkleActivityTypeRegistry($manager);

        $this->assertTrue($registry->has(UserActivityTypes::class));
        $this->assertInstanceOf(UserActivityTypes::class, $registry->get(UserActivityTypes::class));

        foreach ([
            'sign_up' => ['ACCOUNT.ACTIVITY.REGISTER', 'ACCOUNT.ACTIVITY.LABEL.REGISTER'],
        ] as $value => [$expectedI18nKey, $expectedLabelI18nKey]) {
            $this->assertSame($expectedI18nKey, $registry->getI18nKey($value));
            $this->assertSame($expectedLabelI18nKey, $registry->getLabelI18nKey($value));
        }
        $this->assertNull($registry->getI18nKey('unknown_activity'));
        $this->assertNull($registry->getLabelI18nKey('unknown_activity'));

        /** @var array<class-string<BackedEnum&ActivityTypes>, array<string, string>> $expectedI18nKeys */
        $expectedI18nKeys = [
            UserActivityTypes::class => [
                'verified'          => 'ACCOUNT.ACTIVITY.VERIFIED',
                'password_reset'    => 'ACCOUNT.ACTIVITY.PASSWORD_RESET',
                'sign_in'           => 'ACCOUNT.ACTIVITY.LOGGED_IN',
                'sign_out'          => 'ACCOUNT.ACTIVITY.LOGGED_OUT',
                'password_upgraded' => 'ACCOUNT.ACTIVITY.PASSWORD_UPGRADED',
            ],
            AccountActivityTypes::class => [
                'account_create'              => 'ACCOUNT.ACTIVITY.CREATE',
                'account_delete'              => 'ACCOUNT.ACTIVITY.DELETE',
                'update_profile_settings'     => 'ACCOUNT.ACTIVITY.UPDATE_PROFILE_SETTINGS',
                'update_account_settings'     => 'ACCOUNT.ACTIVITY.UPDATE_ACCOUNT_SETTINGS',
                'update_password'             => 'ACCOUNT.ACTIVITY.UPDATE_PASSWORD',
            ],
        ];
        foreach ($expectedI18nKeys as $activityTypeClass => $expectedKeys) {
            foreach ($expectedKeys as $value => $expectedKey) {
                $this->assertSame($expectedKey, $activityTypeClass::getI18nKey($value));
            }
        }

        /** @var array<class-string<BackedEnum&ActivityTypes>, list<string>> $expectedLabelI18nKeys */
        $expectedLabelI18nKeys = [
            UserActivityTypes::class => [
                'ACCOUNT.ACTIVITY.LABEL.REGISTER',
                'ACCOUNT.ACTIVITY.LABEL.VERIFIED',
                'ACCOUNT.ACTIVITY.LABEL.PASSWORD_RESET',
                'ACCOUNT.ACTIVITY.LABEL.LOGGED_IN',
                'ACCOUNT.ACTIVITY.LABEL.LOGGED_OUT',
                'ACCOUNT.ACTIVITY.LABEL.PASSWORD_UPGRADED',
            ],
            AccountActivityTypes::class => [
                'ACCOUNT.ACTIVITY.LABEL.CREATE',
                'ACCOUNT.ACTIVITY.LABEL.DELETE',
                'ACCOUNT.ACTIVITY.LABEL.UPDATE_PROFILE_SETTINGS',
                'ACCOUNT.ACTIVITY.LABEL.UPDATE_ACCOUNT_SETTINGS',
                'ACCOUNT.ACTIVITY.LABEL.UPDATE_EMAIL',
                'ACCOUNT.ACTIVITY.LABEL.UPDATE_PASSWORD',
            ],
        ];
        foreach ($expectedLabelI18nKeys as $activityTypeClass => $expectedKeys) {
            $this->assertSame($expectedKeys, array_map(
                static fn (BackedEnum&ActivityTypes $activityType): ?string => $activityTypeClass::getLabelI18nKey((string) $activityType->value),
                $activityTypeClass::cases()
            ));
        }

        foreach (array_keys($expectedI18nKeys) as $activityTypeClass) {
            $this->assertNull($activityTypeClass::getI18nKey('unknown_activity'));
            $this->assertNull($activityTypeClass::getLabelI18nKey('unknown_activity'));
        }
    }

    public function testMissingClassIsRejected(): void
    {
        $recipe = Mockery::mock(ActivityRecipe::class)
            ->shouldReceive('getActivityTypes')->andReturn(['/Not/An/ActivityType'])->getMock();
        /** @var SprinkleManager&Mockery\MockInterface $manager */
        $manager = Mockery::mock(SprinkleManager::class)
            ->shouldReceive('getSprinkles')->andReturn([$recipe])->getMock();
        $registry = new SprinkleActivityTypeRegistry($manager);

        $this->expectException(BadClassNameException::class);
        $this->expectExceptionMessage('Activity type class `/Not/An/ActivityType` not found.');
        $registry->all();
    }

    public function testClassWithWrongInterfaceIsRejected(): void
    {
        $recipe = Mockery::mock(ActivityRecipe::class)
            ->shouldReceive('getActivityTypes')->andReturn([stdClass::class])->getMock();
        /** @var SprinkleManager&Mockery\MockInterface $manager */
        $manager = Mockery::mock(SprinkleManager::class)
            ->shouldReceive('getSprinkles')->andReturn([$recipe])->getMock();
        $registry = new SprinkleActivityTypeRegistry($manager);

        $this->expectException(BadInstanceOfException::class);
        $this->expectExceptionMessage('Activity type class `stdClass` doesn\'t implement ' . ActivityTypes::class);
        $registry->all();
    }

    public function testDuplicateValuesAreRejected(): void
    {
        $recipe = Mockery::mock(ActivityRecipe::class)
            ->shouldReceive('getActivityTypes')->andReturn([
                UserActivityTypes::class,
                DuplicateActivityType::class,
            ])->getMock();
        /** @var SprinkleManager&Mockery\MockInterface $manager */
        $manager = Mockery::mock(SprinkleManager::class)
            ->shouldReceive('getSprinkles')->andReturn([$recipe])->getMock();
        $registry = new SprinkleActivityTypeRegistry($manager);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Activity type value `sign_up` is registered more than once.');
        $registry->all();
    }
}
