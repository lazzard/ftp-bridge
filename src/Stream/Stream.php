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

use Lazzard\FtpBridge\Response\Response;
use Lazzard\FtpBridge\Logger\LoggerInterface;
use Lazzard\FtpBridge\Exception\StreamException;
use Lazzard\FtpBridge\Exception\ResponseParserException;
use Lazzard\FtpBridge\FtpBridge;

/**
 * Abstracts shared implementation of FTP stream classes.
 * Holds the common logic between FTP stream classes.
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 *
 * @internal
 */
abstract class Stream implements StreamInterface
{
    /** @var resource */
    public $stream;

    /** @var LoggerInterface */
    public $logger;

    /**
     * Stream constructor.
     *
     * @param LoggerInterface|null $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    final public function write($command)
    {
        $command = $this->prepareCommand($command);

        $write = fwrite($this->stream, $command);

        if ($write !== false && $this->logger instanceof LoggerInterface) {
            $this->logger->command($command);
        }

        return $write;
    }

    /**
     * @inheritDoc
     */
    final public function close()
    {
        return fclose($this->stream);
    }

    /**
     * Internal logging method.
     *
     * @param string $message
     *
     * @return void
     *
     * @throws ResponseParserException
     */
    final protected function log($message)
    {
        if (!$this->logger) {
            return;
        }

        $response = new Response($message);
        call_user_func_array(
            array($this->logger, $response->getCode() < 400 ? 'info' : 'error'),
            array($message)
        );
    }

    /**
     * Opens an FTP stream socket connection.
     *
     * @param string $host
     * @param int    $port
     * @param int    $timeout
     * @param bool   $blocking
     *
     * @return bool
     *
     * @throws StreamException
     */
    final protected function openStreamSocket($host, $port, $timeout, $blocking)
    {
        if (!($this->stream = @stream_socket_client("tcp://$host:$port", $errno, $errMsg, $timeout,
            $blocking ? STREAM_CLIENT_CONNECT : STREAM_CLIENT_ASYNC_CONNECT))) {
            throw new StreamException($errMsg);
        }

        return true;
    }

    /**
     * Sanitize and prepare a client command to be send to the serever.
     * 
     * @param string $command
     * 
     * @return string
     */
    final protected function prepareCommand($command)
    {
        return trim($command) . FtpBridge::CRLF;
    }

    /**
     * @inheritDoc
     */
    abstract public function open();

    /**
     * @inheritDoc
     */
    abstract public function read();
}