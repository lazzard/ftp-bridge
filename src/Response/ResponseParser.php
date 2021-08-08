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

use Lazzard\FtpBridge\Exception\ResponseParserException;

/**
 * Provides methods to parse a raw FTP response string.
 */
class ResponseParser
{
    /** @var string */
    protected $raw;

    const MATCHES = [
        'code'      => '/^\d+/',
        'message'   => '/[A-z ]+.*/',
        'multiline' => '/^\d{2,}-/',
    ];

    /**
     * @param string $raw
     */
    public function __construct($raw)
    {
        $this->raw = $raw;
    }

    /**
     * Parses a raw FTP response string into an array representation.
     *
     * @return array
     *
     * @throws ResponseParserException
     */
    public function parseToArray()
    {
        return array(
            'code'      => $this->parseCode(),
            'message'   => $this->parseMessage(),
            'multiline' => $this->isMultiline(),
        );
    }

    /**
     * @return false|int
     *
     * @throws ResponseParserException
     */
    protected function parseCode()
    {
        $result = preg_match(self::MATCHES['code'], $this->raw, $matches);

        if ($result === false) {
            throw new ResponseParserException("Failed to match " . self::MATCHES['code'] . " pattern.");
        }

        if (count($matches) > 0) {
            return (int)$matches[0];
        }

        return false;
    }

    /**
     * @return string
     *
     * @throws ResponseParserException
     */
    protected function parseMessage()
    {
        $result = preg_match(self::MATCHES['message'], $this->raw, $matches);

        if ($result === false) {
            throw new ResponseParserException("Failed to match " . self::MATCHES['message'] . " pattern.");
        }

        return str_replace("\r",'',$matches[0]);  // remove the carriage return from the end
    }

    /**
     * @return bool
     *
     * @throws ResponseParserException
     */
    protected function isMultiline()
    {
        // according to RFC959, an FTP replay may consists of multiple lines and at least one line,
        // to check weather if a replay consists of multiple lines or not the RFC959 sets a convention,
        // for multiple lines replies the first line must be on a special format, the replay code
        // must immediately followed by a minus "-" character.
        //@link https://tools.ietf.org/html/rfc959#section-4
        $match = preg_match(self::MATCHES['multiline'], $this->raw);

        if ($match === false) {
            throw new ResponseParserException("Failed to match the response multiline pattern.");
        }

        return $match === 1;
    }
}