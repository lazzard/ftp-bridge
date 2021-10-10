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
use Lazzard\FtpBridge\Exception\ActiveDataStreamException;
use Lazzard\FtpBridge\Exception\FtpBridgeException;
use Lazzard\FtpBridge\Exception\PassiveDataStreamException;
use Lazzard\FtpBridge\Logger\LoggerInterface;
use Lazzard\FtpBridge\Response\Response;
use Lazzard\FtpBridge\Stream\ActiveDataStream;
use Lazzard\FtpBridge\Stream\CommandStream;
use Lazzard\FtpBridge\Stream\DataStream;
use Lazzard\FtpBridge\Stream\PassiveDataStream;
use Lazzard\FtpBridge\Util\StreamWrapper;

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
     * @return bool
     *
     * @throws FtpBridgeException if the connection not created yet.
     */
    public function send($command)
    {
        if (!$this->commandStream || !is_resource($this->commandStream->stream)) {
            throw new FtpBridgeException('The FTP connection must be established ' .
                'first before try sending any commands.');
        }

        if (!$this->commandStream->write($command)) {
            return !ErrorTrigger::raise("Unable to send the command \"$command\" " .
                'through the control channel.');
        }

        return true;
    }

    /**
     * Writes the provided string content to the data channel.
     *
     * @param string $string
     *
     * @return bool
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
            return !ErrorTrigger::raise("Unable to write content to the data channel.");
        }

        return true;
    }

    /**
     * Receives and gets the response from the command stream.
     *
     * @return Response|false Returns a {@see Response} object in success, false otherwise.
     *
     * @throws FtpBridgeException
     */
    public function receive()
    {
        if (!$this->commandStream || !is_resource($this->commandStream->stream)) {
            throw new FtpBridgeException('The FTP command connection not created yet.');
        }

        if (!$raw = $this->commandStream->read()) {
            return !ErrorTrigger::raise('Failed to retrieve data from the control channel.');
        }

        return $this->response = new Response($raw);
    }

    /**
     * Receives and reads the data from the data stream.
     *
     * @return string|bool
     *
     * @throws FtpBridgeException
     */
    public function receiveData()
    {
        if (!$this->dataStream || !is_resource($this->dataStream->stream)) {
            throw new FtpBridgeException('The FTP data connection not created yet.');
        }

        if (!$data = $this->dataStream->read()) {
            return !ErrorTrigger::raise('Failed to retrieve data from the data channel.');
        }

        return $data;
    }

    /**
     * Opens an FTP connection.
     *
     * @param string $host     The remote host name or the IP address.
     * @param int    $port     [optional] The remote server port to connect to, if omitted the port 21 will be used.
     * @param int    $timeout  [optional] Specifies the connection timeout of all FTP transfer operations, default sets
     *                         to 90.
     * @param bool   $blocking [optional] The transfer mode, the blocking mode is the default.
     *
     * @return bool Returns true if successfully connected, false otherwise.
     */
    public function connect($host, $port = 21, $timeout = 90, $blocking = true)
    {
        $this->commandStream = new CommandStream($host,
            $port,
            $timeout,
            $blocking,
            new StreamWrapper,
            $this->logger
        );

        if (!$this->commandStream->open()) {
            $error = error_get_last();
            return !ErrorTrigger::raise(
                sprintf(
                    "Failed to establish a successful connection in the control channel to the (%s) : %s",
                    $host,
                    $error['message']
                )
            );
        }

        return true;
    }

    /**
     * Opens a passive data connection.
     *
     * @return bool
     *
     * @throws PassiveDataStreamException
     */
    public function openPassive()
    {
        $this->dataStream = new PassiveDataStream(
            $this->commandStream,
            new StreamWrapper,
            $this->logger
        );

        if (!$this->dataStream->open()) {
            $error = error_get_last();
            return !ErrorTrigger::raise(
                sprintf(
                    "Failed to establish a successful passive data connection to the (%s) : %s",
                    $this->commandStream->host,
                    $error['message']
                )
            );
        }

        return true;
    }

    /**
     * Opens an active data connection to the FTP server.
     *
     * @return bool Returns true on success, false in failure and an E_USER_WARNING error will be raised also.
     *
     * @throws ActiveDataStreamException
     */
    public function openActive()
    {
        $this->dataStream = new ActiveDataStream(
            $this->commandStream,
            new StreamWrapper,
            $this->logger
        );

        if (!$this->dataStream->open()) {
            $error = error_get_last();
            return !ErrorTrigger::raise(
                sprintf(
                    "Failed to establish a successful active data connection to the (%s) : %s",
                    $this->commandStream->host,
                    $error['message']
                )
            );
        }

        return true;
    }

    /**
     * Logs into the FTP server.
     *
     * Note: this method must be called after a successful connection.
     *
     * @param string $username
     * @param string $password
     *
     * @return bool Returns true on success, false in failure and an E_USER_WARNING error will be raised also.
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

            return !ErrorTrigger::raise($this->response->getMessage());
        }

        return !ErrorTrigger::raise($this->response->getMessage());
    }

    /**
     * Sets the transfer type for the next transfer operation.
     *
     * @param string     $type        The transfer type can be either {@see FtpBridge::TR_TYPE_BINARY}
     *                                or {@see FtpBridge::TR_TYPE_ASCII} or {@see FtpBridge::TR_TYPE_EBCDIC}.
     * @param string|int $secondParam Specifies how the text should be interpreted for the file types
     *                                {@see FtpBridge::TR_TYPE_ASCII} and {@see FtpBridge::TR_TYPE_EBCDIC},
     *                                it can be either {@see FtpBridge::TR_TYPE_NON_PRINT},
     *                                {@see FtpBridge::TR_TYPE_TELNET} or {@see TR_TYPE_CONTROL}.
     *                                For the {@see FtpBridge::TR_TYPE_LOCAL} an integer must be specified
     *                                to specify the number of bits per byte on the local system.
     *
     * @return bool
     *
     * @throws FtpBridgeException
     */
    public function setTransferType($type, $secondParam = null)
    {
        $this->send(sprintf(
            "TYPE %s%s", $type, $secondParam ? " $secondParam" : ''
        ));

        $code = $this->receive()->getCode();

        if ($code !== 200) {
            return !ErrorTrigger::raise($this->response->getMessage());
        }

        return true;
    }
}