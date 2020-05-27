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
 * Represents a command stream commandStream.
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
class FtpCommandStream extends FtpStreamAbstract
{
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
     * @throws StreamException
     */
    public function __construct($logger, $host, $port, $timeout, $blocking)
    {
        $this->logger   = $logger;
        $this->host     = $host;
        $this->port     = $port;
        $this->timeout  = $timeout;
        $this->blocking = $blocking;

        $this->open();
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

    protected function open()
    {
        // TODO wrong giving host resolving
        if ( ! ($this->stream = fsockopen($this->host, $this->port, $errno, $errMsg))) {
            throw new StreamException("Opening command stream socket was failed : [{$errMsg}]");
        }

        stream_set_blocking($this->stream, $this->blocking);
        stream_set_timeout($this->stream, $this->blocking);

        // TODO check the reply
        $this->receive();
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
            // TODO 400 ?
            if ((new FtpResponse($response))->getCode() < 400) {
                $this->logger->info($response);
            } else {
                $this->logger->info($response);
            }
        }
    }
}