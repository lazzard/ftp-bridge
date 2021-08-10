<?php

namespace Lazzard\FtpBridge\Tests\Exception;

use Lazzard\FtpBridge\Exception\ActiveDataStreamException;

class ActiveDataStreamExceptionTest extends ExceptionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->exceptionClass = ActiveDataStreamException::class;
    }
}
