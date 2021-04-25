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

/**
 * Absracts a data stream socket.
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 * 
 * @internal
 */
abstract class DataStream extends Stream
{
    /** @var StreamInterface */
    public $commandStream;

    /**
     * Opens a data stream socket.
     *
     * @param LoggerInterface $logger
     * @param StreamInterface $commandStream
     */
    public function __construct($logger, $commandStream)
    {
        parent::__construct($logger);
        $this->commandStream   = $commandStream;
    }
}