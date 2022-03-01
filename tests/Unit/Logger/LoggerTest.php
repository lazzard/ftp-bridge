<?php

namespace Lazzard\FtpBridge\Tests\Unit\Logger;

use Lazzard\FtpBridge\FtpBridge;
use Lazzard\FtpBridge\Logger\LogLevel;

abstract class LoggerTest extends LoggerTestCase
{
    /** @var string */
    protected static $logger;

    public function testInfo()
    {
        $logger = $this->getMockBuilder(self::$logger)
            ->disableOriginalConstructor()
            ->onlyMethods(['log'])
            ->getMock();

        $logger->expects($this->once())
            ->method('log')
            ->with(LogLevel::$info, '221 Goodbye.'.FtpBridge::CRLF);

        $this->assertNull($logger->info('221 Goodbye.'.FtpBridge::CRLF));
    }

    public function testError()
    {
        $logger = $this->getMockBuilder(self::$logger)
            ->disableOriginalConstructor()
            ->onlyMethods(['log'])
            ->getMock();

        $logger->expects($this->once())
            ->method('log')
            ->with(LogLevel::$error, '500 not understood.'.FtpBridge::CRLF);

        $this->assertNull($logger->error('500 not understood.'.FtpBridge::CRLF));
    }

    public function testCommand()
    {
        $logger = $this->getMockBuilder(self::$logger)
            ->disableOriginalConstructor()
            ->onlyMethods(['log'])
            ->getMock();

        $logger->expects($this->once())
            ->method('log')
            ->with(LogLevel::$command, 'USER username.'.FtpBridge::CRLF);

        $this->assertNull($logger->command('USER username.'.FtpBridge::CRLF));
    }
}

