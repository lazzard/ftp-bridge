<?php

namespace Lazzard\FtpBridge\Util;

use Lazzard\FtpBridge\Exception\StreamWrapperException;

class StreamWrapper
{
    /** @var resource */
    protected $stream;

    /**
     * @param resource $stream
     *
     * @throws StreamWrapperException
     */
    public function setStream($stream)
    {
        if (!is_resource($stream)) {
            throw new StreamWrapperException(sprintf(
                "StreamWrapper stream handle must be of type resource, '%s' type given.",
                gettype($stream)
            ));
        }

        $this->stream = $stream;
    }

    /**
     * @param resource $stream
     *
     * @return void
     */
    public function getStream($stream)
    {
        $this->stream = $stream;
    }

    /**
     * @param string $host
     * @param string $timeout
     * @param string $blocking
     *
     * @return resource|false
     */
    public function streamSocketClient($host, $timeout, $blocking)
    {
        return @stream_socket_client($host, $errno, $errMsg, $timeout, $blocking);
    }

    /**
     * @param string $host
     * @param int    $context
     *
     * @return resource|false
     */
    public function streamSocketServer($host, $context)
    {
        return @stream_socket_server($host, $errno, $errMsg, $context);
    }

    /**
     * @return false|string
     */
    public function streamSocketGetName()
    {
        return stream_socket_get_name($this->stream, false);
    }

    public function streamSocketAccept()
    {
        return stream_socket_accept($this->stream);
    }

    /**
     * @param string $filename
     * @param string $mode
     *
     * @return resource|false
     */
    public function fopen($filename, $mode)
    {
        return fopen($filename, $mode);
    }

    /**
     * @param string $string
     *
     * @return int|false
     */
    public function fwrite($string)
    {
        return fwrite($this->stream, $string);
    }

    /**
     * @return string|false
     */
    public function fgets()
    {
        return fgets($this->stream);
    }

    /**
     * @return bool
     */
    public function fclose()
    {
        return fclose($this->stream);
    }

    /**
     * @param int $offset
     *
     * @return int
     */
    public function fseek($offset)
    {
        return fseek($this->stream, $offset);
    }

    /**
     * @return bool
     */
    public function feof()
    {
        return feof($this->stream);
    }

    /**
     * @param int $length
     *
     * @return false|string
     */
    public function fread($length)
    {
        return fread($this->stream, $length);
    }
}