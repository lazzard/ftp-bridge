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
 * StreamInterface defines an FTP stream class behavior.
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 * 
 * @internal
 */
interface StreamInterface
{
    /**
     * Opens the stream resource.
     *
     * @return bool Returns true on success, false otherwise, an E_WARNING_ERROR also will raised.
     */
    public function open();

    /**
     * Closes the stream resource.
     *
     * @return bool
     */
    public function close();

    /**
     * Writes the giving string content to the stream resource.
     *
     * @param string $string
     *
     * @return bool
     */
    public function write($string);

    /**
     * Reads the content from the stream resource.
     *
     * @return string|false
     */
    public function read();
}