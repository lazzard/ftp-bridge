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

namespace Lazzard\FtpBridge\Logger;

use Lazzard\FtpBridge\FtpLoggerInterface;

/**
 * Logger
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
class FtpArrayLogger implements 
    FtpArrayLoggerInterface,
    FtpLoggerInterface
{
    /** @var array */
    protected $logs;

    public function __construct()
    {
        $this->logs = [];
    }

    public function getLogs()
    {
        return $this->logs;
    }

    public function addLog($log)
    {
        $this->logs[] = $log; 
    }

    public function clear()
    {
        $this->logs[] = null;
    }
}   