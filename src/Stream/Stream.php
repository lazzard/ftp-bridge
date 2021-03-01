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

use Lazzard\FtpBridge\Logger\FtpLoggerInterface;
use Lazzard\FtpBridge\Response\FtpResponse;

/**
 * Abstracts FTP streams shared behavior.
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 *
 * @internal
 */
abstract class Stream implements Streamable
{
    /** @var resource */
    public $stream;

    /** @var FtpLoggerInterface */
    public $logger;

    /**
     * StreamableAbstract constructor.
     *
     * @param FtpLoggerInterface $logger
     */
    public function __construct(FtpLoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    final public function close()
    {
        return fclose($this->stream);
    }

    /**
     * Internal logging method.
     *
     * @param string $message
     *
     * @return void
     */
    final protected function log($message)
    {
        if (!is_null($this->logger)) {
            // TODO 400 ?
            $response = new FtpResponse($message);
            if ($response->getCode() < 400) {
                $this->logger->info($message);
            } else {
                $this->logger->error($message);
            }
        }
    }
}