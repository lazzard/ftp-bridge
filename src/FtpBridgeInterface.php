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

/**
 * FtpBridgeInterface class
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
interface FtpBridgeInterface
{
    /**
     * Creates a command stream connection.
     *
     * @param string $host
     * @param int    $port     [optional]
     * @param int    $timeout  [optional]
     * @param bool   $blocking [optional]
     */
    public function connect($host, $port = 21, $timeout = 90, $blocking = true);

    /**
     * Logs into the FTP server.
     *
     * Note! this method must be called after as successful connection.
     *
     * @param string $username
     * @param string $password
     *
     * @return void
     */
    public function login($username, $password);

    /**
     * Sends a command to the server thought the control channel.
     *
     * @param string $command
     *
     * @return void
     */
    public function send($command);

    /**
     * Receive and gets the response from the command stream.
     *
     * Note! be careful when calling this method, it will reads
     * the server response from the command stream, if there is
     * no response the method will hang, so you must expect the server
     * responses depending on which command you send.
     *
     * @return array
     */
    public function receive();

    /**
     * Gets synchronously the data from the data stream.
     *
     * @return array
     */
    public function receiveData();

    /**
     * Opens a passive data connection to the remote server.
     *
     * @param bool $passive           [optional]
     * @param bool $usePassiveAddress [optional]
     *
     * @return void
     */
    public function openDataConnection($passive = true, $usePassiveAddress = true);

    /**
     * Sets the transfer type for the next transfer operation.
     *
     * @param string $type [optional] If omitted, the type sets to binary 'I'.
     *
     * @return void
     */
    public function setTransferType($type = self::BINARY);
}