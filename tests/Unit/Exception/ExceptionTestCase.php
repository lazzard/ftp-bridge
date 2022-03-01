<?php

namespace Lazzard\FtpBridge\Tests\Unit\Exception;

use Lazzard\FtpBridge\Exception\FtpBridgeException;
use PHPUnit\Framework\TestCase;

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
