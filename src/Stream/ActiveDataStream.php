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

use Lazzard\FtpBridge\Exception\ActiveDataStreamException;
use Lazzard\FtpBridge\Exception\ResponseException;
use Lazzard\FtpBridge\Exception\StreamWrapperException;
use Lazzard\FtpBridge\Logger\LoggerInterface;
use Lazzard\FtpBridge\Response\Response;
use Lazzard\FtpBridge\Util\StreamWrapper;

/**
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 *
 * @internal
 */
class ActiveDataStream extends DataStream
{
    /**
     * Opens a data stream socket.
     *
     * @param CommandStream        $commandStream
     * @param StreamWrapper        $streamWrapper
     * @param LoggerInterface|null $logger
     */
    public function __construct(CommandStream $commandStream, $streamWrapper, $logger)
    {
        parent::__construct($commandStream, $streamWrapper, $logger);
    }

    /**
     * Opens a stream socket connection that listening to the local random port sent with
     * the PORT command.
     *
     * {@inheritDoc}
     *
     * @throws ActiveDataStreamException|ResponseException|StreamWrapperException
     */
    public function open()
    {
        // minimum port number will be 1024 because 4 * 256 + 0 = 1024
        // maximum port number will be 65535 because 255 * 256 + 255 = 65535
        $p1 = rand(4, 255);
        $p2 = rand(0, 255);

        $port = $this->calculatePortNumber($p1, $p2);

        // 1- create a server socket
        // 2- bind the socket into a local host address
        // 3- listen to the socket on the local port that will be sent along with PORT command
        // 4- send the PORT command.
        if ($stream = $this->streamWrapper->streamSocketServer(
            "tcp://0.0.0.0:$port",
            STREAM_SERVER_BIND | STREAM_SERVER_LISTEN)
        ) {
            $this->stream = $stream;

            $this->streamWrapper->setStream($stream);

            // get the local socket name of the socket resource created by the commandStream instance
            $name = $this->commandStream->streamWrapper->streamSocketGetName();
            $ip   = str_replace('.', ',', preg_replace('/:\d+/', '', $name));

            if (!$this->commandStream->write("PORT $ip,$p1,$p2")) {
                throw new ActiveDataStreamException('Unable to send the PORT command to the server.');
            }

            $response = new Response($this->commandStream->read());

            if ($response->getCode() !== 200) {
                throw new ResponseException($response->getMessage());
            }

            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function read()
    {
        if (($connection = $this->streamWrapper->streamSocketAccept()) === false) {
            return false;
        }

        $this->streamWrapper->setStream($connection);

        $data = '';

        while (($chunk = $this->streamWrapper->fread(64)) !== false) {
            $data .= $chunk;

            if ($this->streamWrapper->feof()) {
               break;
            }
        }

        $this->streamWrapper->fclose();

        // revert to the original server socket stream
        $this->streamWrapper->setStream($this->stream);

        $this->log($data);

        return $data;
    }

    /**
     * @param int $p1
     * @param int $p2
     *
     * @return int
     */
    protected function calculatePortNumber($p1, $p2)
    {
        // calculate the port number based on the rule ($p1 * 256 + $p2)
        return $p1 * 256 + $p2;
    }
}