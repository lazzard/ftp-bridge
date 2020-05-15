<?php

namespace Lazzard\FtpBridge\Logger;

use Lazzard\FtpBridge\FtpLoggerInterface;

/**
 * Class AbstractFtpLogger
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
abstract class AbstractFtpLogger implements FtpLoggerInterface
{
    /**
     * @inheritDoc
     */
    public function info($message)
    {
        $this->log(FtpLogLevel::INFO, $message);
    }

    /**
     * @inheritDoc
     */
    public function error($message)
    {
        $this->log(FtpLogLevel::ERROR, $message);
    }
}