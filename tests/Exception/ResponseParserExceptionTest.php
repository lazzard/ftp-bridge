<?php

namespace Lazzard\FtpBridge\Tests\Exception;

use Lazzard\FtpBridge\Exception\ResponseParserException;

class ResponseParserExceptionTest extends ExceptionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->exceptionClass = ResponseParserException::class;
    }
}
