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
 * Provides methods to parse a raw FTP response string.
 */
class ResponseParser
{
    /** @var string */
    protected $raw;

    protected static $matches = array(
        'code'      => '/^\d{3}/',
        'message'   => '/^\d{3}(\s|-)(.*)/',
        'multiline' => '/^\d{3}-/',
    );

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
     */
    public function toArray()
    {
        return array(
            'code'      => $this->parseCode(),
            'message'   => $this->parseMessage(),
            'multiline' => $this->isMultiline(),
        );
    }

    /**
     * @return int|false
     */
    protected function parseCode()
    {
        if (!preg_match(self::$matches['code'], $this->raw, $matches)) {
            return false;
        }

        return (int)$matches[0];
    }

    /**
     * @return string|false
     */
    protected function parseMessage()
    {
        if (!preg_match(self::$matches['message'], $this->raw, $matches)) {
            return false;
        }

        return str_replace("\r",'', $matches[2]);  // remove the carriage return from the end
    }

    /**
     * @return bool
     */
    protected function isMultiline()
    {
        // according to RFC959, an FTP replay may consists of multiple lines and at least one line,
        // to check weather if a replay consists of multiple lines or not the RFC959 sets a convention,
        // for multiple lines replies the first line must be on a special format, the replay code
        // must immediately followed by a minus "-" character.
        //@link https://tools.ietf.org/html/rfc959#section-4
        return (bool)@preg_match(self::$matches['multiline'], $this->raw);
    }
}