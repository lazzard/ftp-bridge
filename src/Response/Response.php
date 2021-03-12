<?php

/**
 * This file is part of the Lazzard/ftp-bridge package.
 *
 * (c) El Amrani Chakir <elamrani.sv.laza@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lazzard\FtpBridge\Response;

use Lazzard\FtpBridge\Parser\ResponseParser;

/**
 * Represents an FTP replay.
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 *
 * @internal
 */
class Response
{
    /** @var string */
    protected $reply;

    /** @var int */
    protected $code;

    /** @var string */
    protected $message;

    /** @var bool */
    protected $multiline;

    /**
     * @param string $reply The raw FTP reply string.
     */
    public function __construct($reply)
    {
        $parser          = new ResponseParser($reply);
        $response        = $parser->parse();
        $this->reply     = $reply;
        $this->code      = $response['code'];
        $this->message   = $response['message'];
        $this->multiline = $response['multiline'];
    }

    /**
     * Gets the raw (original) reply string sent by the server.
     * @return string
     */
    public function getReply()
    {
        return $this->reply;
    }

    /**
     * Gets reply code.
     *
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Gets reply message.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Whether the FTP response consists of multiple lines or not.
     *
     * @return bool
     */
    public function isMultiline()
    {
        return $this->multiline;
    }
}