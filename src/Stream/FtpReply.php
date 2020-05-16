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

/**
 * An FTP replay class
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
class FtpReply
{
    /** @var string */
    protected $reply;

    /** @var int */
    protected $code;

    /** @var string  */
    protected $message;

    /** @var bool */
    protected $multiline;

    /**
     * FtpReply constructor.
     *
     * @param $reply
     */
    public function __construct($reply)
    {
        $this->reply = $reply;
        $this->setCode();
        $this->setMessage();
        $this->setMultiline();
    }

    /**
     * @return string
     */
    public function getReply()
    {
        return $this->reply;
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Returns true if the FTP replay consists of multiple lines, false if one line.
     *
     * @return bool
     */
    public function isMultiline()
    {
        return $this->multiline;
    }

    /**
     * @return void
     */
    protected function setCode()
    {
        preg_match('/^\d+/', $this->reply, $match);
        $this->code = (int)$match[0];
    }

    /**
     * @return void
     */
    protected function setMessage()
    {
        preg_match('/[A-z ]+.*/', $this->reply, $match);
        $this->message = ltrim($match[0]);
    }

    /**
     * @return void
     */
    protected function setMultiline()
    {
        /**
         * According to RFC959, an FTP replay may consists of multiple lines and at least one line,
         * to check weather if a replay consists of multiple lines or not the RFC959 sets a convention,
         * for the multiple lines replies the first line must be on a special format, the replay code
         * must immediately followed by a minus "-" character.
         *
         * @link https://tools.ietf.org/html/rfc959#section-4
         */
        $this->multiline = preg_match('/\d{2,}-/', $this->reply);
    }
}