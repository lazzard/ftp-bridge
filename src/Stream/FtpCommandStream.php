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
 * Represents a command stream commandStream.
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
class FtpCommandStream implements FtpStreamInterface
{
    /**
     * Carriage return and line feed used in the end of FTP commands as defined in RFC959.
     */
    const CRLF = "\r\n";

    /** @var FtpLoggerInterface */
    public $logger;

    /** @var resource */
    public $stream;

    /** @var string */
    public $host;

    /** @var int */
    public $port;

    /** @var int */
    public $timeout;

    /** @var bool */
    public $blocking;

    /**
     * FtpCommandStream constructor.
     *
     * @param FtpLoggerInterface $logger
     * @param string             $host
     * @param int                $port
     * @param int                $timeout
     * @param bool               $blocking
     * 
     * @throws \RuntimeException
     */
    public function __construct($logger, $host, $port, $timeout, $blocking)
    {
        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout;
        $this->blocking = $blocking;

        // TODO wrong giving host resolving
        if ( ! ($this->stream = fsockopen($host, $port, $errno, $errMsg))) {
            throw new \RuntimeException("Opening command stream socket was failed : [{$errMsg}]");
        }

        stream_set_blocking($this->stream, $blocking);
        stream_set_timeout($this->stream, $timeout);

        $this->logger = $logger;

        $this->receive();
    }

    /**
     * @inheritDoc
     */
    public function send($command)
    {
        return fwrite($this->stream, trim($command) . self::CRLF);
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

            /**
             * To distinguish the end of an FTP reply, the RFC959 indicates that the last line of
             * a the reply must be on a special format, it must be begin with 3 digits followed
             * by a space.
             *
             * @link https://tools.ietf.org/html/rfc959#section-4
             */
            if (preg_match('/\d{3}+ /', $line) !== 0) {
                break;
            }
        }

        $this->log($response);

        return $response;
    }

    /**
     * Internal logging method.
     *
     * @param string $response
     *
     * @return void
     */
    protected function log($response)
    {
        if ($this->logger) {
            $response = new FtpResponse($response);

            // TODO 400 ?
            if ($response->getCode() < 400) {
                $this->logger->info($response->getReply());
            } else {
                $this->logger->info($response->getReply());
            }
        }
    }
}