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
use Lazzard\FtpBridge\Response\Response;

/**
 * An FTP data stream socket connection.
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
class DataStream extends Stream
{
    /** @var CommandStream */
    public $commandStream;
    
    /** @var bool */
    public $passive;

    /**
     * Opens a data stream socket.
     *
     * @param FtpLoggerInterface $logger
     * @param CommandStream      $commandStream
     * @param bool               $passive
     */
    public function __construct($logger, $commandStream, $passive = true)
    {
        parent::__construct($logger);

        $this->commandStream = $commandStream;
        $this->passive       = $passive;
    }

    /**
     * @inheritDoc
     */
    public function send($command)
    {
        return $this->commandStream->send($command);
    }

    /**
     * @inheritDoc
     */
    public function receive()
    {
        $data = '';
        while (!feof($this->stream)) {
            $data .= fread($this->stream, 8192);
        }

        $this->log($data);

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function open()
    {
        return $this->passive ? $this->openPassiveConnection() : null;
    }

    /**
     * Opens a passive data connection.
     *
     * @return bool
     */
    protected function openPassiveConnection()
    {
        $this->send('PASV');
        $response = new Response($this->commandStream->receive());
        if ($response->getCode() === 227) {
            preg_match_all('/\d+/', $response->getMessage(), $match);

            $ipAddress = join('.', array_slice($match[0], 0, 4));

            $hostPort = array_slice($match[0], 4);
            $hostPort = ($hostPort[0] * 256) + $hostPort[1];

            if (!$this->stream = fsockopen($ipAddress, $hostPort, $errno, $errMsg)) {
                return !trigger_error('Establish data connection was failed.', E_USER_WARNING);
            }

            stream_set_blocking($this->stream, $this->commandStream->blocking);
            stream_set_timeout($this->stream, $this->commandStream->timeout);

            return true;
        }

        return false;
    }

    protected function openActiveConnection()
    {
        // TODO
    }
}