<?php

namespace Lazzard\FtpBridge\Tests\Unit\Exception;

use Lazzard\FtpBridge\Exception\ResponseException;

class ResponseExceptionTest extends ExceptionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->exceptionClass = ResponseException::class;
    }
}
