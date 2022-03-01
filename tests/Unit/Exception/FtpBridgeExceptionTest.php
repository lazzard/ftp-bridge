<?php

namespace Lazzard\FtpBridge\Tests\Unit\Exception;

use Lazzard\FtpBridge\Exception\FtpBridgeException;

class FtpBridgeExceptionTest extends ExceptionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->exceptionClass = FtpBridgeException::class;
    }
}
