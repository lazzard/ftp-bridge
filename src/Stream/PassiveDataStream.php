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
use Lazzard\FtpBridge\Exception\PassiveDataStreamException;
use Lazzard\FtpBridge\Exception\StreamException;

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
     * @return bool
     *
     * @throws PassiveDataStreamException|StreamException
     */
    public function open()
    {
        $this->commandStream->write('PASV');

        $response = new Response($this->commandStream->read());
        $message  = $response->getMessage();

        if ($response->getCode() !== 227) {
            throw new PassiveDataStreamException($message);
        }

        if (!$hostip = $this->parseIPFromMessage($message)) {
            throw new PassiveDataStreamException('Failed to parse the host IP from the "PASV" command reply.');
        }

        if (!$portNumbers = $this->parsePortNumbersFromMessage($message)) {
            throw new PassiveDataStreamException('Failed to parse the data port numbers from the "PASV" command reply.');
        }

        $dataPort = $this->calculateDataPortNumber($portNumbers);

        return $this->openSocketConnection(
            $hostip,
            $dataPort,
            $this->commandStream->timeout,
            $this->commandStream->blocking
        );
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

    /**
     * @param string $message
     *
     * @return bool
     */
    private function parseIPFromMessage($message)
    {
        if (!preg_match('/(\d+,){4}/', $message, $matches)) {
            return false;
        }

        return substr(str_replace(',', '.', $matches[0]), 0, -1);
    }

    /**
     * @param string $message
     *
     * @return array|false
     */
    private function parsePortNumbersFromMessage($message)
    {
        // TODO the FTP replay format to 'PASV' is not standardized
        // @link https://datatracker.ietf.org/doc/html/rfc1123#page-31
        if (!preg_match('/(\d{2,},\d{2,})\)/', $message, $matches)) {
            return false;
        }

        return explode(',', $matches[1]);
    }

    /**
     * @param array $portNumbers
     *
     * @return int
     */
    private function calculateDataPortNumber($portNumbers)
    {
        return ($portNumbers[0] * 256) + $portNumbers[1];
    }
}