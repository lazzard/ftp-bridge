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
use Lazzard\FtpBridge\Response\Response;

/**
 * Abstracts FTP streams shared behavior.
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
abstract class Stream implements StreamInterface
{
    /** @var resource */
    public $stream;

    /** @var LoggerInterface */
    public $logger;

    /**
     * StreamableAbstract constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger = null)
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
            $response = new Response($message);
            if ($response->getCode() < 400) {
                $this->logger->info($message);
            } else {
                $this->logger->error($message);
            }
        }
    }
}