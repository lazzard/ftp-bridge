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
use Lazzard\FtpBridge\Response\FtpResponse;

/**
 * Class FtpDataStream
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
class FtpDataStream implements FtpStreamInterface
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
     * @param bool               $usePassiveAddress
     */
    public function __construct($logger, $commandStream, $passive = true, $usePassiveAddress = true)
    {
        $this->logger            = $logger;
        $this->commandStream     = $commandStream;
        $this->passive           = $passive;
        $this->usePassiveAddress = $usePassiveAddress;

        if ($passive) {
            $this->send('PASV');

            $response = new FtpResponse($this->commandStream->receive());

            if ($response->getCode() === 227) {
                preg_match_all('/\d+/', $response->getMessage(), $match);

                $hostIp = join('.', array_slice($match[0], 0, 4));

                $hostPort = array_slice($match[0], 4);
                $hostPort = ($hostPort[0] * 256) + $hostPort[1];

                if ( ! ($this->stream = fsockopen($hostIp, $hostPort, $errno, $errMsg))) {
                    throw new \RuntimeException("Opening data connection stream was failed. [{$errMsg}]");
                }

                stream_set_blocking($this->stream, $this->commandStream->blocking);
                stream_set_timeout($this->stream, $this->commandStream->timeout);

            } else {
                throw new \RuntimeException("PASV command fails : " . $response->getReply());
            }
        }
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
            $data .= fgets($this->stream);
        }

        $this->logger->info($data);

        return $data;
    }
}