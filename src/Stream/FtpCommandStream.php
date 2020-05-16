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

/**
 * Represents a command stream socket.
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
class FtpCommandStream extends FtpStreamAbstract
{

    public function __construct($host, $username, $password, $port = 21, $timeout = 90, $blocking = true)
    {
        parent::__construct($host, $username, $password, $port, $timeout, $blocking);

        $this->login();
    }

    /**
     * {@inheritDoc}
     *
     * @throws \RuntimeException
     */
    protected function connect()
    {
        // TODO wrong giving host resolving
        if ( ! ($this->socket = fsockopen($this->host, $this->port, $errno, $errMsg))) {
            throw new \RuntimeException("Opening socket connection was failed : [{$errMsg}]");
        }

        stream_set_blocking($this->socket, $this->blocking);
        stream_set_timeout($this->socket, $this->timeout);

        $this->receive();
    }

    /**
     * Logs into the FTP server.
     *
     * Note! this method must be called after as successful connection.
     *
     * @return void
     *
     * @throws \RuntimeException
     */
    protected function login()
    {
        // TODO SSL/TLS before login
        $this->send(sprintf('USER %s%s', $this->username, self::CRLF));
        $reply = new FtpReply($this->receive());

        /**
         * 230 : User logged in, proceed.
         * 331 : User name okay, need password.
         */

        if ($reply->getCode() === 230) {
            return;
        }

        if ($reply->getCode() === 331) {
            $this->send(sprintf('PASS %s%s', $this->password, self::CRLF));
            $reply = new FtpReply($this->receive());

            // TODO 202 code
            /**
             * 230 : User logged in, proceed.
             * 202 : Already logged with USER
             */
            if (in_array($reply->getCode(), [202, 230])) {
                return;
            }

            throw new \RuntimeException(sprintf("PASS command fails : %s", $reply->getMessage()));
        }

        throw new \RuntimeException(sprintf("PASS command fails : %s", $reply->getMessage()));
    }
}