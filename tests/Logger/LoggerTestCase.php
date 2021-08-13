<?php

namespace Lazzard\FtpBridge\Tests\Logger;

use PHPUnit\Framework\TestCase;
use Lazzard\FtpBridge\FtpBridge;
use Lazzard\FtpBridge\Logger\LogLevel;
use Lazzard\FtpBridge\Logger\LoggerInterface;

abstract class LoggerTestCase extends TestCase
{
    /** @var Array */
    protected static $fakeSession;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $crlf = FtpBridge::CRLF;

        self::$fakeSession = [
            [LogLevel::$info, "220 FTP Server ready.{$crlf}"],
            [LogLevel::$command, "USER username{$crlf}"],
            [LogLevel::$info, "331 Password required for username{$crlf}"],
            [LogLevel::$command, "PASS password{$crlf}"],
            [LogLevel::$info, "230 User u852470563 logged in{$crlf}"],
            [LogLevel::$command, "PWD{$crlf}"],
            [LogLevel::$info, "257 \"root\" is the current directory{$crlf}"],
            [LogLevel::$command, "NLST .{$crlf}"],
            [LogLevel::$info, "150 Opening ASCII mode data connection for file list{$crlf}"],
            [LogLevel::$info, "file1.txt{$crlf}file2.txt{$crlf}file3.txt{$crlf}"],
            [LogLevel::$info, "226 Transfer complete{$crlf}"],
            [LogLevel::$command, "UNKNOWN{$crlf}"],
            [LogLevel::$error, "500 UNKNOWN not understood{$crlf}"],
            [LogLevel::$command, "QUIT{$crlf}"],
            [LogLevel::$info, "221 Goodbye.{$crlf}"],
        ];
    }

    protected static function logFakeSession(LoggerInterface $logger): void
    {
        foreach (self::$fakeSession as $lvlAndMessage) {
            $logger->log($lvlAndMessage[0], $lvlAndMessage[1]);
        }
    }
}

