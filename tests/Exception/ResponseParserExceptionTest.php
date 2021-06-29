<?php

namespace Exception;

use Lazzard\FtpBridge\Exception\ResponseParserException;
use PHPUnit\Framework\TestCase;

class ResponseParserExceptionTest extends TestCase
{
    public function testThrow()
    {
        $this->expectException(ResponseParserException::class);
        $this->expectExceptionMessage("some exception message.");
        throw new ResponseParserException("some exception message.");
    }

    public function testExceptionImplementFtpBridgeException()
    {
        $this->assertTrue(new ResponseParserException("some exception message.") instanceof ResponseParserException);
    }
}
