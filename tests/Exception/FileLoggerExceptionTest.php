<?php

namespace Lazzard\FtpBridge\Tests\Exception;

use Lazzard\FtpBridge\Exception\FileLoggerException;

class FileLoggerExceptionTest extends ExceptionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->exceptionClass = FileLoggerException::class;
    }
}
