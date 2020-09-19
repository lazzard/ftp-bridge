<?php
/**
 * This file is part of the Lazzard/ftp-bridge package.
 *
 * (c) El Amrani Chakir <elamrani.sv.laza@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Lazzard\FtpBridge;

use Lazzard\FtpBridge\Logger\FtpLoggerInterface;
use Lazzard\FtpBridge\Response\FtpResponse;
use Lazzard\FtpBridge\Stream\FtpCommandStream;
use Lazzard\FtpBridge\Stream\FtpDataStream;

/**
 * FtpBridge class holds the necessary methods to start sending commands and
 * receiving FTP responses with some useful utility methods.
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
class FtpBridge implements FtpBridgeInterface
{
    /**
     * Transfers modes.
     */
    const ASCII  = 'A';
    const BINARY = 'I';
    const EBCDIC = 'E';

    /** @var FtpLoggerInterface */
    public $logger;

    /** @var FtpCommandStream */
    public $commandStream;

    /** @var FtpDataStream */
    public $dataStream;

    /**
     * FtpBridge constructor
     *
     * @param FtpLoggerInterface|null $logger
     */
    public function __construct(FtpLoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function send($command)
    {
        $this->commandStream->send($command);
    }

    /**
     * @inheritDoc
     */
    public function receive()
    {
        return $this->commandStream->receive();
    }

    /**
     * @inheritDoc
     */
    public function receiveData()
    {
        return $this->dataStream->receive();
    }

    /**
     * {@inheritDoc}
     */
    public function connect($host, $port = 21, $timeout = 90, $blocking = true)
    {
        $this->commandStream = new FtpCommandStream($this->logger, $host, $port, $timeout, $blocking);
        return $this->commandStream->open();
    }

    /**
     * {@inheritDoc}
     */
    public function login($username, $password)
    {
        $this->send(sprintf("USER %s", $username));
        $response = new FtpResponse($this->receive());

        /**
         * 230 : User logged in, proceed.
         */
        if ($response->getCode() === 230) {
            return true;
        }

        /**
         * 331 : User name okay, need password.
         */
        if ($response->getCode() === 331) {
            $this->send(sprintf('PASS %s', $password));

            $response = new FtpResponse($this->receive());

            // TODO 202 code
            /**
             * 230 : User logged in, proceed.
             * 202 : Already logged with USER
             */
            if (in_array($response->getCode(), [202, 230])) {
                return true;
            }

            return !trigger_error(
                sprintf("PASS command failed : %s", $response->getMessage()),
                E_USER_WARNING
            );
        }

        return !trigger_error(
            sprintf("USER command failed : %s", $response->getMessage()),
            E_USER_WARNING
        );
    }

    /**
     * {@inheritDoc}
     */
    public function openDataConnection($passive = true)
    {
        $this->dataStream = new FtpDataStream($this->logger, $this->commandStream, $passive);
        return $this->dataStream->open();
    }

    /**
     * @inheritDoc
     */
    public function setTransferType($type)
    {
        $this->send('TYPE ' . $type);
        $this->receive();
    }
}