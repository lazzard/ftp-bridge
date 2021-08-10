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

        self::$fakeSession = array(
            array(LogLevel::$info, "220 FTP Server ready.{$crlf}"),
            array(LogLevel::$command, "USER username{$crlf}"),
            array(LogLevel::$info, "331 Password required for username{$crlf}"),
            array(LogLevel::$command, "PASS password{$crlf}"),
            array(LogLevel::$info, "230 User u852470563 logged in{$crlf}"),
            array(LogLevel::$command, "PWD{$crlf}"),
            array(LogLevel::$info, "257 \"root\" is the current directory{$crlf}"),
            array(LogLevel::$command, "NLST .{$crlf}"),
            array(LogLevel::$info, "150 Opening ASCII mode data connection for file list{$crlf}"),
            array(LogLevel::$info, "file1.txt{$crlf}file2.txt{$crlf}file3.txt{$crlf}"),
            array(LogLevel::$info, "226 Transfer complete{$crlf}"),
            array(LogLevel::$command, "UNKNOWN{$crlf}"),
            array(LogLevel::$error, "500 UNKNOWN not understood{$crlf}"),
            array(LogLevel::$command, "QUIT{$crlf}"),
            array(LogLevel::$info, "221 Goodbye.{$crlf}"),
        );
    }

    protected static function logFakeSession(LoggerInterface $logger): void
    {
        foreach (self::$fakeSession as $lvlAndMessage) {
            $logger->log($lvlAndMessage[0], $lvlAndMessage[1]);
        }
    }
}

