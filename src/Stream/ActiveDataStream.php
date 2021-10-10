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
        $ip = str_replace('.', ',', $this->activeIpAddress ?: $_SERVER['SERVER_ADDR']);

        $low  = rand(32, 255);
        $high = rand(32, 255);
        // $port = ($low * 256) + $high
        $port = ($low<<8) + $high;

        // 1- create a stream socket.
        // 2- bind the socket to a local host address.
        // 3- listen to the socket on the local port that will
        // be send along with PORT command.
        // 4- send the PORT command.
        if (is_resource($stream = stream_socket_server('tcp://0.0.0.0:'.$port, $errnon, $errstr, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN))) {

            if(!$this->commandStream->write("PORT $ip,$low,$high")) {
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