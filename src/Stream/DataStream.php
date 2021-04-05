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

use Lazzard\FtpBridge\Error\ErrorTrigger;
use Lazzard\FtpBridge\Logger\LoggerInterface;
use Lazzard\FtpBridge\Response\Response;

/**
 * An FTP data stream socket.
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 * 
 * @internal
 */
class DataStream extends Stream
{
    /** @var StreamInterface */
    public $commandStream;
    
    /** @var bool */
    public $passive;

    /** @var string */
    public $activeIpAddress;

    /**
     * Opens a data stream socket.
     *
     * @param LoggerInterface $logger
     * @param StreamInterface $commandStream
     * @param bool            $passive
     */
    public function __construct($logger, $commandStream, $passive = true, $activeIpAddress = null)
    {
        parent::__construct($logger);
        $this->commandStream   = $commandStream;
        $this->passive         = $passive;
        $this->activeIpAddress = $activeIpAddress;
    }

    /**
     * @inheritDoc
     */
    public function read()
    {
        $conn = stream_socket_accept($this->stream);
        $data = fread($conn, 8192);
        fclose($conn);

        $this->log($data);

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function open()
    {
        return call_user_func_array(array($this, $this->passive ? 'openPassive' : 'openActive'), array());
    }

    /**
     * Opens the data connection stream to the server port sent via the FTP server after
     * sending the PASV command.
     *
     * @return bool
     */
    protected function openPassive()
    {
        $this->commandStream->write('PASV');
        $response = new Response($this->commandStream->read());
        if ($response->getCode() !== 227) {
            return !ErrorTrigger::raise($response->getMessage());
        }

        if (!preg_match('/(\d+,){4}+/', $response->getMessage(), $ipMatches)
            || !preg_match('/\d+,\d+\)$/', $response->getMessage(), $portMatches)) {
                return !ErrorTrigger::raise("Unable to get the passive IP & PORT from the reply message.");
        }

        $ip    = rtrim(str_replace(",", ".", $ipMatches[0]), ".");
        $ports = explode(",", rtrim($portMatches[0], ")"));
        $port  = ($ports[0] * 256) + $ports[1];

        return $this->openStreamSocket($ip, $port, $this->commandStream->timeout, $this->commandStream->blocking);
    }

    /**
     * Opens a stream socket connection that listening to the local random port sent with 
     * the PORT command.
     * 
     * @return bool
     */
    protected function openActive()
    {
        $hostIp = str_replace(".", ",", $this->activeIpAddress ?: $_SERVER['SERVER_ADDR']);

        $low  = rand(32, 255);
        $high = rand(32, 255);
        // $port = ($low * 256) + $high
        $port = ($low<<8) + $high;

        // 1- create a stream socket.
        // 2- bind the socket to a local host address.
        // 3- listen to the socket on the local port that will
        // be send along with PORT comamnd.
        // 4- send the PORT command.
        if (is_resource($stream = stream_socket_server('tcp://0.0.0.0:'.$port, $errnon, $errstr, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN))) {
            $this->commandStream->write("PORT $hostIp,$low,$high");
            $response = new Response($this->commandStream->read());
            if ($response->getCode() === 200) {
                $this->stream = $stream;
                return true;
            }

            return !ErrorTrigger::raise($response->getMessage());
        }

        return !ErrorTrigger::raise("Unable to open the data stream socket connection.");
    }
}