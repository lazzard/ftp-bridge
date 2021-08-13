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

use Lazzard\FtpBridge\Exception\ActiveDataStreamException;
use Lazzard\FtpBridge\Exception\FtpBridgeException;
use Lazzard\FtpBridge\Exception\PassiveDataStreamException;
use Lazzard\FtpBridge\Exception\StreamException;
use Lazzard\FtpBridge\Logger\LoggerInterface;
use Lazzard\FtpBridge\Response\Response;
use Lazzard\FtpBridge\Stream\ActiveDataStream;
use Lazzard\FtpBridge\Stream\CommandStream;
use Lazzard\FtpBridge\Stream\DataStream;
use Lazzard\FtpBridge\Stream\PassiveDataStream;

/**
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
class FtpBridge
{
    /**
     * @var string 
     * 
     * The Carriage return and line feed represents an end of line of an FTP reply/command.
     * 
     * @link https://tools.ietf.org/html/rfc959#section-4 (4.2. FTP REPLIES)
     */
    const CRLF = "\r\n";
    
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
     * @param CommandStream $stream
     */
    public function setCommandStream(CommandStream $stream)
    {
        $this->commandStream = $stream;
    }

    /**
     * @param DataStream $stream
     */
    public function setDataStream(DataStream $stream)
    {
        $this->dataStream = $stream;
    }

    /**
     * Sends a command to the server thought the control channel.
     *
     * @param string $command
     *
     * @return void
     *
     * @throws FtpBridgeException
     */
    public function send($command)
    {
        if (!$this->commandStream || !is_resource($this->commandStream->stream)) {
            throw new FtpBridgeException('The FTP connection must be established ' .
                'first before try sending any commands.');
        }

        if (!$this->commandStream->write($command)) {
            throw new FtpBridgeException("Unable to send the command \"$command\" " .
                'through the control channel.');
        }
    }

    /**
     * Writes the provided string content to the data channel.
     *
     * @param string $string
     *
     * @return void
     *
     * @throws FtpBridgeException
     */
    public function write($string)
    {
        if (!$this->dataStream || !is_resource($this->dataStream->stream)) {
            throw new FtpBridgeException('The FTP data connection must be established'
                . 'first before try writing content to the data channel.');
        }

        if (!$this->dataStream->write($string)) {
            throw new FtpBridgeException("Unable to write content to the data channel.");
        }
    }

    /**
     * Receives and gets the response from the command stream.
     *
     * @return Response Returns a {@link Response} object.
     *
     * @throws FtpBridgeException
     */
    public function receive()
    {
        if (!$this->commandStream || !is_resource($this->commandStream->stream)) {
            throw new FtpBridgeException('The FTP command connection not created yet.');
        }

        if (!$raw = $this->commandStream->read()) {
            throw new FtpBridgeException('Failed to retrieve data from the command stream.');
        }

        return $this->response = new Response($raw);
    }

    /**
     * Receives and reads the data from the data stream.
     *
     * @return string
     *
     * @throws FtpBridgeException
     */
    public function receiveData()
    {
        if (!$this->dataStream || !is_resource($this->dataStream->stream)) {
            throw new FtpBridgeException('The FTP data connection not created yet.');
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
     * @param bool   $blocking $blocking [optional] The transfer mode, the blocking mode is the default.
     *
     * @return bool Returns true on success, false on failure and an E_WARNING error raised.
     *
     * @throws StreamException
     */
    public function connect($host, $port = 21, $timeout = 90, $blocking = true)
    {
        $this->commandStream = new CommandStream($this->logger, $host, $port, $timeout, $blocking);
        return $this->commandStream->open();
    }

    /**
     * Opens a passive data connection.
     *
     * @return bool
     *
     * @throws PassiveDataStreamException|StreamException
     */
    public function openPassive()
    {
        $this->dataStream = new PassiveDataStream($this->logger, $this->commandStream);
        return $this->dataStream->open();
    }

    /**
     * Opens an active data connection to the FTP server.
     *
     * @param string $activeIpAddress            [optional] The IP address to send along with the PORT command, if omitted
     *                                           the server IP address in the $_SERVER['SERVER_ADDR'] will be used.
     *
     * @return bool
     *
     * @throws ActiveDataStreamException
     */
    public function openActive($activeIpAddress = null)
    {
        $this->dataStream = new ActiveDataStream($this->logger, $this->commandStream, $activeIpAddress);
        return $this->dataStream->open();
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
     *
     * @throws FtpBridgeException
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

            // TODO 202 code
            if ($this->response->hasCode(202, 230)) {
                return true;
            }

            throw new FtpBridgeException($this->response->getMessage());
        }

        throw new FtpBridgeException($this->response->getMessage());
    }

    /**
     * Sets the transfer type for the next transfer operation.
     *
     * @param string     $type        The transfer type can be either {@link FtpBridge2::TR_TYPE_BINARY}
     *                                or {@link FtpBridge2::TR_TYPE_ASCII} or {@link FtpBridge2::TR_TYPE_EBCDIC}.
     * @param string|int $secondParam Specifies how the text should be interpreted for the file types
     *                                {@link FtpBridge2::TR_TYPE_ASCII} and {@link FtpBridge2::TR_TYPE_EBCDIC},
     *                                it can be either {@link FtpBridge2::TR_TYPE_NON_PRINT},
     *                                {@link FtpBridge2::TR_TYPE_TELNET} or {@link TR_TYPE_CONTROL}.
     *                                For the {@link FtpBridge2::TR_TYPE_LOCAL} an integer must be specified to specify the
     *                                number of bits per byte on the local system.
     *
     * @return bool
     *
     * @throws FtpBridgeException
     */
    public function setTransferType($type, $secondParam = null)
    {
        $this->send(sprintf("TYPE %s%s", $type, $secondParam ? " $secondParam" : ''));
        $this->receive();

        return $this->response->getCode() === 200;
    }
}