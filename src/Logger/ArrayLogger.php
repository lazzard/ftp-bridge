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
 * ArrayLogger
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
class ArrayLogger extends Logger
{
    /** @var array */
    protected $logs;

    /**
     * @param int $mode
     */
    public function __construct($mode = LoggerInterface::PLAIN_MODE)
    {
        parent::__construct($mode);
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
        if ($this->mode === self::PLAIN_MODE) {
            $this->logs[] = sprintf("%s %s", $level, $message);
        } elseif ($this->mode === self::ARRAY_MODE) {
            $lines = explode(self::CRLF, $message);
            $this->logs[] = sprintf("%s %s", $level, $lines[0]);
            foreach ($lines as $key => $line) {
                if ($key === 0 || $line === '') continue;
                $this->logs[] = $line;
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function clear()
    {
        $this->logs[] = null;
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return count($this->logs) - 1;
    }
}