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
use Lazzard\FtpBridge\Util\StreamWrapper;

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

    /** @var StreamWrapper */
    protected $streamWrapper;

    /**
     * @param string             $filePath
     * @param bool               $append
     * @param StreamWrapper|null $streamWrapper
     *
     * @throws FileLoggerException
     */
    public function __construct($filePath, $append = false, $streamWrapper = null)
    {
        $this->filePath      = $filePath;
        $this->append        = $append;
        $this->streamWrapper = $streamWrapper ?: new StreamWrapper;

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
            throw new FileLoggerException("($this->filePath) not found or isn't readable.");
        }

        // rewind pointer position to 0
        $this->streamWrapper->fseek(0);

        $content = '';

        while (($line = $this->streamWrapper->fgets()) !== false) {
            $content .= $line;
        }

        return $content;
    }

    /**
     * @inheritDoc
     */
    public function log($level, $message)
    {
        $this->streamWrapper->fwrite(sprintf("%s %s", $level, $message));
    }

    /**
     * {@inheritDoc}
     *
     * @throws FileLoggerException
     */
    public function clear()
    {
        if (ftruncate($this->stream, 0) === false) {
            throw new FileLoggerException("Unable to clear the file ($this->filePath) content.");
        }
    }

    /**
     * {@inheritDoc}
     *
     * @throws FileLoggerException
     */
    public function count()
    {
        $logs = $this->getLogs();

        if (!empty($logs)) {
            return count(explode(FtpBridge::CRLF, $logs)) - 1;
        }

        return 0;
    }


    public function __destruct()
    {
        if (file_exists($this->filePath)) {
            $this->streamWrapper->fclose();
        }
    }

    /**
     * @throws FileLoggerException
     */
    protected function open()
    {
        if (($stream = fopen($this->filePath, $this->append ? 'a+' : 'w+')) === false) {
            throw new FileLoggerException("Cannot open/create the logging file ($this->filePath).");
        }

        $this->stream = $stream;

        $this->streamWrapper->setStream($stream);
    }
}