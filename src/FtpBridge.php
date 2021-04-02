<?php

/**
 * This file is part of the Lazzard/ftp-bridge package.
 *
 * (c) El Amrani Chakir <elamrani.sv.laza@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Lazzard\FtpBridge;

use Lazzard\FtpBridge\Error\ErrorTrigger;
use Lazzard\FtpBridge\Logger\LoggerInterface;
use Lazzard\FtpBridge\Response\Response;
use Lazzard\FtpBridge\Stream\CommandStream;
use Lazzard\FtpBridge\Stream\DataStream;

/**
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
class FtpBridge
{
    /**
     * Transfer type representations.
     */
    const TR_TYPE_ASCII      = 'A';
    const TR_TYPE_BINARY     = 'I';
    const TR_TYPE_EBCDIC     = 'E';
    const TR_TYPE_LOCAL      = 'L';
    const TR_TYPE_NON_PRINT  = 'N';
    const TR_TYPE_TELNET     = 'T';
    const TR_TYPE_CR_CONTROL = 'C';

    /** @var LoggerInterface */
    public $logger;

    /** @var Response */
    public $response;

    /** @var CommandStream */
    protected $commandStream;

    /** @var DataStream */
    protected $dataStream;

    /**
     * FtpBridge constructor
     *
     * @param LoggerInterface|null $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * Sends a command to the server thought the control channel.
     *
     * @param string $command
     *
     * @return bool
     */
    public function send($command)
    {
        if (!$this->commandStream || !is_resource($this->commandStream->stream)) {
            throw new \LogicException("The FTP connection must be established first before try sending any commands.");
        }

        return $this->commandStream->write($command);
    }

    /**
     * Writes the string content to the data stream.
     *
     * @param string $string
     * 
     * @return bool
     */
    public function write($string)
    {
        if (!$this->dataStream || !is_resource($this->dataStream->stream)) {
            throw new \LogicException("The FTP data connection must be established first 
                before try writing to the data stream.");
        }

        return $this->dataStream->write($string);
    }

    /**
     * Receives and gets the response from the command stream.
     * 
     * @return Response
     */
    public function receive()
    {
        if (!$this->commandStream || !is_resource($this->commandStream->stream)) {
            throw new \LogicException("The FTP command connection not created yet.");
        }

        return $this->response = new Response($this->commandStream->read());
    }

    /**
     * Receives and reads the data from the data stream.
     *
     * @return string
     */
    public function receiveData()
    {
        if (!$this->dataStream || !is_resource($this->dataStream->stream)) {
            throw new \LogicException("The FTP data connection not created yet.");
        }

        return $this->dataStream->read();
    }

    /**
     * Opens an FTP connection.
     *
     * @param string $host     The remote host name or the IP address.
     * @param int    $port     [optional] The remote server port to connect to, if omitted the port 21 will be used.
     * @param int    $timeout  [optional] Specifies the connection timeout of all FTP transfer operations, default sets
     *                         to 90.
     * @param        $blocking $blocking [optional] The transfer mode, the blocking mode is the default.
     *
     * @return bool Returns true on success, false on failure and an E_WARNING error raised.
     */
    public function connect($host, $port = 21, $timeout = 90, $blocking = true)
    {
        $this->commandStream = new CommandStream($this->logger, $host, $port, $timeout, $blocking);
        return $this->commandStream->open();
    }

    /**
     * Logs into the FTP server.
     *
     * Note: this method must be called after a successful connection.
     *
     * @param string $username
     * @param string $password
     *
     * @return bool Returns true on success, false on failure and an E_WARNING error 
     *              will be raised.
     */
    public function login($username, $password)
    {
        $this->send("USER $username");
        $this->receive();

        if ($this->response->getCode() === 230) {
            return true;
        }

        if ($this->response->getCode() === 331) {
            $this->send("PASS $password");
            $this->receive();

            if ($this->response->hasCode(202, 230)) { // TODO 202 code
                return true;
            }

            return !ErrorTrigger::raise($this->response->getMessage());
        }

        return !ErrorTrigger::raise($this->response->getMessage());
    }

    /**
     * Opens the data connection stream.
     *
     * @param bool $passive [optional] Specifies weather to use a passive or active data connection.
     *
     * @return bool
     */
    public function openDataConnection($passive = false)
    {
        $this->dataStream = new DataStream($this->logger, $this->commandStream, $passive);
        return $this->dataStream->open();
    }

    /**
     * Sets the transfer type for the next transfer operation.
     *
     * @param string     $type        The transfer type can be either {@link FtpBridge::TR_TYPE_BINARY} or {@link FtpBridge::TR_TYPE_ASCII} 
     *                                or {@link FtpBridge::TR_TYPE_EBCDIC}.
     * @param string|int $secondParam Specifies how the text should be interpreted for the file types {@link FtpBridge::TR_TYPE_ASCII} 
     *                                and {@link FtpBridge::TR_TYPE_EBCDIC}, it can be either {@link FtpBridge::TR_TYPE_NON_PRINT},
     *                                {@link FtpBridge::TR_TYPE_TELNET} or {@link TR_TYPE_CONTROL}.
     *                                For the {@link FtpBridge::TR_TYPE_LOCAL} an integer must be specified to specify the 
     *                                number of bits per byte on the local system.
     *                     
     * @return bool
     */
    public function setTransferType($type, $secondParam = null)
    {
        $this->send(sprintf("TYPE %s%s", $type, $secondParam ? " $secondParam" : ''));
        $this->receive();

        return $this->response->getCode() === 200;
    }
}