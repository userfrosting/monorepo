<?php

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\Authorize;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use UserFrosting\Config\Config;
use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Sprinkle\Account\Account;
use UserFrosting\Sprinkle\Account\Authorize\AccessConditionEvaluator;
use UserFrosting\Sprinkle\Account\Authorize\AccessConditions;
use UserFrosting\Sprinkle\Account\Authorize\AccessConditionsInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Group;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Log\AuthLoggerInterface;
use UserFrosting\Sprinkle\Account\ServicesProvider\AccessConditionsService;
use UserFrosting\Sprinkle\Account\ServicesProvider\AuthorizationService;
use UserFrosting\Sprinkle\Account\ServicesProvider\AuthService;
use UserFrosting\Sprinkle\Account\ServicesProvider\I18nService;
use UserFrosting\Sprinkle\Account\ServicesProvider\LoggersService;
use UserFrosting\Sprinkle\Account\ServicesProvider\ModelsService;
use UserFrosting\Sprinkle\Account\Tests\AccountTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

class AccessConditionEvaluatorTest extends AccountTestCase
{
    use RefreshDatabase;
    use MockeryPHPUnitIntegration;

    /** @var AuthLoggerInterface|\Mockery\MockInterface */
    protected AuthLoggerInterface $logger;

    protected string $mainSprinkle = CustomAccount::class;

    /**
     * Setup the test database.
     */
    public function setUp(): void
    {
        parent::setUp();

        // We'll test using the `debug.auth` on.
        /** @var Config */
        $config = $this->getService(Config::class);
        $config->set('debug.auth', true);
        $config->set('reserved_user_ids.master', 1);

        // We'll test using a mock AuthLoggerInterface, to not get our dirty test into
        // the real log.
        $this->logger = Mockery::mock(AuthLoggerInterface::class);
        $this->getContainer()->set(AuthLoggerInterface::class, $this->logger);
    }

    public function testEvaluate(): void
    {
        /** @var AccessConditionEvaluator */
        $ace = $this->getService(AccessConditionEvaluator::class);

        /** @var User */
        $user = User::factory()->make();

        // Set logger expectations.
        $this->logger->shouldReceive('debug')->with("Evaluating access condition 'always()' with parameters:", ['self' => $user->toArray()])->once();
        $this->logger->shouldReceive('debug')->with("Evaluating callback 'always'...")->once();
        $this->logger->shouldReceive('debug')->with('Result: 1')->once();
        $this->logger->shouldReceive('debug')->with("Expression '1' evaluates to true")->once();

        $result = $ace->evaluate('always()', [], $user);
        $this->assertTrue($result);
    }

    public function testEvaluateWithAuthorizationException(): void
    {
        /** @var AccessConditionEvaluator */
        $ace = $this->getService(AccessConditionEvaluator::class);

        /** @var User */
        $user = User::factory()->make();

        // Set logger expectations.
        $this->logger->shouldReceive('debug')->with("Evaluating access condition 'equals_num(self.group_id,user.group_id)' with parameters:", ['self' => $user->toArray()])->once();
        $this->logger->shouldReceive('debug')->with("Error parsing access condition 'equals_num(self.group_id,user.group_id)': Cannot resolve the path \"self . group_id\". Error at token \"group_id\".")->once();

        $result = $ace->evaluate('equals_num(self.group_id,user.group_id)', [], $user);
        $this->assertFalse($result);
    }

    public function testEvaluateWithParams(): void
    {
        /** @var AccessConditionEvaluator */
        $ace = $this->getService(AccessConditionEvaluator::class);

        /** @var User */
        $user = User::factory([
            'group_id' => 1,
        ])->make();

        // Set logger expectations.
        $this->logger->shouldReceive('debug')->with("Evaluating access condition 'equals_num(self.group_id,user.group_id)' with parameters:", [
            'user' => $user,
            'self' => $user->toArray()
        ])->once();
        $this->logger->shouldReceive('debug')->with("Evaluating callback 'equals_num' on: ", [
            ['expression' => 'self . group_id', 'type' => 'parameter', 'resolved_value' => 1],
            ['expression' => 'user . group_id', 'type' => 'parameter', 'resolved_value' => 1],
        ])->once();
        $this->logger->shouldReceive('debug')->with('Result: 1')->once();
        $this->logger->shouldReceive('debug')->with("Expression '1' evaluates to true")->once();

        $result = $ace->evaluate('equals_num(self.group_id,user.group_id)', ['user' => $user], $user);
        $this->assertTrue($result);
    }

    public function testEvaluateWithNonAccessConditions(): void
    {
        /** @var AccessConditionEvaluator */
        $ace = $this->getService(AccessConditionEvaluator::class);

        // Set logger expectations.
        $this->logger->shouldReceive('debug')->with("Evaluating access condition 'foo()' with parameters:", [])->once();
        $this->logger->shouldReceive('debug')->with("Evaluating callback 'foo'...")->once();
        $this->logger->shouldReceive('debug')->with("Error parsing access condition 'foo()': Authorization failed: Access condition method 'foo' does not exist.")->once();

        $result = $ace->evaluate('foo()');
        $this->assertFalse($result);
    }

    public function testEvaluateWithArrayNode(): void
    {
        /** @var AccessConditionEvaluator */
        $ace = $this->getService(AccessConditionEvaluator::class);

        $this->logger->shouldReceive('debug')->times(4);
        $result = $ace->evaluate("subset(['group'],fields)", ['fields' => ['group', 'foobar']]);
        $this->assertTrue($result);
    }

    public function testEvaluateWithArrayAndKeysNode(): void
    {
        /** @var AccessConditionEvaluator */
        $ace = $this->getService(AccessConditionEvaluator::class);

        $this->logger->shouldReceive('debug')->times(4);
        $result = $ace->evaluate("subset(['foo' => 'group'],fields)", ['fields' => ['foo' => 'group']]);
        $this->assertTrue($result);
    }

    public function testEvaluateWithNumberNode(): void
    {
        /** @var AccessConditionEvaluator */
        $ace = $this->getService(AccessConditionEvaluator::class);

        $this->logger->shouldReceive('debug')->times(4);
        $result = $ace->evaluate('equals_num(1, 1)');
        $this->assertTrue($result);
    }

    public function testEvaluateWithStringNode(): void
    {
        /** @var AccessConditionEvaluator */
        $ace = $this->getService(AccessConditionEvaluator::class);

        $this->logger->shouldReceive('debug')->times(4);
        $result = $ace->evaluate("equals('1', '1')");
        $this->assertTrue($result);
    }

    public function testEvaluateWithDecimalNode(): void
    {
        /** @var AccessConditionEvaluator */
        $ace = $this->getService(AccessConditionEvaluator::class);

        $this->logger->shouldReceive('debug')->times(4);
        $result = $ace->evaluate('equals(1.1, 1.1)');
        $this->assertTrue($result);
    }

    public function testEvaluateWithUnknownNode(): void
    {
        /** @var AccessConditionEvaluator */
        $ace = $this->getService(AccessConditionEvaluator::class);

        $this->logger->shouldReceive('debug')->times(4);
        $result = $ace->evaluate('equals(__LINE__, __LINE__)'); // As "MagicConst" node.
        $this->assertTrue($result);
    }

    public function testEvaluateWithRelation(): void
    {
        // Require database
        $this->refreshDatabase();

        /** @var AccessConditionEvaluator */
        $ace = $this->getService(AccessConditionEvaluator::class);

        /** @var User */
        $user = User::factory()->create();

        /** @var Group */
        $group = Group::factory()->create();

        // Attach group to user.
        $user->group()->associate($group);

        $this->logger->shouldReceive('debug')->times(4);
        $result = $ace->evaluate('equals_num(self.group.id,user.group.id)', ['user' => $user], $user);
        $this->assertTrue($result);
    }

    public function testCustomCondition(): void
    {
        /** @var AccessConditionEvaluator */
        $ace = $this->getService(AccessConditionEvaluator::class);

        // Set logger expectations.
        $this->logger->shouldReceive('debug')->times(8);

        $this->assertFalse($ace->evaluate("equals_foo('bar')"));
        $this->assertTrue($ace->evaluate("equals_foo('foo')"));
    }

    /*public function testMoreCustomCondition(): void
    {
        /** @var AccessConditionEvaluator * /
        $ace = $this->getService(AccessConditionEvaluator::class);

        // Set logger expectations.
        $this->logger->shouldReceive('debug')->times(8);

        $this->assertFalse($ace->evaluate("equals_bar('foo')"));
        $this->assertTrue($ace->evaluate("equals_bar('bar')"));
    }*/
}

class CustomAccount extends Account
{
    public function getServices(): array
    {
        return [
            AccessConditionsService::class,
            CustomAccessConditionsService::class,
            // MoreCustomAccessConditionsService::class,
            AuthorizationService::class,
            AuthService::class,
            ModelsService::class,
            I18nService::class,
            LoggersService::class,

        ];
    }
}

class CustomAccessConditions extends AccessConditions
{
    public function equals_foo(string $string): bool
    {
        return $string === 'foo';
    }
}

class CustomAccessConditionsService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            AccessConditionsInterface::class => \DI\autowire(CustomAccessConditions::class),
        ];
    }
}

/*class MoreCustomAccessConditionsService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            AccessConditionsInterface::class => \DI\decorate(function (AccessConditionsInterface $ac) {
                $ac['equals_bar'] = function (string $string): bool {
                    return $string === 'bar';
                };
            }),
        ];
    }
}*/
