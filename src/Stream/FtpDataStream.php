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

use Lazzard\FtpBridge\Exception\StreamException;
use Lazzard\FtpBridge\Logger\FtpLoggerInterface;
use Lazzard\FtpBridge\Response\FtpResponse;

/**
 * Class FtpDataStream
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
class FtpDataStream extends FtpStreamAbstract
{
    /** @var FtpLoggerInterface */
    public $logger;

    /** @var FtpCommandStream */
    public $commandStream;

    /** @var resource */
    public $stream;

    /** @var bool */
    public $passive;

    /** @var bool */
    public $usePassiveAddress;

    /**
     * Opens a data stream socket.
     *
     * @param FtpLoggerInterface $logger
     * @param FtpCommandStream   $commandStream
     * @param bool               $passive
     * 
     * @throws StreamException
     */
    public function __construct($logger, $commandStream, $passive = true)
    {
        $this->logger            = $logger;
        $this->commandStream     = $commandStream;
        $this->passive           = $passive;

        $this->open();
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

        while ( ! feof($this->stream)) {
            $data .= fread($this->stream, 8192);
        }

        $this->logger->info($data);

        return $data;
    }

    protected function open()
    {
        if ($this->passive) {
            $this->send('PASV');

            $response = new FtpResponse($this->commandStream->receive());

            if ($response->getCode() === 227) {
                preg_match_all('/\d+/', $response->getMessage(), $match);

                $hostIp = join('.', array_slice($match[0], 0, 4));

                $hostPort = array_slice($match[0], 4);
                $hostPort = ($hostPort[0] * 256) + $hostPort[1];

                if ( ! ($this->stream = fsockopen($hostIp, $hostPort, $errno, $errMsg))) {
                    throw new StreamException("Opening data connection stream was failed. [{$errMsg}]");
                }

                stream_set_blocking($this->stream, $this->commandStream->blocking);
                stream_set_timeout($this->stream, $this->commandStream->timeout);
            }
        }
    }
}