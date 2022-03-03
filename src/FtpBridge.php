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
use Lazzard\FtpBridge\Exception\CommandStreamException;
use Lazzard\FtpBridge\Exception\ResponseException;
use Lazzard\FtpBridge\Exception\PassiveDataStreamException;
use Lazzard\FtpBridge\Logger\LoggerInterface;
use Lazzard\FtpBridge\Response\Response;
use Lazzard\FtpBridge\Stream\ActiveDataStream;
use Lazzard\FtpBridge\Stream\CommandStream;
use Lazzard\FtpBridge\Stream\DataStream;
use Lazzard\FtpBridge\Stream\PassiveDataStream;
use Lazzard\FtpBridge\Util\StreamWrapper;

/**
 * Provides methods to interact with FTP servers.
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
class FtpBridge
{
    /**
     * @link https://tools.ietf.org/html/rfc959#section-4 (4.2. FTP REPLIES)
     * @var string
     *
     * The Carriage return and line feed represents an end of line of an FTP reply/command.
     *
     */
    const CRLF = "\r\n";

    /**
     * Transfer type representations.
     */
    const TR_TYPE_ASCII = 'A';
    const TR_TYPE_BINARY = 'I';
    const TR_TYPE_EBCDIC = 'E';
    const TR_TYPE_LOCAL = 'L';
    const TR_TYPE_NON_PRINT = 'N';
    const TR_TYPE_TELNET = 'T';
    const TR_TYPE_CR_CONTROL = 'C';

    /** @var LoggerInterface */
    protected $logger;

    /** @var Response */
    protected $response;

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
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param Response $response
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    /**
     * @return CommandStream
     */
    public function getCommandStream()
    {
        return $this->commandStream;
    }

    /**
     * @param CommandStream $commandStream
     */
    public function setCommandStream($commandStream)
    {
        $this->commandStream = $commandStream;
    }

    /**
     * @return DataStream
     */
    public function getDataStream()
    {
        return $this->dataStream;
    }

    /**
     * @param DataStream $dataStream
     */
    public function setDataStream($dataStream)
    {
        $this->dataStream = $dataStream;
    }

    /**
     * Sends a command to the server thought the control channel.
     *
     * @param string $command
     *
     * @return bool
     *
     * @throws CommandStreamException
     */
    public function send($command)
    {
        if (!$this->commandStream->write($command)) {
            throw new CommandStreamException("Unable to send the command '\"$command\"' " .
                'through the control channel.');
        }

        return true;
    }

    /**
     * Writes the provided string content to the data stream.
     *
     * @param string $string
     *
     * @return bool
     *
     * @throws ActiveDataStreamException|PassiveDataStreamException
     */
    public function write($string)
    {
        if (!$this->dataStream->write($string)) {
            if ($this->dataStream instanceof ActiveDataStream) {
                throw new ActiveDataStreamException(
                    'Failed to write data to data stream using the active mode.');
            }

            if ($this->dataStream instanceof PassiveDataStream) {
                throw new PassiveDataStreamException(
                    'Failed to write data to data stream using the passive mode.');
            }
        }

        return true;
    }

    /**
     * Receives and gets the response from the command stream.
     *
     * @return Response Returns a {@see Response} object in success, an exception thrown otherwise.
     *
     * @throws CommandStreamException
     */
    public function receive()
    {
        if (($raw = $this->commandStream->read()) === false) {
            throw new CommandStreamException('Failed to retrieve data from the command stream.');
        }

        return $this->response = new Response($raw);
    }

    /**
     * Receives and reads the data from the data stream.
     *
     * @return string|bool
     *
     * @throws ActiveDataStreamException|PassiveDataStreamException
     */
    public function receiveData()
    {
        if (($data = $this->dataStream->read()) === false) {
            if ($this->dataStream instanceof ActiveDataStream) {
                throw new ActiveDataStreamException(
                    'Failed to retrieve data from the data stream using the active mode.');
            }

            if ($this->dataStream instanceof PassiveDataStream) {
                throw new PassiveDataStreamException(
                    'Failed to retrieve data from the data channel using the passive mode.');
            }
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
     * @return bool
     *
     * @throws CommandStreamException
     */
    public function connect($host, $port = 21, $timeout = 90, $blocking = true)
    {
        $this->commandStream = new CommandStream($host, $port, $timeout, $blocking, $this->streamWrapper, $this->logger);

        if (!$this->commandStream->open()) {
            $lastError = error_get_last();
            throw new CommandStreamException(sprintf(
                "Failed to establish a successful connection in the control channel to the (%s) : %s",
                $host,
                $lastError['message']
            ));
        }

        return true;
    }

    /**
     * Opens a passive data connection.
     *
     * @return bool
     *
     * @throws PassiveDataStreamException|ResponseException
     */
    public function openPassive()
    {
        $this->dataStream = new PassiveDataStream($this->commandStream, $this->streamWrapper, $this->logger);

        if (!$this->dataStream->open()) {
            $lastError = error_get_last();
            throw new PassiveDataStreamException(sprintf(
                "Failed to establish a successful passive data connection to the (%s) : %s",
                $this->commandStream->host,
                $lastError['message']
            ));
        }

        return true;
    }

    /**
     * Opens an active data connection to the FTP server.
     *
     * @return bool
     *
     * @throws ActiveDataStreamException|ResponseException
     */
    public function openActive()
    {
        $this->dataStream = new ActiveDataStream($this->commandStream, $this->streamWrapper, $this->logger);

        if (!$this->dataStream->open()) {
            $lastError = error_get_last();
            throw new ActiveDataStreamException(sprintf(
                "Failed to establish a successful active data connection to the (%s) : %s",
                $this->commandStream->host,
                $lastError['message']
            ));
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
     * @return bool
     *
     * @throws CommandStreamException|ResponseException
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

            throw new ResponseException($this->response->getMessage());
        }

        throw new ResponseException($this->response->getMessage());
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
     * @throws CommandStreamException|ResponseException
     */
    public function setTransferType($type, $secondParam = "")
    {
        $this->send(sprintf("TYPE %s%s", $type, $secondParam ? " $secondParam" : ''));

        if ($this->receive()->getCode() !== 200) {
            throw new ResponseException($this->response->getMessage());
        }

        return true;
    }
}