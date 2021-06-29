<?php

namespace Exception;

use Lazzard\FtpBridge\Exception\FileLoggerException;
use Lazzard\FtpBridge\Exception\FtpBridgeException;
use PHPUnit\Framework\TestCase;

class FileLoggerExceptionTest extends TestCase
{
    public function testThrow()
    {
        $this->expectException(FileLoggerException::class);
        $this->expectExceptionMessage("some exception message.");
        throw new FileLoggerException("some exception message.");
    }

    public function testExceptionImplementFtpBridgeException()
    {
        $this->assertTrue(new FileLoggerException("some exception message.") instanceof FtpBridgeException);
    }
}
