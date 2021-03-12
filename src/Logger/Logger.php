<?php

/**
 * This file is part of the Lazzard/ftp-bridge package.
 *
 * (c) El Amrani Chakir <elamrani.sv.laza@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lazzard\FtpBridge\Logger;

/**
 * Basic FTP logger implementation that other FTP Loggers may extends its.
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
abstract class Logger implements LoggerInterface
{
    /**
     * @inheritDoc
     */
    public function info($message)
    {
        $this->log(LogLevel::INFO, $message);
    }

    /**
     * @inheritDoc
     */
    public function error($message)
    {
        $this->log(LogLevel::ERROR, $message);
    }
}