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
 * FtpLoggerInterface
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
interface FtpLoggerInterface
{
    /**
     * @return mixed
     */
    public function getLogs();

    /**
     * @param int    $level
     * @param string $message
     *
     * @return void
     */
    public function log($level, $message);

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
     * @return void
     */
    public function clear();

    /**
     * @return int
     */
    public function count();
}