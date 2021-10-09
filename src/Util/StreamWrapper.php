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
}