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

use Lazzard\FtpBridge\Logger\LoggerInterface;
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
     * @param LoggerInterface $logger
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

        if ($response->getCode() !== 227) {
            return false;
        }

        if (!preg_match('/(\d+,){4}+/', $response->getMessage(), $ipMatches)
            || !preg_match('/\d+,\d+\)$/', $response->getMessage(), $portMatches)) {
            return false;
        }

        $ip    = rtrim(str_replace(",", ".", $ipMatches[0]), ".");
        $ports = explode(",", rtrim($portMatches[0], ")"));
        $port  = ($ports[0] * 256) + $ports[1];

        return $this->openSocket($ip, $port, $this->commandStream->timeout, $this->commandStream->blocking);
    }

    protected function openActiveConnection()
    {
        // TODO
    }
}