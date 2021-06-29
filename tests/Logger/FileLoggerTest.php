<?php

namespace Logger;

use Lazzard\FtpBridge\Exception\FileLoggerException;
use Lazzard\FtpBridge\Logger\FileLogger;
use Lazzard\FtpBridge\Logger\LoggerInterface;
use PHPUnit\Framework\TestCase;

class FileLoggerTest extends TestCase
{
    public function testConstructor()
    {
        $file = tempnam(sys_get_temp_dir(), 'lazzard_ftp_bridge_temp_file');

        $logger = new FileLogger($file);

        $this->assertInstanceOf(FileLogger::class, $logger);
        $this->assertIsResource($logger->getStream());

        unlink($file);
    }


    public function testGetLogsWithPlainMode()
    {
        $file = tempnam(sys_get_temp_dir(), 'lazzard_ftp_bridge_temp_file');

        $logger = new FileLogger($file, LoggerInterface::PLAIN_MODE);

        $logger->log('-->', 'HELP');

        $this->assertSame('--> HELP', $logger->getLogs());

        unlink($file);
    }

    public function testGetLogsWithArrayMode()
    {
        $file = tempnam(sys_get_temp_dir(), 'lazzard_ftp_bridge_temp_file');

        $logger = new FileLogger($file, LoggerInterface::ARRAY_MODE);

        $logger->log('-->', 'HELP' . LoggerInterface::CRLF);

        $this->assertSame("[1] array() --> [
    HELP
]",
            $logger->getLogs()
        );

        unlink($file);
    }

    public function testGetLogsIfFileNotExists()
    {
        $file = tempnam(sys_get_temp_dir(), 'lazzard_ftp_bridge_temp_file');

        $logger = new FileLogger($file);

        unlink($file);

        $logger->log('-->', 'HELP' . LoggerInterface::CRLF);

        $this->expectException(FileLoggerException::class);

        $logger->getLogs();
    }

    public function testCountWithPlainMode()
    {
        $file = tempnam(sys_get_temp_dir(), 'lazzard_ftp_bridge_temp_file');

        $logger = new FileLogger($file, LoggerInterface::PLAIN_MODE);

        $logger->log('<--',
            "220---------- Welcome to Pure-FTPd [privsep] [TLS] ----------
                220-You are user number 231 of 6900 allowed.
                220-Local time is now 18:54. Server port: 21.
                220-This is a private system - No anonymous login
                220 You will be disconnected after 60 seconds of inactivity" . LoggerInterface::CRLF);

        $this->assertSame(5, $logger->count());

        unlink($file);
    }

    public function testCountWithArrayMode()
    {
        $file = tempnam(sys_get_temp_dir(), 'lazzard_ftp_bridge_temp_file');

        $logger = new FileLogger($file, LoggerInterface::ARRAY_MODE);

        $logger->log('<--',
            "220---------- Welcome to Pure-FTPd [privsep] [TLS] ----------
                220-You are user number 231 of 6900 allowed.
                220-Local time is now 18:54. Server port: 21.
                220-This is a private system - No anonymous login
                220 You will be disconnected after 60 seconds of inactivity" . LoggerInterface::CRLF);

        $this->assertSame(1, $logger->count());

        unlink($file);
    }

    public function testLogWithPlainMode()
    {
        $file = tempnam(sys_get_temp_dir(), 'lazzard_ftp_bridge_temp_file');

        $logger = new FileLogger($file, LoggerInterface::PLAIN_MODE);

        $this->assertNull($logger->log('-->', 'NOOP' . LoggerInterface::CRLF));
        $this->assertSame('--> NOOP' . LoggerInterface::CRLF, $logger->getLogs());

        unlink($file);
    }

    public function testLogWithArrayMode()
    {
        $file = tempnam(sys_get_temp_dir(), 'lazzard_ftp_bridge_temp_file');

        $logger = new FileLogger($file, LoggerInterface::ARRAY_MODE);

        $this->assertNull($logger->log('-->', 'NOOP' . LoggerInterface::CRLF));
        $this->assertSame('[1] array() --> [
    NOOP
]', $logger->getLogs());

        unlink($file);
    }

    public function testClear()
    {
        $file = tempnam(sys_get_temp_dir(), 'lazzard_ftp_bridge_temp_file');

        $logger = new FileLogger($file);

        $this->assertTrue($logger->clear());

        unlink($file);
    }
}
