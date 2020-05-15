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

use Lazzard\FtpBridge\FtpBridge;
use Lazzard\FtpBridge\FtpLoggerInterface;

/**
 * FtpArrayLogger
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
class FtpArrayLogger implements 
    FtpLoggerInterface,
    \Countable
{
    /** @var array */
    protected $logs;

    /** @var int */
    protected $mode;

    public function __construct($mode = self::PLAIN_MODE)
    {
        $this->logs = [];
        $this->mode = $mode;
    }

    public function getLogs()
    {
        return $this->logs;
    }

    public function addLog($log)
    {
        if (is_string($log)) {
            if ($this->mode === self::PLAIN_MODE) {
                $this->logs[] = $log;
            } else {
                $lines = explode(FtpBridge::CRLF, $log);
                foreach ($lines as $line) {
                    $this->logs[] = $line;
                }
            }
        }
    }

    public function clear()
    {
        $this->logs[] = null;
    }

    public function count()
    {
        return count($this->logs);
    }
}   