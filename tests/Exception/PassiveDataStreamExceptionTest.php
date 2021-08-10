<?php

namespace Lazzard\FtpBridge\Tests\Exception;

use Lazzard\FtpBridge\Exception\PassiveDataStreamException;

class PassiveDataStreamExceptionTest extends ExceptionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->exceptionClass = PassiveDataStreamException::class;
    }
}
