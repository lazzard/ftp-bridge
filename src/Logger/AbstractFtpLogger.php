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
     * @var int
     *
     * The plain mode logs the FTP server response as they are,
     * no parsing performed on the response string.
     */
    const PLAIN_MODE = 0;

    /**
     * @var int
     *
     * Array mode parses the remote reply to an array and then logs it.
     */
    const ARRAY_MODE = 1;

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