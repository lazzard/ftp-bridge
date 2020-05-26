# Introduction

**Lazzard/FtpBridge** Provides a low-level implementation to the FTP in php, it can be used to implement a specific and adjusted FTP functions, it provides also the possibility to logging the entire FTP session.

# FtpBridge 

Syntax : 

```php
<?php
    $ftp = new Lazzard\FtpBridge\FtpBridge(Lazzard\FtpBridge\Logger\FtpLoggerInterface $logger);
```

## FtpBridgeInterface methods

| Method                 | Description      
| ---------------------- |:---------------  
| `send($command)`       | Sends an FTP command through the control stream.
| `receive()`            | Receive the FTP reply, must be called after the `send($command)` method.
| `receiveData()`        | Receive and gets the data from the data stream, must be called after sending a **directory listing** or a **transfer** FTP command.


## FtpBridgeInterface already implemented FTP functions

Those functions already implemented for you, its provided in the **FtpBridgeInterface**.

| Method                         | PHP alternative  | PHP Min version      | FtpBridge Min version     |
| ----------------------         |:----------------:|:--------------------:|:-------------------------:|
| `connect()`                    | `ftp_connect()`  | 4.0.0                | 5.3.0                     |
| `login()`                      | `ftp_login()`    | 4.0.0                | 5.3.0                     |
| `openDataConnection($passive)` | `ftp_pasv()`     | 4.0.0                | 5.3.0                     |
| `setTransferMode`              | N/A              | N/A                  | N/A                       |

# Logger [optional]

The logger interface provides a simple methods to add and manage the FTP replies logs.

| Method                  | Description      
| ----------------------  |:---------------  
| `getLogs()`             | Gets logs.
| `log($level, $message)` | Logs a registry, the log levels provided in the `Lazzard\FtpBridge\Logger\FtpLogLevel`.
| `clear()`               | Clears the logs.
| `count()`               | Gets the logs count.


## Logger modes

* **ARRAY_MODE** : logging each line of the FTP reply as one registry.
* **PLAIN_MODE** : logging the entire FTP reply as one registry.

## FileLogger

`FileLogger` logs the replies in a file.

Syntax : 

```php
<?php
    new FileLogger($mode, $filePath, $append = false);
```

Example : 

```php
<?php
    $logger = new FileLogger(FileLogger::ArrayLogger, 'ftp.txt');
```

## ArrayLogger

`ArrayLogger` logs the FTP replies in array.

Syntax : 

```php
<?php
    new ArrayLogger($mode, $filePath, $append = false);
```

Example : 

```php
<?php
    $logger = new ArrayLogger(ArrayLogger::ArrayLogger, 'ftp.txt');
```
# Usage

## Initialize the FtpBridge and connect to the server.

```php
    $logger = new ArrayLogger(ArrayLogger::PLAIN_MODE);
    
    $ftp = new FtpBridge($logger);
    
    $ftp->connect("foo@bar.com");
    $ftp->login("user", "pass");
```

## Implementing an FTP function that's depends on an **arbitrary FTP command**.

Let's implement our **help()** function :

**Note!** This is just a simple example, you can improve this function a lot.

```php
<?php
    /**
    * @return array|bool
    */
    function help()
    {
        $ftp->send('HELP');
    
        $response = new FtpResponse($ftp->receive());
    
        if (in_array($response->getCode(), [211, 214])) {
            return explode("\r\n", $response->getReply());
        }
    
        return false;
    }
```

## Implementing an FTP function that's depends on an FTP **directory listing command**.

```php
<?php
    /**
    * @param string $directory 
    * 
    * @return array|bool
    */
    function filesList($directory)
    {
        // open a passive data connection
        $ftp->openDataConnection(true);
        
        $ftp->send(sprintf("NLST %s", $directory));
        
        $response = new FtpResponse($ftp->receive());
        
        if (in_array($response->getCode(), [150, 125])) {

            /**
            * After the data was sent, the server sends a reply 226 or 250 to
            * indicates that state of data channel and the state of transfer.
            * 
            * @link https://tools.ietf.org/html/rfc959#section-5
            */
            $response = new FtpResponse($ftp->receive());
            if (in_array($response->getCode(), [226, 250])) {
                return $ftp->receiveData();
            } 
        }
        
        return false;
    }
```

