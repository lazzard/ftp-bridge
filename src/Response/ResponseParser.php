<?php

/*
 * This file is part of the Lazzard/ftp-bridge package.
 *
 * (c) El Amrani Chakir <elamrani.sv.laza@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lazzard\FtpBridge\Response;

/**
 * @internal
 */
class ResponseParser
{
    /** @var string */
    protected $reply;

    public function __construct($reply)
    {
        $this->reply = $reply;
    }

    public function parse()
    {
        return array(
          'code' => $this->parseCode(),
          'message' => $this->parseMessage(),
          'multiline' => $this->isMultiline(),
        );
    }

    protected function parseCode()
    {
        if (preg_match('/^\d+/', $this->reply, $match) === 1) {
            return (int)$match[0];
        }
    }

    protected function parseMessage()
    {
        if (preg_match('/[A-z ]+.*/', $this->reply, $matches)) {
            // remove the carriage return from the end
            return str_replace("\r", '', $matches[0]);
        }
    }

    protected function isMultiline()
    {
        // according to RFC959, an FTP replay may consists of multiple lines and at least one line,
        // to check weather if a replay consists of multiple lines or not the RFC959 sets a convention,
        // for multiple lines replies the first line must be on a special format, the replay code
        // must immediately followed by a minus "-" character.
        //@link https://tools.ietf.org/html/rfc959#section-4
        return preg_match('/^\d{2,}-/', $this->reply);
    }
}