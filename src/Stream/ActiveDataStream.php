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

use Lazzard\FtpBridge\Util\StreamWrapper;
use Lazzard\FtpBridge\Logger\LoggerInterface;
use Lazzard\FtpBridge\Exception\ActiveDataStreamException;
use Lazzard\FtpBridge\Response\Response;

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
     * @throws ActiveDataStreamException
     */
    public function open()
    {
        // minimum port number will be 1024 because 4 * 256 + 0 = 1024
        // maximum port number will be 65535 because 255 * 256 + 255 = 65535
        $p1  = rand(4, 255);
        $p2 = rand(0, 255);

        // calculate the port number based on the rule ($p1 * 256 + $p2)
        $port = $p1 * 256 + $p2;

        // 1- create a server socket
        // 2- bind the socket into a local host address
        // 3- listen to the socket on the local port that will be send along with PORT command
        // 4- send the PORT command.
        if (is_resource($stream = $this->streamWrapper->streamSocketServer(
            "tcp://0.0.0.0:$port",
            STREAM_SERVER_BIND | STREAM_SERVER_LISTEN)
        )) {
            $name = stream_socket_get_name($this->commandStream->stream, false);
            $ip   = str_replace('.', ',', preg_replace('/:\d+/', '', $name));

            if(!$this->commandStream->write("PORT $ip,$p1,$p2")) {
                throw new ActiveDataStreamException('Unable to send the PORT command to the server.');
            }

            $response = new Response($this->commandStream->read());

            if ($response->getCode() !== 200) {
                throw new ActiveDataStreamException($response->getMessage());
            }

            $this->stream = $stream;

            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function read()
    {
        if (($conn = stream_socket_accept($this->stream)) === false) {
            return false;
        }

        $data = fread($conn, 8192);

        fclose($conn);

        $this->log($data);

        return $data;
    }
}