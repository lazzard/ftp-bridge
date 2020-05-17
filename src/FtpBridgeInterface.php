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
     * Opens a command stream connection.
     *
     * @param string $host     The remote host name or the IP address.
     * @param int    $port     [optional] The remote server port to connect to, if omitted the port 21 will be used.
     * @param int    $timeout  [optional] Specifies the connection timeout of all FTP transfer operations, default sets
     *                         to 90.
     * @param bool   $blocking [optional] The transfer mode, the blocking mode is the default.
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
     * Receives and gets the response from the command stream.
     *
     * @return array
     */
    public function receive();

    /**
     * Receives and reads the data from the data stream.
     *
     * @return array
     */
    public function receiveData();

    /**
     * Opens the data connection.
     *
     * @param bool $passive           [optional] Opens a passive data connection.
     * @param bool $usePassiveAddress [optional] Specifies if the connection will uses the passive IP address returned
     *                                in the PASV command to open the connection, or the IP address or the host name
     *                                which supplied in the {@link FtpBridge::connect()} method.
     *
     * @return void
     */
    public function openDataConnection($passive = true, $usePassiveAddress = true);

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