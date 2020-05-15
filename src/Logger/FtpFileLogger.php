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

use Lazzard\FtpBridge\FtpLoggerInterface;

/**
 * Logger
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
class FtpFileLogger implements 
    FtpFileLoggerInterface,
    FtpLoggerInterface
{
    /** @var $handle */
    protected $handle;

    /** @var $filePath */
    protected $filePath;

    /** @var $append */
    protected $append;

    public function __construct($filePath, $append = false)
    {
        $this->handle = $this->open($filePath, $append);
        $this->filePath = $filePath;
        $this->append = $append;
    }

    public function getLogs()
    {
        return file_get_contents($this->filePath);
    }
    
    public function addLog($log)
    {
        fwrite($this->handle, $log);
    }

    public function clear()
    {
        file_put_contents($this->filePath, '');
    }

    protected function open($filePath, $append)
    {
        return fopen($filePath, $append ? 'a' : 'w');
    }

    protected function close()
    {
        fclose($this->handle);
    }

}