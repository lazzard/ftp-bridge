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

/**
 * FtpBridge class
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
class FtpBridge implements FtpBridgeInterface
{
    const CRLF = "\r\n";

    /**
     * Transfers modes
     */
    const ASCII  = 'A';
    const BINARY = 'I';
    const EBCDIC = 'E';

    /** @var FtpLoggerInterface */
    public $logger;

    /** @var resource */
    protected $commandStream;

    /** @var resource */
    protected $dataStream;

    /** @var array */
    protected $response;

    /** @var int */
    protected $responseCode;

    /** @var string */
    protected $responseMessage;

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
     * Opens a command stream connection on active port 21 (default) and logs with the provided username and password.
     *
     * @param string $host
     * @param string $username
     * @param string $password
     * @param int    $port
     *
     * @throws \RuntimeException
     */
    public function connect($host, $username, $password, $port = 21)
    {
        if ( ! ($this->commandStream = fsockopen($host, $port, $errno, $errMsg))) {
            throw new \RuntimeException("Opening socket connection was failed : [{$errMsg}]");
        }

        $this->getCmd(); // welcome message

        stream_set_blocking($this->commandStream, true); // Switch to blocking mode.
        stream_set_timeout($this->commandStream, 90); // Setting the default timeout for the control channel.

        // login.
        $this->putCmd('USER ' . $username);
        $this->getCmd();

        $this->putCmd('PASS ' . $password);
        $this->getCmd();
    }

    /**
     * @inheritDoc
     */
    public function getDataStream()
    {
        return $this->dataStream;
    }

    /**
     * @inheritDoc
     */
    public function getCommandStream()
    {
        return $this->commandStream;
    }

    /**
     * @inheritDoc
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @inheritDoc
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * @inheritDoc
     */
    public function getResponseMessage()
    {
        return $this->responseMessage;
    }

    /**
     * @inheritDoc
     */
    public function isSuccess()
    {
        return $this->responseCode <= 257;
    }

    /**
     * @inheritDoc
     */
    public function getCmd()
    {
        $response = '';
        while (true) {
            $response .= fgets($this->commandStream);
            // TODO consider to replace this condition
            if (@fseek($this->commandStream, ftell($this->commandStream) + 1)) {
                break;
            }
        }

        $this->logger->addLog($response);

        $this->response        = $response = $this->responseToArray($response);;
        $this->responseCode    = $this->responseCode = intval(substr(@$this->response[0], 0, 3));
        $this->responseMessage = $this->responseMessage = intval(substr(@$this->response[0], 0, 3));

        return $response;
    }

    /**
     * @inheritDoc
     */
    public function putCmd($command)
    {
        fputs($this->commandStream, trim($command) . self::CRLF);
    }

    /**
     * @inheritDoc
     */
    public function getData()
    {
        $res = '';
        // TODO feof hang problem
        while ( ! feof($this->dataStream)) {
            $res .= fgets($this->dataStream);
        }

        $r = $this->responseToArray($res);
        $this->addLog($r);

        return $r;
    }

    /**
     * @inheritDoc
     */
    public function setTransferType($type = self::BINARY)
    {
        $this->putCmd('TYPE ' . $type);
        $this->getCmd();
    }

    /**
     * @inheritDoc
     */
    public function openPassiveConnection()
    {
        $this->putCmd('PASV');

        $res = $this->getCmd()[0];

        // TODO use regex instead of string functions
        $ip_port = substr(substr($res, strpos($res, '(') + 1), 0, -2);
        $ip      = str_replace(',', '.', implode(',', array_slice(explode(',', $ip_port), 0, 4)));
        $port    = array_slice(explode(',', $ip_port), 4, 6);
        $port    = ($port[0] * 256) + $port[1];

        if ( ! ($this->dataStream = fsockopen($ip, $port, $errno, $errMsg))) {
            throw new \RuntimeException("Opening data connection stream was failed. [{$errMsg}]");
        }

        stream_set_blocking($this->dataStream, true); // Switch to blocking mode.
        stream_set_timeout($this->dataStream, 90); // Setting the default timeout for data channel.
    }

    /**
     * Convert the response lines to an array
     *
     * @param $response
     *
     * @return array
     */
    protected function responseToArray($response)
    {
        $response = explode(self::CRLF, $response);
        array_pop($response);

        return $response;
    }

    /**
     * @param array $response
     */
    protected function addLog($response)
    {
        foreach ($response as $log) {
            $this->logs[] = $log;
        }
    }
}