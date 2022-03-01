<?php

/**
 * This file is part of the Lazzard/ftp-bridge package.
 *
 * (c) El Amrani Chakir <elamrani.sv.laza@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lazzard\FtpBridge\Stream;

use Lazzard\FtpBridge\Logger\LoggerInterface;
use Lazzard\FtpBridge\Util\StreamWrapper;

/**
 * Represents an FTP command stream (control channel).
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 * 
 * @internal
 */
class CommandStream extends Stream
{
    /** @var string */
    public $host;

    /** @var int */
    public $port;

    /** @var int */
    public $timeout;

    /** @var bool */
    public $blocking;

    /**
     * CommandStream constructor.
     *
     * @param string               $host
     * @param int                  $port
     * @param int                  $timeout
     * @param bool                 $blocking
     * @param StreamWrapper        $streamWrapper
     * @param LoggerInterface|null $logger
     */
    public function __construct($host, $port, $timeout, $blocking, $streamWrapper, $logger)
    {
        parent::__construct($streamWrapper, $logger);
        $this->host     = $host;
        $this->port     = $port;
        $this->timeout  = $timeout;
        $this->blocking = $blocking;
    }

    /**
     * @inheritDoc
     */
    public function read()
    {
        $response = '';

        while (true) {
            if (!$line = $this->streamWrapper->fgets()) {
                break;
            }

            $response .= $line;

            // To distinguish the end of an FTP reply, the RFC959 indicates that the last line of
            // a reply must be on a special format, it must start with 3 digits followed
            // by a space.
            //@link https://tools.ietf.org/html/rfc959#section-4
            if (preg_match('/^\d{3}+ /', $line) !== 0) {
                break;
            }
        }

        $this->log($response);

        return $response;
    }

    /**
     * @inheritDoc
     */
    public function open()
    {
        if ($this->openSocketConnection($this->host, $this->port, $this->timeout, $this->blocking)) {
            $this->read();
            return true;
        }

        return false;
    }
}