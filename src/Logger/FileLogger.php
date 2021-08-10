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

use Lazzard\FtpBridge\Exception\FileLoggerException;
use Lazzard\FtpBridge\FtpBridge;

/**
 * Logs the FTP session into a file system.
 * 
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
class FileLogger extends Logger
{
    /** @var resource */
    protected $stream;

    /** @var string */
    protected $filePath;

    /** @var bool */
    protected $append;

    /**
     * @param string $filePath
     * @param int    $mode
     * @param bool   $append
     */
    public function __construct($filePath, $append = false)
    {
        $this->filePath = $filePath;
        $this->append   = $append;
        $this->open();
    }

    public function getStream()
    {
        return $this->stream;
    }

    /**
     * @return string
     *
     * @throws FileLoggerException
     */
    public function getLogs()
    {
        if (!file_exists($this->filePath) || !is_readable($this->filePath)) {
            throw new FileLoggerException("$this->filePath file is not found or isn't readable.");
        }

        if (($content = file_get_contents($this->filePath)) === false) {
            throw new FileLoggerException("Failed to retrieve logs from $this->filePath.");
        }

        return $content;
    }

    /**
     * {@inheritDoc}
     *
     * @throws FileLoggerException
     */
    public function log($level, $message)
    {
        $this->write(sprintf("%s %s", $level, $message));
    }

    /**
     * {@inheritDoc}
     *
     * @throws FileLoggerException
     */
    public function clear()
    {
        if ($this->write('') === false) {
            throw new FileLoggerException("Unable to clear the file {$this->filePath}'s content.");
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return count(explode(FtpBridge::CRLF, $this->getLogs())) - 1;
    }

    public function __destruct()
    {
        if (file_exists($this->filePath)) {
            $this->close();
        }
    }

    protected function open()
    {
        return $this->stream = fopen($this->filePath, $this->append ? 'a' : 'w');
    }

    protected function write($content)
    {
        return fwrite($this->stream, $content);
    }

    protected function close()
    {
        return fclose($this->stream);
    }
}