<?php

namespace Lazzard\FtpBridge\Util;

class StreamWrapper
{
    private $handle;

    public function setHandle($handle)
    {
        $this->handle = $handle;
    }

    /**
     * @param string   $host
     * @param string   $timeout
     * @param string   $blocking
     * @param callable $callback
     *
     * @return resource|false
     */
    public function streamSocketClient($host, $timeout, $blocking, $callback)
    {
        if (!$resource = @stream_socket_client($host, $errno, $errMsg, $timeout, $blocking)) {
            call_user_func($callback, $errMsg);
        }

        return $resource;
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
        return fwrite($this->handle, $string);
    }

    /**
     * @return string|false
     */
    public function fgets()
    {
        return fgets($this->handle);
    }

    /**
     * @return bool
     */
    public function fclose()
    {
        return fclose($this->handle);
    }

    /**
     * @param int $offset
     *
     * @return int
     */
    public function fseek($offset)
    {
        return fseek($this->handle, $offset);
    }
}