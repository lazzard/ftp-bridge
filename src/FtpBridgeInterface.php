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
     * @return resource
     */
    public function getDataStream();

    /**
     * @return resource
     */
    public function getCommandStream();

    /**
     * @return array
     */
    public function getResponse();

    /**
     * @return int
     */
    public function getResponseCode();

    /**
     * @return string
     */
    public function getResponseMessage();

    /**
     * @return bool
     */
    public function isSuccess();
    
    /**
     * Sends a command to the server thought the control channel.
     *
     * @param $command
     */
    public function putCmd($command);

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
    public function getCmd();

    /**
     * Gets synchronously the data from the data stream.
     *
     * @return array
     */
    public function getData();

    /**
     * Sets the transfer type for the next transfer operation.
     *
     * @param string $type If omitted, the type sets to Binary 'I'
     */
    public function setTransferType($type = self::BINARY);

    /**
     * Opens a data stream connection based on the IP address and the port number
     * returned from 'PASV' command.
     *
     * @throws \RuntimeException
     */
    public function openPassiveConnection();
}