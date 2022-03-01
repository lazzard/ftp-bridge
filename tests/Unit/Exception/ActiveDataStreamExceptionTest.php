<?php

namespace Lazzard\FtpBridge\Tests\Unit\Exception;

use Lazzard\FtpBridge\Exception\ActiveDataStreamException;

class ActiveDataStreamExceptionTest extends ExceptionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->exceptionClass = ActiveDataStreamException::class;
    }
}
