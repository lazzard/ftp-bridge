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
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
abstract class DataStream extends Stream
{
    /** @var CommandStream */
    public $commandStream;

    /**
     * DataStream Constructor
     *
     * @param CommandStream        $commandStream
     * @param StreamWrapper        $streamWrapper
     * @param LoggerInterface|null $logger
     */
    public function __construct(CommandStream $commandStream, $streamWrapper, $logger)
    {
        parent::__construct($streamWrapper, $logger);
        $this->commandStream = $commandStream;
    }
}