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
 * FtpBridgeInterface interface
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
interface FtpBridgeInterface
{
    /**
     * Sends a command to the server thought the control channel.
     *
     * @param string $command
     *
     * @return void
     */
    public function send($command);

    /**
     * Receives and gets the response from the command stream.
     *
     * @return string
     */
    public function receive();

    /**
     * Receives and reads the data from the data stream.
     *
     * @return string
     */
    public function receiveData();

    /**
     * Opens a command stream connection.
     *
     * @param string $host     The remote host name or the IP address.
     * @param int    $port     [optional] The remote server port to connect to, if omitted the port 21 will be used.
     * @param int    $timeout  [optional] Specifies the connection timeout of all FTP transfer operations, default sets
     *                         to 90.
     * @param        $blocking $blocking [optional] The transfer mode, the blocking mode is the default.
     *
     * @return bool Returns true on success, false on failure and an E_WARNING error raised.
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
     * @return bool Returns true on success, false on failure and an E_WARNING error raised.
     */
    public function login($username, $password);

    /**
     * Opens the data connection.
     *
     * @param bool $passive [optional] Specifies weather to use a passive or active data connection.
     *
     * @return bool Returns true on success, false on failure and an E_WARNING error raised.
     */
    public function openDataConnection($passive = true);

    /**
     * Sets the transfer type for the next transfer operation.
     *
     * @param string $type The transfer type can be either {@link FtpBridge::BINARY} or {@link FtpBridge::ASCII} or
     *                     {@link FtpBridge::EBCDIC}.
     *
     * @return void
     */
    public function setTransferType($type);
}