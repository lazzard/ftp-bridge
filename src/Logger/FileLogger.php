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

/**
 * FileLogger
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
    public function __construct($filePath, $mode = LoggerInterface::PLAIN_MODE, $append = false)
    {
        parent::__construct($mode);

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
            throw new FileLoggerException($this->filePath . " file is not found or isn't readable.");
        }

        if (($content = file_get_contents($this->filePath)) === false) {
            throw new FileLoggerException("Failed to get the " . $this->filePath . " content.");
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
        if ($this->mode === self::PLAIN_MODE) {

            $this->write(sprintf("%s %s", $level, $message));

        } elseif ($this->mode === self::ARRAY_MODE) {
            // remove the '\r\n' from the end of the message
            $message = preg_replace("/[\r\n]$/", '', $message);
            $lines   = explode(self::CRLF, $message);
            $indent  = str_repeat(' ', 4);

            $output = sprintf(
                "%s[%s] array() %s [%s",
                ftell($this->stream) ? self::CRLF : '',
                count($lines),
                $level,
                self::CRLF
            );

            foreach ($lines as $line) {
                $output .= sprintf("%s%s%s", $indent, $line, self::CRLF);
            }

            $output .= ']';

            if ($this->write($output) === false) {
                throw new FileLoggerException("Cannot write to file " . $this->filePath.".");
            }
        }
    }

    /**
     * {@inheritDoc}
     *
     * @throws FileLoggerException
     */
    public function clear()
    {
        if ($this->write('') === false) {
            throw new FileLoggerException("Unable to clear the file " . $this->filePath . " content.");
        }

        return true;
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