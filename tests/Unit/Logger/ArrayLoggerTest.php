<?php

namespace Lazzard\FtpBridge\Tests\Unit\Logger;

use Lazzard\FtpBridge\FtpBridge;
use Lazzard\FtpBridge\Logger\ArrayLogger;
use Lazzard\FtpBridge\Logger\LoggerInterface;
use Lazzard\FtpBridge\Logger\LogLevel;

class ArrayLoggerTest extends LoggerTest
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$logger = ArrayLogger::class;
    }

    public function testConstructor()
    {
        $logger = new ArrayLogger;

        $this->assertInstanceOf(LoggerInterface::class, $logger);
    }

    public function testLog()
    {
        $logger = new ArrayLogger;

        $this->assertNull($logger->log(LogLevel::$command, 'USER username'));
    }

    public function testGetLogs()
    {
        $logger = new ArrayLogger;

        self::logFakeSession($logger);

        $logs = $logger->getLogs();

        $crlf = FtpBridge::CRLF;

        $this->assertSame([
            LogLevel::$info . " 220 FTP Server ready.{$crlf}",
            LogLevel::$command . " USER username{$crlf}",
            LogLevel::$info . " 331 Password required for username{$crlf}",
            LogLevel::$command . " PASS password{$crlf}",
            LogLevel::$info . " 230 User u852470563 logged in{$crlf}",
            LogLevel::$command . " PWD{$crlf}",
            LogLevel::$info . " 257 \"root\" is the current directory{$crlf}",
            LogLevel::$command . " NLST .{$crlf}",
            LogLevel::$info . " 150 Opening ASCII mode data connection for file list{$crlf}",
            LogLevel::$info . " file1.txt{$crlf}file2.txt{$crlf}file3.txt{$crlf}",
            LogLevel::$info . " 226 Transfer complete{$crlf}",
            LogLevel::$command . " UNKNOWN{$crlf}",
            LogLevel::$error . " 500 UNKNOWN not understood{$crlf}",
            LogLevel::$command . " QUIT{$crlf}",
            LogLevel::$info . " 221 Goodbye.{$crlf}",
        ], $logs);
    }

    public function testClear()
    {
        $logger = new ArrayLogger;

        self::logFakeSession($logger);

        $this->assertNull($logger->clear());
        $this->assertEmpty($logger->getLogs());
    }

    public function testCount()
    {
        $logger = new ArrayLogger;

        $this->assertSame(0, $logger->count());

        self::logFakeSession($logger);

        $this->assertSame(15, $logger->count());
    }
}

