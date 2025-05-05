<?php

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\Twig;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Slim\Views\Twig;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Twig\AccountExtension;

/**
 * Tests Alerts twig extensions
 */
class AccountExtensionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testCheckAuthenticated(): void
    {
        /** @var UserInterface */
        $user = Mockery::mock(UserInterface::class)
            ->shouldReceive('foo')->once()->andReturn('blah') // Don't need anything special here
            ->getMock();

        /** @var Authenticator */
        $authenticator = Mockery::mock(Authenticator::class)
                ->shouldReceive('check')->once()->andReturn(true)
                ->shouldReceive('user')->once()->andReturn($user)
                ->getMock();

        // Create and add to extensions.
        $extensions = new AccountExtension($authenticator);

        // Create dumb Twig and test adding extension
        $view = Twig::create('');
        $view->addExtension($extensions);

        $result = $view->fetchFromString('{% if checkAuthenticated() %}TRUE{% else %}FALSE{% endif %}');
        $this->assertSame('TRUE', $result);

        // Will be empty, as we didn't setup a user for authenticator mock
        $result = $view->fetchFromString('{{ current_user.foo() }}');
        $this->assertSame('blah', $result);
    }

    public function testCheckAccess(): void
    {
        /** @var UserInterface */
        $user = Mockery::mock(UserInterface::class);

        /** @var Authenticator */
        $authenticator = Mockery::mock(Authenticator::class)
                ->shouldReceive('checkAccess')->with('foo', ['foo' => 'bar'])->once()->andReturn(true)
                ->shouldReceive('user')->once()->andReturn($user)
                ->getMock();

        // Create and add to extensions.
        $extensions = new AccountExtension($authenticator);

        // Create dumb Twig and test adding extension
        $view = Twig::create('');
        $view->addExtension($extensions);

        $result = $view->fetchFromString("{% if checkAccess('foo', {foo: 'bar'}) %}TRUE{% else %}FALSE{% endif %}");
        $this->assertSame('TRUE', $result);
    }

    public function testCurrentUser(): void
    {
        // Define mock Authenticator and register with Container
        /** @var Authenticator */
        $authenticator = Mockery::mock(Authenticator::class);

        // Create and add to extensions.
        $extensions = new AccountExtension($authenticator);

        // Create dumb Twig and test adding extension
        $view = Twig::create('');
        $view->addExtension($extensions);

        // Will be empty, as we didn't setup a user for authenticator mock
        $result = $view->fetchFromString('{{ current_user }}');
        $this->assertSame('', $result);
    }
}
