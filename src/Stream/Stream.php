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
use Lazzard\FtpBridge\Logger\LogLevel;

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
     * @param LoggerInterface $logger
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
        $write = fwrite($this->stream, trim($command).self::CRLF);

        if ($this->logger) {
            $this->logger->command($command.self::CRLF);
        }

        return $write !== 0 && $write === false ? false : true;
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
     * Opens an FTP stream socket.
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
        if (!($this->stream = @fsockopen($host, $port, $errno, $errMsg))) {
            return !ErrorTrigger::raise($errMsg);
        }

        stream_set_timeout($this->stream, $timeout);
        stream_set_blocking($this->stream, $blocking);

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