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

use Lazzard\FtpBridge\Logger\LoggerInterface;
use Lazzard\FtpBridge\Response\Response;
use Lazzard\FtpBridge\Error\ErrorTrigger;

/**
 * Abstracts shared implementation of an FTP stream.
 * Holds the common logic between FTP streams.
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
        if (($write = fwrite($this->stream, trim($command).self::CRLF)) !== false && $this->logger) {
            $this->logger->command($command.self::CRLF);
        }

        return true;
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
     */
    final protected function openStreamSocket($host, $port, $timeout, $blocking)
    {
        if (!($this->stream = stream_socket_client("tcp://$host:$port", $errno, $errMsg, $timeout, 
            $blocking ? STREAM_CLIENT_CONNECT : STREAM_CLIENT_ASYNC_CONNECT))) {
                return !ErrorTrigger::raise($errMsg);    
        }

        return true;
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