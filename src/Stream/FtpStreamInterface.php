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
 * FtpStreamInterface defines an FTP stream behavior.
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 *
 * @internal
 */
interface FtpStreamInterface
{
    /**
     * Carriage return and line feed used in the end of FTP commands as defined in RFC959.
     */
    const CRLF = "\r\n";

    /**
     * Opens the stream socket.
     *
     * @return bool Returns true on success, false otherwise, an E_WARING_ERROR also raised.
     */
    public function open();

    /**
     * Closes FTP stream socket.
     *
     * @return bool
     */
    public function close();

    /**
     * Sends an arbitrary command to the FTP server.
     *
     * @param string $command
     *
     * @return int|false
     */
    public function send($command);

    /**
     * Receive the FTP reply.
     *
     * Note! no parsing performed on the replay string.
     *
     * @return string|false
     */
    public function receive();
}