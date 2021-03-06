<?php

/**
 * This file is part of the Lazzard/ftp-bridge package.
 *
 * (c) El Amrani Chakir <elamrani.sv.laza@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lazzard\FtpBridge\Stream;

use Lazzard\FtpBridge\Logger\FtpLoggerInterface;
use Lazzard\FtpBridge\Logger\FtpLogLevel;

/**
 * Represents an FTP command stream (control channel).
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
class CommandStream extends Stream
{
    /** @var string */
    public $host;

    /** @var int */
    public $port;

    /** @var int */
    public $timeout;

    /** @var bool */
    public $blocking;

    /**
     * CommandStream constructor.
     *
     * @param FtpLoggerInterface $logger
     * @param string             $host
     * @param int                $port
     * @param int                $timeout
     * @param bool               $blocking
     */
    public function __construct($logger, $host, $port, $timeout, $blocking)
    {
        parent::__construct($logger);
        $this->host     = $host;
        $this->port     = $port;
        $this->timeout  = $timeout;
        $this->blocking = $blocking;
    }

    /**
     * @inheritDoc
     */
    public function send($command)
    {
        $write = fwrite($this->stream, trim($command) . self::CRLF);
        $this->logger->log(FtpLogLevel::COMMAND, $command . self::CRLF);
        return ($write !== 0 && $write === false) ? false : true;
    }

    /**
     * @inheritDoc
     */
    public function receive()
    {
        $response = '';
        while (true) {
            $line     = fgets($this->stream);
            $response .= $line;
            // To distinguish the end of an FTP reply, the RFC959 indicates that the last line of
            // a the reply must be on a special format, it must be begin with 3 digits followed
            // by a space.
            //@link https://tools.ietf.org/html/rfc959#section-4
            if (preg_match('/^\d{3}+ /', $line) !== 0) {
                break;
            }
        }

        $this->log($response);

        return $response;
    }

    /**
     * @inheritDoc
     */
    public function open()
    {
        // TODO wrong giving host resolving
        if (!($this->stream = fsockopen($this->host, $this->port, $errno, $errMsg))) {
            return !trigger_error(sprintf("Opening command stream socket was failed : %s", $errMsg), E_USER_WARNING);
        }

        stream_set_blocking($this->stream, $this->blocking);
        stream_set_timeout($this->stream, $this->timeout);

        // TODO check the reply
        $this->receive();

        return true;
    }
}