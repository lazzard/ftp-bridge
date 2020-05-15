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
 * FtpFileLogger
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
class FtpFileLogger implements 
    FtpLoggerInterface,
    \Countable
{
    /** @var string */
    const EOL = "\r\n";

    /** @var resource */
    protected $handle;

    /** @var string */
    protected $filePath;

    /** @var bool */
    protected $append;

    /** @var int */
    protected $mode;

    public function __construct($filePath, $append = false, $mode = self::PLAIN_MODE)
    {
        $this->filePath = $filePath;
        $this->append = $append;
        $this->mode = $mode;

        $this->open();
    }

    public function getLogs()
    {
        return file_get_contents($this->filePath);
    }

    public function addLog($log)
    {
        if (is_string($log)) {
            if ($this->mode === self::PLAIN_MODE) {
                $this->write($log);
                
            } elseif ($this->mode === self::ARRAY_MODE) {
                $lines = explode(FtpBridge::CRLF, $log);
                array_pop($lines);
                
                $indent = str_repeat(' ', 4);

                $output = sprintf("%s[%s] array() [%s", ftell($this->handle) === 0 ? '' : self::EOL, count($lines), FtpBridge::CRLF);

                foreach ($lines as $line) {
                    $output .= sprintf("%s%s%s", $indent, $line, self::EOL);
                }
            
                $output .= ']';
                
                $this->write($output);
            }
        }
    }

    public function clear()
    {
        fwrite($this->filePath, '');
    }

    public function count()
    {
        if ($this->mode === self::PLAIN_MODE) {
            return count(explode(self::EOL, $this->getLogs())) - 1;
        } else {
            return substr_count($this->getLogs(), 'array');
        }
    }

    protected function write($content)
    {
        fwrite($this->handle, $content);
    }

    protected function open()
    {
        $this->handle = fopen($this->filePath, $this->append ? 'a' : 'w');
    }

    protected function close()
    {
        fclose($this->handle);
    }
}