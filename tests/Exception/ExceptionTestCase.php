<?php

namespace Lazzard\FtpBridge\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Lazzard\FtpBridge\Exception\FtpBridgeException;

class ExceptionTestCase extends TestCase
{
    /** @var string */
    protected $exceptionClass;

    public function testThrow()
    {
        $this->expectException($this->exceptionClass);
        $this->expectExceptionMessage("some exception message.");
        throw new $this->exceptionClass("some exception message.");
    }

    public function testExceptionExtendsFtpBridgeExceptionClass()
    {
        $this->assertTrue(new $this->exceptionClass("some exception message.") instanceof FtpBridgeException);
    }
}
