<?php

namespace Logger;

use Lazzard\FtpBridge\Logger\ArrayLogger;
use Lazzard\FtpBridge\Logger\LoggerInterface;
use PHPUnit\Framework\TestCase;

class ArrayLoggerTest extends TestCase
{
    public function testConstructor()
    {
        $logger = new ArrayLogger;

        $this->assertInstanceOf(ArrayLogger::class, $logger);
    }

    public function testGetLogsWithPlainMode()
    {
        $logger = $this
            ->getMockBuilder(ArrayLogger::class)
            ->setConstructorArgs([LoggerInterface::PLAIN_MODE])
            ->onlyMethods(['getLogs'])
            ->getMock();

        $expected = [
            0 => "<-- 220---------- Welcome to Pure-FTPd [privsep] [TLS] ----------
    220-You are user number 231 of 6900 allowed.
    220-Local time is now 18:54. Server port: 21.
    220-This is a private system - No anonymous login
    220 You will be disconnected after 60 seconds of inactivity.",
        ];

        $logger
            ->method('getLogs')
            ->with()
            ->willReturn($expected);

        $this->assertSame($expected, $logger->getLogs());
    }

    public function testGetLogsWithArrayMode()
    {
        $logger = $this
            ->getMockBuilder(ArrayLogger::class)
            ->setConstructorArgs([LoggerInterface::ARRAY_MODE])
            ->onlyMethods(['getLogs'])
            ->getMock();

        $expected = [
            0 => "<-- 220---------- Welcome to Pure-FTPd [privsep] [TLS] ----------",
            1 => "220-You are user number 216 of 6900 allowed.",
            2 => "220-Local time is now 19:38. Server port: 21.",
            3 => "220-This is a private system - No anonymous login",
            4 => "220 You will be disconnected after 60 seconds of inactivity.",
        ];

        $logger
            ->method('getLogs')
            ->with()
            ->willReturn($expected);

        $this->assertSame($expected, $logger->getLogs());
    }

    public function testLogWithPlainMode()
    {
        $logger = new ArrayLogger(LoggerInterface::PLAIN_MODE);

        $response = "220---------- Welcome to Pure-FTPd [privsep] [TLS] ----------
                220-You are user number 231 of 6900 allowed.
                220-Local time is now 18:54. Server port: 21.
                220-This is a private system - No anonymous login
                220 You will be disconnected after 60 seconds of inactivity";

        $this->assertNull($logger->log('<--', $response . LoggerInterface::CRLF));
        $this->assertSame([0 => '<-- ' . $response . LoggerInterface::CRLF], $logger->getLogs());
    }

    public function testLogWithArrayMode()
    {
        $logger = new ArrayLogger(LoggerInterface::ARRAY_MODE);

        $response = "220---------- Welcome to Pure-FTPd [privsep] [TLS] ----------
220-You are user number 267 of 6900 allowed.
220-Local time is now 22:48. Server port: 21.
220-This is a private system - No anonymous login
220 You will be disconnected after 60 seconds of inactivity.
";

        $this->assertNull($logger->log('<--', $response));

        $this->assertSame(
            [
                0 => "<-- 220---------- Welcome to Pure-FTPd [privsep] [TLS] ----------",
                1 => "220-You are user number 267 of 6900 allowed.",
                2 => "220-Local time is now 22:48. Server port: 21.",
                3 => "220-This is a private system - No anonymous login",
                4 => "220 You will be disconnected after 60 seconds of inactivity."
            ],
            $logger->getLogs());
    }

    public function testClear()
    {
        $logger = new ArrayLogger;

        $this->assertNull($logger->clear());
        $this->assertEmpty($logger->getLogs());
    }

    public function testCountWithPlainMode()
    {
        $logger = new ArrayLogger(LoggerInterface::PLAIN_MODE);

        $logger->log('<--',
        "220---------- Welcome to Pure-FTPd [privsep] [TLS] ----------
    220-You are user number 231 of 6900 allowed.
    220-Local time is now 18:54. Server port: 21.
                220-This is a private system - No anonymous login
                220 You will be disconnected after 60 seconds of inactivity"
        );

        $this->assertSame(1, $logger->count());
    }

    public function testCountWithArrayMode()
    {
        $logger = new ArrayLogger(LoggerInterface::ARRAY_MODE);

        $logger->log('<--',
            "220---------- Welcome to Pure-FTPd [privsep] [TLS] ----------
    220-You are user number 231 of 6900 allowed.
    220-Local time is now 18:54. Server port: 21.
                220-This is a private system - No anonymous login
                220 You will be disconnected after 60 seconds of inactivity"
        );

        $this->assertSame(5, $logger->count());
    }
}
