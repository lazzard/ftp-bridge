<?php

namespace Error;

use Lazzard\FtpBridge\Error\ErrorTrigger;
use PHPUnit\Framework\TestCase;

class ErrorTriggerTest extends TestCase
{
    public function testRaiseSuccess()
    {
        set_error_handler(function () {});
        $this->assertTrue(ErrorTrigger::raise("something goes wrong!"));
        restore_error_handler();
    }

    public function testRaiseFailure()
    {
        $wrongErrorType = -1;
        set_error_handler(function () {});
        $this->assertFalse(ErrorTrigger::raise("something goes wrong!", $wrongErrorType));
        restore_error_handler();
    }
}
