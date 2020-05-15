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
class FtpArrayLogger extends AbstractFtpLogger implements FtpLoggerInterface
{
    /** @var array */
    protected $logs;

    /** @var int */
    protected $mode;

    /**
     * FtpArrayLogger constructor.
     *
     * @param int $mode
     */
    public function __construct($mode)
    {
        $this->mode = $mode;
        $this->logs = [];
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
            $this->logs[] = sprintf("[%s] %s", $level, $message);

        } elseif ($this->mode === self::ARRAY_MODE) {
            $lines = explode(FtpBridge::CRLF, $message);
            foreach ($lines as $line) {
                $this->logs[] = sprintf("[%s] %s", $level, $line);
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