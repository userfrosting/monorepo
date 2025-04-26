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

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Core\Log\MailLoggerInterface;
use UserFrosting\Sprinkle\Core\Mail\Mailer;
use UserFrosting\Sprinkle\Core\ServicesProvider\MailService;
use UserFrosting\Testing\ContainerStub;

/**
 * Integration tests for `mailer` service.
 * Check to see if service returns what it's supposed to return
 */
class MailerServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testService(): void
    {
        // Create container with provider to test
        $provider = new MailService();
        $ci = ContainerStub::create($provider->register());

        // Set dependencies services
        $config = Mockery::mock(Config::class)
            ->shouldReceive('get')->with('mail')->andReturn(['mailer' => 'sendmail'])->once()
            ->getMock();
        $ci->set(Config::class, $config);

        // Set dependencies services
        $logger = Mockery::mock(MailLoggerInterface::class);
        $ci->set(MailLoggerInterface::class, $logger);

        // Assertions
        $ci->get(Mailer::class);
    }
}
