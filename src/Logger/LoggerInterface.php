<?php

/**
 * This file is part of the Lazzard/ftp-bridge package.
 *
 * (c) El Amrani Chakir <elamrani.sv.laza@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lazzard\FtpBridge\Logger;

/**
 * Describes an FTP logger instance.
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
interface LoggerInterface
{
    /**
     * @return mixed
     */
    public function getLogs();

    /**
     * @param string $message
     *
     * @return void
     */
    public function info($message);

    /**
     * @param string $message
     *
     * @return void
     */
    public function error($message);

     /**
     * @param string $string
     *
     * @return void
     */
    public function command($string);

        /**
     * @param int    $level
     * @param string $message
     *
     * @return void
     */
    public function log($level, $message);

    /**
     * @return void
     */
    public function clear();

    /**
     * @return int
     */
    public function count();
}