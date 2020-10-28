# Lazzard/FtpBridge

[![Packagist Version (including pre-releases)](https://img.shields.io/packagist/v/lazzard/php-ftp-bridge?color=orange&include_prereleases)](https://packagist.org/packages/lazzard/php-ftp-bridge)
[![Minimum PHP version](https://img.shields.io/packagist/php-v/lazzard/php-ftp-bridge)](https://packagist.org/packages/lazzard/php-ftp-bridge)

**Lazzard/FtpBridge** is a low-level implementation library of the File Transfer Protocol (FTP) in PHP, it can be used to implement an FTP functions without the need for the FTP extension, and it provides the possibility of logging the entire FTP session.

***Note :** Until a stable version released, please do not use this library for production.*

# Contents

- [Introduction](#introduction)
- [Contents](#contents)
- [Requirements](#requirements)
- [Installation](#installation)
- [FtpBridge](#ftpbridge)
    + [FtpBridge methods](#ftpbridge-methods)
    + [FtpBridge already implemented FTP functions](#ftpbridge-already-implemented-ftp-functions)
- [FtpResponse](#ftpresponse)
- [Logger [optional]](#logger-optional)
  * [Logger modes](#logger-modes)
  * [FtpFileLogger](#ftpfilelogger)
  * [FtpArrayLogger](#ftparraylogger)
- [Usage](#usage)
- [Implementation examples](#implementation-examples)
  * [Implementing an FTP function that's depends on an Arbitrary FTP command](#implementing-an-ftp-function-thats-depends-on-an-arbitrary-ftp-command)
  * [Implementing an FTP function that's depends on an FTP Directory listing command](#implementing-an-ftp-function-thats-depends-on-an-ftp-directory-listing-command)
  * [Implementing an FTP function that's depends on an FTP File transfer command](#implementing-an-ftp-function-thats-depends-on-an-ftp-file-transfer-command)

# Requirements

* PHP version >= 5.3.0

# Installation

This library is available via composer.

First add this two lines in your composer.json file:

```json
"minimum-stability": "dev",
"prefer-stable": true,
```

Then require the package:

```console
composer require lazzard/php-ftp-bridge@dev
```

# FtpBridge

`FtpBridge` holds all the necessary methods to start communicating with the remote server, in addition to other useful utility methods. 

**Syntax :** 

```php
<?php
    $ftp = new FtpBridge();
```

### FtpBridgeInterface methods

| Method                 | Description      
| ---------------------- |:---------------  
| `send($command)`       | Sends an FTP command through the control stream.
| `receive()`            | Receives the FTP reply, must be called after the `send($command)` method.
| `receiveData()`        | Receives and gets the data from the data stream, must be called after sending a **directory listing** or a **transfer** FTP command.
| `setTransferMode($mode)` | Sets the transfer mode for all FTP transfer operations.  


### FtpBridge already implemented FTP functions

`FtpBridge` provides already functions for creating an FTP connection and logging to the remote server and some other basics functions, if you don't have any reason (special requirements) to implement those functions in yourself then why to reinvent the wheel, you can use the following functions provided by **FtpBridgeInterface**.

| Method                         | PHP alternative  | PHP Min version      | FtpBridge Min version     |
| ----------------------         |:----------------:|:--------------------:|:-------------------------:|
| `connect()`                    | `ftp_connect()`  | 4.0.0                | 5.3.0                     |
| `login()`                      | `ftp_login()`    | 4.0.0                | 5.3.0                     |
| `openDataConnection($passive)` | `ftp_pasv()`     | 4.0.0                | 5.3.0                     |

# FtpResponse

`FtpResponse` normalizes FTP responses.

**Syntax :** 

```php
<?php
    $response = new FtpResponse($response);
```

**Example of usage**

```php
<?php
    $ftp = new FtpBridge();
    
    $ftp->send('NOOP');
    
    $response = new FtpResponse($ftp->receive);

    echo $response->getCode(); // 200
    echo $response->getMessage(); // Zzz...
```

**FtpResponseInterface methods :** 

| Method                  | Description      
| ----------------------  |:---------------  
| `getResponse()`         | Gets FTP reply as a string.
| `getMessage()`          | Gets FTP readable text.
| `getCode()`             | Gets FTP reply code.
| `isMultiline()`         | Checks if the FTP reply is multiline reply.

# Logger [optional]

The logger interface provides a simple methods to add and manage the FTP replies logs.

| Method                  | Description      
| ----------------------  |:---------------  
| `getLogs()`             | Gets logs.
| `log($level, $message)` | Logs a registry, the log levels provided by `FtpLogLevel`.
| `clear()`               | Clears the logs.
| `count()`               | Gets the logs count.

## Logger modes

`FtpLoggerInterface` provides the following logging modes.

* **ARRAY_MODE** : logging each line of the FTP reply as one registry.
* **PLAIN_MODE** : logging the entire FTP reply as one registry.

## FtpFileLogger

`FtpFileLogger` logs the replies to a regular file.


**Example :** 

```php
<?php
    $logger = new FtpFileLogger(FtpLoggerInterface::PLAIN_MODE, 'ftp.txt');
```

## FtpArrayLogger

`FtpArrayLogger` logs the FTP replies to an array.

**Example :** 

```php
<?php
    $logger = new FtpArrayLogger(FtpLoggerInterface::ARRAY_MODE);
```

# Usage

```php
    $logger = new ArrayLogger(ArrayLogger::ARRAY_MODE); // initialize the logger (Optional)
    
    $ftp = new FtpBridge($logger); // initialize the FtpBridge
    
    $ftp->connect("foo@bar.com"); 
    $ftp->login("user", "pass");
    
    $ftp->send('HELP');
    
    $response = new FtpResponse($ftp->receive());
    
    var_dump($ftp->getMessage()); // dump the ftp reply message
    var_dump($ftp->getCode()); // dump the ftp reply code
    
    var_dump($logger->getLogs()); // Shows the entire session negotiation 
```

# Implementation examples

## Implementing an FTP function that's depends on an Arbitrary FTP command

Let's implement our **help()** function :

**Note!** This is just a simple example, you can improve this function a lot.

```php
/**
 * @param FtpBridgeInterface $ftp
 *
 * @return array|bool
 */
function help($ftp)
{
    $ftp->send('HELP');

    $response = new FtpResponse($ftp->receive());
    if (in_array($response->getCode(), [211, 214])) {
        return explode("\r\n", $response->getResponse());
    }

    return false;
}
```

## Implementing an FTP function that's depends on an FTP directory listing command

```php
/**
 * @param FtpBridgeInterface $ftp
 * @param string $directory
 *
 * @return array|bool
 */
function filesList($ftp, $directory)
{
    // open a passive data connection
    if (!@$ftp->openDataConnection(true)) {
        throw new \RuntimeException("Unable to open the FTP data connection.");
    }

    $ftp->send(sprintf("NLST %s", $directory));

    if (in_array((new FtpResponse($ftp->receive()))->getCode(), [150, 125])) {

        /**
         * Note! This is an optional step, it basically useful for logging the next FTP
         * reply concerning this transfer, if you don't care about logs you can start working
         * with the data here without the following test.
         *
         * After the data was sent, the server sends a reply code '226' or '250' to
         * indicate the state of data channel (closed or still opened) and
         * the state of the transfer.
         *
         * @link https://tools.ietf.org/html/rfc959#section-5
         */
        if (in_array((new FtpResponse($ftp->receive()))->getCode(), [226, 250])) {
            return $ftp->receiveData();
        }
    }

    return false;
}
```

## Implementing an FTP function that's depends on an FTP file transfer command

```php
/**
 * @param FtpBridgeInterface $ftp
 * @param string $remoteFile
 * @param string $localFile
 * @param string $transferType
 *
 * @return bool
 */
function download($ftp, $remoteFile, $localFile, $transferType = FtpBridge::BINARY)
{
    $ftp->setTransferType($transferType);

    // open a passive data connection
    if (!@$ftp->openDataConnection(true)) {
        throw new \RuntimeException("Unable to open the FTP data connection.");
    }

    $ftp->send(sprintf("RETR %s", $remoteFile));

    if (in_array((new FtpResponse($ftp->receive()))->getCode(), [125, 150])) {
        if (in_array((new FtpResponse($ftp->receive()))->getCode(), [226, 250])) {
            $handle = fopen($localFile, 'wb');

            while (!feof($ftp->dataStream->stream)) {
                fwrite($handle, fread($ftp->dataStream->stream, 8192));
            }

            fclose($handle);
            return true;
        }
    }


    return false;
}
```
