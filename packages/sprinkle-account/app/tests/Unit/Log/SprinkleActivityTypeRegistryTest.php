<?php

declare(strict_types=1);

namespace UserFrosting\Sprinkle\Account\Tests\Unit\Log;

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
use UserFrosting\Sprinkle\Admin\Log\GroupActivityTypes;
use UserFrosting\Sprinkle\Admin\Log\RoleActivityTypes;
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
                GroupActivityTypes::class,
                RoleActivityTypes::class,
            ])->getMock();
        $nonActivitySprinkle = Mockery::mock(SprinkleRecipe::class);
        /** @var SprinkleManager&Mockery\MockInterface $manager */
        $manager = Mockery::mock(SprinkleManager::class)
            ->shouldReceive('getSprinkles')->andReturn([$nonActivitySprinkle, $recipe])->getMock();

        $registry = new SprinkleActivityTypeRegistry($manager);

        $this->assertCount(20, $registry->all());
        $this->assertCount(20, $registry->all());
        $this->assertTrue($registry->has(UserActivityTypes::class));
        $this->assertInstanceOf(UserActivityTypes::class, $registry->get(UserActivityTypes::class));
        $this->assertSame(20, $registry->count());
        $this->assertSame('ACCOUNT.ACTIVITY.REGISTER', $registry->getI18nKey('sign_up'));
        $this->assertSame('ACCOUNT.ACTIVITY.LABEL.REGISTER', $registry->getLabelI18nKey('sign_up'));
        $this->assertSame('ROLE.ACTIVITY.UPDATE_FIELD', $registry->getI18nKey('role_update_field'));
        $this->assertSame('ROLE.ACTIVITY.LABEL.UPDATE_FIELD', $registry->getLabelI18nKey('role_update_field'));
        $this->assertNull($registry->getI18nKey('unknown_activity'));
        $this->assertNull($registry->getLabelI18nKey('unknown_activity'));

        $this->assertSame('ACCOUNT.ACTIVITY.VERIFIED', UserActivityTypes::getI18nKey('verified'));
        $this->assertSame('ACCOUNT.ACTIVITY.PASSWORD_RESET', UserActivityTypes::getI18nKey('password_reset'));
        $this->assertSame('ACCOUNT.ACTIVITY.LOGGED_IN', UserActivityTypes::getI18nKey('sign_in'));
        $this->assertSame('ACCOUNT.ACTIVITY.LOGGED_OUT', UserActivityTypes::getI18nKey('sign_out'));
        $this->assertSame('ACCOUNT.ACTIVITY.PASSWORD_UPGRADED', UserActivityTypes::getI18nKey('password_upgraded'));
        $this->assertSame('ACCOUNT.ACTIVITY.CREATE', AccountActivityTypes::getI18nKey('account_create'));
        $this->assertSame('ACCOUNT.ACTIVITY.DELETE', AccountActivityTypes::getI18nKey('account_delete'));
        $this->assertSame('ACCOUNT.ACTIVITY.UPDATE_INFO', AccountActivityTypes::getI18nKey('account_update_info'));
        $this->assertSame('ACCOUNT.ACTIVITY.UPDATE_FIELD', AccountActivityTypes::getI18nKey('account_update_field'));
        $this->assertSame('ACCOUNT.ACTIVITY.UPDATE_PROFILE_SETTINGS', AccountActivityTypes::getI18nKey('update_profile_settings'));
        $this->assertSame('ACCOUNT.ACTIVITY.UPDATE_ACCOUNT_SETTINGS', AccountActivityTypes::getI18nKey('update_account_settings'));
        $this->assertSame('GROUP.ACTIVITY.CREATE', GroupActivityTypes::getI18nKey('group_create'));
        $this->assertSame('GROUP.ACTIVITY.DELETE', GroupActivityTypes::getI18nKey('group_delete'));
        $this->assertSame('GROUP.ACTIVITY.UPDATE_INFO', GroupActivityTypes::getI18nKey('group_update_info'));
        $this->assertSame('ROLE.ACTIVITY.CREATE', RoleActivityTypes::getI18nKey('role_create'));
        $this->assertSame('ROLE.ACTIVITY.DELETE', RoleActivityTypes::getI18nKey('role_delete'));
        $this->assertSame('ROLE.ACTIVITY.UPDATE_INFO', RoleActivityTypes::getI18nKey('role_update_info'));

        $this->assertSame([
            'ACCOUNT.ACTIVITY.LABEL.REGISTER',
            'ACCOUNT.ACTIVITY.LABEL.VERIFIED',
            'ACCOUNT.ACTIVITY.LABEL.PASSWORD_RESET',
            'ACCOUNT.ACTIVITY.LABEL.LOGGED_IN',
            'ACCOUNT.ACTIVITY.LABEL.LOGGED_OUT',
            'ACCOUNT.ACTIVITY.LABEL.PASSWORD_UPGRADED',
        ], array_map(
            static fn (UserActivityTypes $activityType): ?string => UserActivityTypes::getLabelI18nKey($activityType->value),
            UserActivityTypes::cases()
        ));
        $this->assertSame([
            'ACCOUNT.ACTIVITY.LABEL.CREATE',
            'ACCOUNT.ACTIVITY.LABEL.DELETE',
            'ACCOUNT.ACTIVITY.LABEL.UPDATE_INFO',
            'ACCOUNT.ACTIVITY.LABEL.UPDATE_FIELD',
            'ACCOUNT.ACTIVITY.LABEL.UPDATE_PROFILE_SETTINGS',
            'ACCOUNT.ACTIVITY.LABEL.UPDATE_ACCOUNT_SETTINGS',
            'ACCOUNT.ACTIVITY.LABEL.UPDATE_EMAIL',
        ], array_map(
            static fn (AccountActivityTypes $activityType): ?string => AccountActivityTypes::getLabelI18nKey($activityType->value),
            AccountActivityTypes::cases()
        ));
        $this->assertSame([
            'GROUP.ACTIVITY.LABEL.CREATE',
            'GROUP.ACTIVITY.LABEL.DELETE',
            'GROUP.ACTIVITY.LABEL.UPDATE_INFO',
        ], array_map(
            static fn (GroupActivityTypes $activityType): ?string => GroupActivityTypes::getLabelI18nKey($activityType->value),
            GroupActivityTypes::cases()
        ));
        $this->assertSame([
            'ROLE.ACTIVITY.LABEL.CREATE',
            'ROLE.ACTIVITY.LABEL.DELETE',
            'ROLE.ACTIVITY.LABEL.UPDATE_INFO',
            'ROLE.ACTIVITY.LABEL.UPDATE_FIELD',
        ], array_map(
            static fn (RoleActivityTypes $activityType): ?string => RoleActivityTypes::getLabelI18nKey($activityType->value),
            RoleActivityTypes::cases()
        ));
        $this->assertNull(UserActivityTypes::getI18nKey('unknown_activity'));
        $this->assertNull(AccountActivityTypes::getI18nKey('unknown_activity'));
        $this->assertNull(GroupActivityTypes::getI18nKey('unknown_activity'));
        $this->assertNull(RoleActivityTypes::getI18nKey('unknown_activity'));
        $this->assertNull(UserActivityTypes::getLabelI18nKey('unknown_activity'));
        $this->assertNull(AccountActivityTypes::getLabelI18nKey('unknown_activity'));
        $this->assertNull(GroupActivityTypes::getLabelI18nKey('unknown_activity'));
        $this->assertNull(RoleActivityTypes::getLabelI18nKey('unknown_activity'));
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
