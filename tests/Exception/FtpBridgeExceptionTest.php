<?php

namespace Lazzard\FtpBridge\Tests\Exception;

use Lazzard\FtpBridge\Exception\FtpBridgeException;

class FtpBridgeExceptionTest extends ExceptionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->exceptionClass = FtpBridgeException::class;
    }
}
