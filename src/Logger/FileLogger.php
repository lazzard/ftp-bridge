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
 * FileLogger
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
class FileLogger extends Logger
{
    /** @var resource */
    protected $handle;

    /** @var string */
    protected $filePath;

    /** @var bool */
    protected $append;

    /**
     * @param int    $mode
     * @param string $filePath
     * @param bool   $append
     */
    public function __construct($mode = LoggerInterface::PLAIN_MODE, $filePath, $append = false)
    {
        parent::__construct($mode);
        $this->filePath = $filePath;
        $this->append   = $append;
        $this->open();
    }

    /**
     * @return string|false
     */
    public function getLogs()
    {
        return file_get_contents($this->filePath);
    }

    /**
     * @inheritDoc
     */
    public function log($level, $message)
    {
        if ($this->mode === self::PLAIN_MODE) {
            $this->write(sprintf("%s %s", $level, $message));

        } elseif ($this->mode === self::ARRAY_MODE) {
            $lines = explode(self::CRLF, $message);
            array_pop($lines);

            $indent = str_repeat(' ', 4);

            $output = sprintf(
                "%s[%s] array() %s [%s",
                ftell($this->handle) ? self::CRLF : '',
                count($lines),
                $level,
                self::CRLF
            );

            foreach ($lines as $line) {
                $output .= sprintf("%s%s%s", $indent, $line, self::CRLF);
            }

            $output .= ']';
            $this->write($output);
        }
    }

    /**
     * @inheritDoc
     */
    public function clear()
    {
        fwrite($this->handle, '');
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        if ($this->mode === self::PLAIN_MODE) {
            return count(explode(self::CRLF, $this->getLogs())) - 1;
        }
        
        return substr_count($this->getLogs(), 'array');
    }

    public function __destruct()
    {
        $this->close();
    }

    protected function open()
    {
        $this->handle = fopen($this->filePath, $this->append ? 'a' : 'w');
    }

    protected function write($content)
    {
        fwrite($this->handle, $content);
    }

    protected function close()
    {
        fclose($this->handle);
    }
}