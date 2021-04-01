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
        return $this->passive ? $this->openPassive() : $this->openActive();
    }

    /**
     * Opens the data connection stream to the server port sent via the FTP server after
     * sending the PASV command.
     *
     * @return bool
     */
    protected function openPassive()
    {
        $this->send('PASV');
        $response = new Response($this->commandStream->receive());
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
        // Gets the right public IP address either if we are running on the local host or on an actual web host.
        $hostIp = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])
            ? file_get_contents('https://api.ipify.org') : $_SERVER['SERVER_ADDR'];

        // Format the IP.
        $hostIp = str_replace(".", ",", $hostIp);

        $low  = rand(32, 255);
        $high = rand(32, 255);
        // $port = ($low * 256) + $high
        $port = ($low<<8) + $high;

        if (is_resource($stream = stream_socket_server('tcp://0.0.0.0:'.$port, $errnon, $errstr, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN))) {
            $this->send(sprintf("PORT %s,%s,%s", str_replace('.', ',', $hostIp), $low, $high));
            $response = new Response($this->commandStream->receive());
            if ($response->getCode() === 200) {
                $this->stream = $stream;
                return true;
            }

            return !ErrorTrigger::raise($response->getMessage());
        }

        return !ErrorTrigger::raise("Unable to open the data stream socket connection.");
    }
}