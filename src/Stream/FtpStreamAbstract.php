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
 * Class FtpStreamAbstract
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
abstract class FtpStreamAbstract
{
    /**
     * Carriage return and line feed used in the end of FTP commands as defined in RFC959.
     */
    const CRLF = "\r\n";

    /**
     * Stream socket.
     *
     * @var resource
     */
    protected $socket;

    /** @var string */
    protected $host;

    /** @var string */
    protected $username;

    /** @var string */
    protected $password;

    /** @var int */
    protected $port;

    /** @var int */
    protected $timeout;

    /** @var bool */
    protected $blocking;

    /**
     * FtpStreamAbstract constructor.
     *
     * @param string $host
     * @param string $username
     * @param string $password
     * @param int    $port
     * @param int    $timeout
     * @param bool   $blocking
     */
    public function __construct($host, $username, $password, $port = 21, $timeout = 90, $blocking = true)
    {
        return fclose($this->stream);
    }
}