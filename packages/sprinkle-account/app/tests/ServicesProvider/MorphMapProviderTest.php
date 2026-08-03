<?php

declare(strict_types=1);

namespace UserFrosting\Sprinkle\Account\Tests\ServicesProvider;

use Illuminate\Database\Eloquent\Relations\Relation;
use PHPUnit\Framework\Attributes\CoversClass;
use UserFrosting\Sprinkle\Account\Database\Models\Activity;
use UserFrosting\Sprinkle\Account\Database\Models\Group;
use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Sprinkle\Account\Database\Models\Persistence;
use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Database\Models\UserVerification;
use UserFrosting\Sprinkle\Account\ServicesProvider\MorphMapProvider;
use UserFrosting\Sprinkle\Account\Tests\AccountTestCase;

#[CoversClass(MorphMapProvider::class)]
final class MorphMapProviderTest extends AccountTestCase
{
    public function testAccountModelsUseStableMorphAliases(): void
    {
        $expected = [
            'activity'          => Activity::class,
            'group'             => Group::class,
            'permission'        => Permission::class,
            'persistence'       => Persistence::class,
            'role'              => Role::class,
            'user'              => User::class,
            'user_verification' => UserVerification::class,
        ];

        $this->assertSame($expected, array_intersect_key(Relation::morphMap(), $expected));

        foreach ($expected as $alias => $model) {
            $this->assertSame($model, Relation::getMorphedModel($alias));
        }
    }
}
