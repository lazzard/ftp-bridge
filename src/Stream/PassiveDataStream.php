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

use Lazzard\FtpBridge\Response\Response;
use Lazzard\FtpBridge\Error\ErrorTrigger;

/**
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 * 
 * @internal
 */
class PassiveDataStream extends DataStream
{
    /**
     * Opens the data connection stream to the server port sent via the FTP server after
     * sending the PASV command.
     *
     *  {@inheritDoc}
     */
    public function open()
    {
        $this->commandStream->write('PASV');
        $response = new Response($this->commandStream->read());
        if ($response->getCode() !== 227) {
            return !ErrorTrigger::raise($response->getMessage());
        }

        if (!preg_match('/(\d+,){4}+/', $response->getMessage(), $ipMatches)
            || !preg_match('/\d+,\d+\)/', $response->getMessage(), $portMatches)) {
            return !ErrorTrigger::raise("Unable to get the passive IP & PORT from the reply message.");
        }

        $ip    = rtrim(str_replace(",", ".", $ipMatches[0]), ".");
        $ports = explode(",", rtrim($portMatches[0], ")"));
        $port  = ($ports[0] * 256) + $ports[1];

        return $this->openStreamSocket($ip, $port, $this->commandStream->timeout, $this->commandStream->blocking);
    }

    /**
     * @inheritDoc
     */
    public function read()
    {
        $data = '';
        while (!feof($this->stream)) {
            $data .= fread($this->stream, 8192);
        }

        $this->log($data);
        
        return $data;
    }
}