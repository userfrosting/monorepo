<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Mail;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;
use PHPUnit\Framework\TestCase;
use UserFrosting\Sprinkle\Core\Log\MailLoggerInterface;
use UserFrosting\Sprinkle\Core\Mail\EmailRecipient;
use UserFrosting\Sprinkle\Core\Mail\Mailer;
use UserFrosting\Sprinkle\Core\Mail\StaticMailMessage;

/**
 * StaticMailMessageTest
 */
class MailerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testNoConfig(): void
    {
        $logger = Mockery::mock(MailLoggerInterface::class);
        $phpMailer = Mockery::mock(PHPMailer::class);
        $config = [];

        $this->expectException(PHPMailerException::class);
        new Mailer($logger, $config, $phpMailer);
    }

    public function testBadConfig(): void
    {
        $logger = Mockery::mock(MailLoggerInterface::class);
        $phpMailer = Mockery::mock(PHPMailer::class);
        $config = ['mailer' => 'foo'];

        $this->expectException(PHPMailerException::class);
        new Mailer($logger, $config, $phpMailer);
    }

    public function testSmtp(): void
    {
        /** @var Mockery\MockInterface&MailLoggerInterface */
        $logger = Mockery::mock(MailLoggerInterface::class)
            ->shouldNotReceive('debug')
            ->getMock();

        /** @var Mockery\MockInterface&PHPMailer */
        $phpMailer = Mockery::mock(PHPMailer::class)
            ->makePartial()
            ->shouldReceive('send')->once()->andReturn(true)
            ->getMock();

        $config = [
            'mailer'          => 'smtp',
            'host'            => 'example.com',
            'port'            => 587,
            'auth'            => true,
            'secure'          => false,
            'username'        => 'user',
            'password'        => 'password',
            'smtp_debug'      => 4,
            'message_options' => [
                'CharSet'   => 'UTF-8',
                'isHtml'    => true,
                'Timeout'   => 15,
            ],
            'smtp_options'    => [
                'ssl' => [
                    'verify_peer'       => true,
                    'verify_depth'      => 3,
                    'allow_self_signed' => true,
                    'peer_name'         => 'smtp.example.com',
                    'cafile'            => '/etc/ssl/ca_cert.pem',
                ],
            ]
        ];

        $mailer = new Mailer($logger, $config, $phpMailer);

        $this->assertInstanceOf(PHPMailer::class, $mailer->getPhpMailer()); // @phpstan-ignore-line

        // Create message
        $recipient = new EmailRecipient('user1@test.com');
        $recipient->bcc('bbc@test.com')->cc('cc@test.com');
        $message = new StaticMailMessage('subject', 'body');
        $message->setFromEmail('test@test.com')
                ->setReplyEmail('test@test.com')
                ->addEmailRecipient($recipient);
        $this->assertNotSame([], $message->getRecipients());
        $mailer->send($message, true);

        // Test clearRecipients
        $this->assertSame([], $message->getRecipients());

        // Test for https://github.com/userfrosting/UserFrosting/issues/1200
        $phpMailerInstance = $mailer->getPhpMailer();
        $this->assertSame([], $phpMailerInstance->getReplyToAddresses());
    }

    public function testMailAndDistinct(): void
    {
        /** @var Mockery\MockInterface&MailLoggerInterface */
        $logger = Mockery::mock(MailLoggerInterface::class)
            ->shouldNotReceive('debug')
            ->getMock();

        /** @var Mockery\MockInterface&PHPMailer */
        $phpMailer = Mockery::mock(PHPMailer::class)
            ->makePartial()
            ->shouldReceive('send')->times(2)->andReturn(true)
            ->getMock();
        $config = ['mailer' => 'mail'];
        $mailer = new Mailer($logger, $config, $phpMailer);

        // Create message
        $recipient1 = new EmailRecipient('user1@test.com');
        $recipient1->bcc('bbc@test.com')->cc('cc@test.com');
        $recipient2 = new EmailRecipient('user2@test.com');
        $message = new StaticMailMessage('subject', 'body');
        $message->setFromEmail('test@test.com')
                ->setReplyEmail('test@test.com')
                ->addEmailRecipient($recipient1)
                ->addEmailRecipient($recipient2);
        $this->assertNotSame([], $message->getRecipients());
        $mailer->sendDistinct($message, true);

        // Test clearRecipients
        $this->assertSame([], $message->getRecipients());
    }

    public function testQMail(): void
    {
        /** @var Mockery\MockInterface&MailLoggerInterface */
        $logger = Mockery::mock(MailLoggerInterface::class)
            ->shouldNotReceive('debug')
            ->getMock();

        /** @var Mockery\MockInterface&PHPMailer */
        $phpMailer = Mockery::mock(PHPMailer::class)
            ->makePartial()
            ->shouldReceive('send')->once()->andReturn(true)
            ->getMock();
        $config = ['mailer' => 'qmail'];
        $mailer = new Mailer($logger, $config, $phpMailer);

        // Create message
        $recipient = new EmailRecipient('user1@test.com');
        $message = new StaticMailMessage('subject', 'body');
        $message->setFromEmail('test@test.com')
                ->addEmailRecipient($recipient)
                ->setReplyEmail('test@test.com');
        $this->assertNotSame([], $message->getRecipients());

        $mailer->send($message, false);

        // Test clearRecipients
        $this->assertNotSame([], $message->getRecipients());
    }

    public function testSendmail(): void
    {
        /** @var Mockery\MockInterface&MailLoggerInterface */
        $logger = Mockery::mock(MailLoggerInterface::class)
            ->shouldNotReceive('debug')
            ->getMock();

        /** @var Mockery\MockInterface&PHPMailer */
        $phpMailer = Mockery::mock(PHPMailer::class)
            ->makePartial()
            ->shouldReceive('send')->once()->andReturn(true)
            ->getMock();
        $config = ['mailer' => 'sendmail'];
        $mailer = new Mailer($logger, $config, $phpMailer);

        // Create message
        $message = new StaticMailMessage('subject', 'body');
        $message->setFromEmail('test@test.com')
                ->setReplyEmail('test@test.com');

        $mailer->send($message, true);
    }

    public function testWithDebug(): void
    {
        /** @var Mockery\MockInterface&MailLoggerInterface */
        $logger = Mockery::mock(MailLoggerInterface::class)
            ->shouldReceive('debug')->once()
            ->getMock();

        /** @var Mockery\MockInterface&PHPMailer */
        $phpMailer = Mockery::mock(PHPMailer::class)
            ->makePartial()
            ->shouldReceive('send')->once()->andReturn(true)
            ->getMock();
        $config = [
            'mailer'          => 'smtp',
            'host'            => 'example.com',
            'port'            => 587,
            'auth'            => true,
            'secure'          => false,
            'username'        => 'user',
            'password'        => 'password',
            'smtp_debug'      => 4,
            'message_options' => [
                'CharSet'   => 'UTF-8',
                'isHtml'    => true,
                'Timeout'   => 15,
            ]
        ];
        $mailer = new Mailer($logger, $config, $phpMailer);

        // Create message
        $message = new StaticMailMessage('subject', 'body');

        $mailer->send($message, true);
    }
}
