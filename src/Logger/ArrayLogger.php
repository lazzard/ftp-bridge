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

/**
 * Logs an FTP session to an array.
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
class ArrayLogger extends Logger
{
    /** @var array */
    protected $logs;

    /**
     * ArrayLogger Construnctor.
     */
    public function __construct()
    {   
        $this->logs = array();
    }

    /**
     * @return array
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * @inheritDoc
     */
    public function log($level, $message)
    {
        $this->logs[] = sprintf("%s %s", $level, $message);
    }

    /**
     * @inheritDoc
     */
    public function clear()
    {
        $this->logs = array();
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return count($this->logs);
    }
}