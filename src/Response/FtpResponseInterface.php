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


/**
 * FtpResponseInterface
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
interface FtpResponseInterface
{
    /**
     * Gets a full response string representation.
     *
     * @return string
     */
    public function getResponse();

    /**
     * Gets reply code.
     *
     * @return int
     */
    public function getCode();

    /**
     * Gets reply text.
     *
     * @return string
     */
    public function getMessage();

    /**
     * Returns true if the FTP replay consists of multiple lines, false if one line.
     *
     * @return bool
     */
    public function isMultiline();
}