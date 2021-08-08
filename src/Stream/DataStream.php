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
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
abstract class DataStream extends Stream
{
    /** @var CommandStream */
    public $commandStream;

    /**
     * @param LoggerInterface $logger
     * @param CommandStream $commandStream
     */
    public function __construct(LoggerInterface $logger, CommandStream $commandStream)
    {
        parent::__construct($logger);
        $this->commandStream = $commandStream;
    }
}