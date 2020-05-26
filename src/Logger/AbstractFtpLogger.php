<?php

namespace Lazzard\FtpBridge\Logger;

/**
 * A base FTP logger that other FTP Loggers must extends its.
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