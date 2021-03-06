<?

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

use Lazzard\FtpBridge\Logger\FtpLoggerInterface;
use Lazzard\FtpBridge\Response\FtpResponse;
use Lazzard\FtpBridge\Stream\CommandStream;
use Lazzard\FtpBridge\Stream\DataStream;

/**
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
class FtpBridge
{
    /**
     * Transfers modes.
     */
    const ASCII  = 'A';
    const BINARY = 'I';
    const EBCDIC = 'E';

    /** @var FtpLoggerInterface */
    public $logger;

    /** @var CommandStream */
    public $commandStream;

    /** @var DataStream */
    public $dataStream;

    /**
     * FtpBridge constructor
     *
     * @param FtpLoggerInterface|null $logger
     */
    public function __construct(FtpLoggerInterface $logger = null)
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
        return $this->commandStream->send($command);
    }

    /**
     * Receives and gets the response from the command stream.
     *
     * @return FtpResponse
     */
    public function receive()
    {
        return new FtpResponse($this->commandStream->receive());
    }

    /**
     * Receives and reads the data from the data stream.
     *
     * @return string
     */
    public function receiveData()
    {
        return $this->dataStream->receive();
    }

    /**
     * Opens a command stream connection.
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
     * @return bool Returns true on success, false on failure and an E_WARNING error raised.
     */
    public function login($username, $password)
    {
        $this->send(sprintf("USER %s", $username));
        $response = $this->receive();

        if (in_array($response->getCode(), array(230))) {
            return true;
        }

        if (in_array($response->getCode(), array(331))) {
            $this->send(sprintf('PASS %s', $password));
            if (in_array($this->receive()->getCode(), array(202, 230))) { // TODO 202 code
                return true;
            }
            return !trigger_error(sprintf("PASS command failed : %s", $response->getMessage()), E_USER_WARNING);
        }
        return !trigger_error(sprintf("USER command failed : %s", $response->getMessage()), E_USER_WARNING);
    }

    /**
     * Opens the data connection.
     *
     * @param bool $passive [optional] Specifies weather to use a passive or active data connection.
     *
     * @return bool Returns true on success, false on failure and an E_WARNING error raised.
     */
    public function openDataConnection($passive = false)
    {
        $this->dataStream = new DataStream($this->logger, $this->commandStream, $passive);
        return $this->dataStream->open();
    }

    /**
     * Sets the transfer type for the next transfer operation.
     *
     * @param string $type The transfer type can be either {@link FtpBridge::BINARY} or {@link FtpBridge::ASCII} or
     *                     {@link FtpBridge::EBCDIC}.
     *
     * @return void
     */
    public function setTransferType($type)
    {
        $this->send('TYPE ' . $type);
        $this->receive();
    }
}