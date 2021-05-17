# Lazzard/FtpBridge

[![Version](https://img.shields.io/github/v/release/lazzard/ftp-bridge?include_prereleases&style=flat-square)](https://packagist.org/packages/lazzard/ftp-bridge)
[![Minimum PHP version](https://img.shields.io/badge/php-%3E%3D5.3.0-blue?style=flat-square)](https://packagist.org/packages/lazzard/ftp-bridge)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/lazzard/ftp-bridge/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/lazzard/ftp-bridge/?branch=master)
[![LICENSE](https://img.shields.io/packagist/l/lazzard/ftp-bridge?style=flat-square)](https://packagist.org/packages/lazzard/ftp-bridge)

A low-level implementation library of the File Transfer Protocol (FTP) in PHP that follows the RFC 959 standards and others related RFC extensions.
  
> This library can be used to communicate with FTP servers, so you can send any FTP commands you want and receive data from the server very simply without writing the sockets/streams logic on yourself, in addition to that, the library provides a logging system to keep track of your FTP sessions.  

## Requirements

* PHP version >= 5.3.0

## Installation

This library is available via composer.

**method 1 :**

Require the exact version directly: 

```console
composer require lazzard/ftp-bridge:v1.0.0-RC1
```

**method 2 :**

 Add this two lines in your `composer.json` file :

```json
"minimum-stability": "dev",
"prefer-stable": true
```

Then require the package :

```console
composer require lazzard/ftp-bridge
```

## Usage

 ```php
use Lazzard\FtpBridge\Logger\ArrayLogger;
use Lazzard\FtpBridge\Logger\LogLevel;
use Lazzard\FtpBridge\FtpBridge;

require dirname(__DIR__) . "/vendor/autoload.php";

// Logger is optional
$logger = new ArrayLogger();

// set log levels prefixes
LogLevel::setInfo('<--');
LogLevel::setError('<--');
LogLevel::setCommand('-->');

// create bridge instance
$ftp = new FtpBridge($logger);

$hostname = 'foo@bar.com';
$username = 'username';
$password = 'password';

if ($ftp->connect($hostname, 21)) {
    // connected
    if ($ftp->login($username, $password)) {
        // logged

        $ftp->send("PWD");
        $ftp->receive();

        // open a passive data connection
        if ($ftp->openPassive()) {
            $ftp->send("NLST .");
            $ftp->receive();
    
            $ftp->receiveData();
            $ftp->receive();
        }
    }

    $ftp->send("QUIT");
    $ftp->receive();
}

print_r($logger->getLogs());
 ```

**Result :** 

```
Array
(
    [0] => <-- 220---------- Welcome to Pure-FTPd [privsep] [TLS] ----------
220-You are user number 399 of 6900 allowed.
220-Local time is now 08:14. Server port: 21.
220-This is a private system - No anonymous login
220 You will be disconnected after 60 seconds of inactivity.

    [1] => --> USER username

    [2] => <-- 331 User username OK. Password required

    [3] => --> PASS password

    [4] => <-- 230-Your bandwidth usage is restricted
230 OK. Current restricted directory is /

    [5] => --> PWD

    [6] => <-- 257 "/" is your current location

    [7] => --> PASV

    [8] => <-- 227 Entering Passive Mode (185,27,134,11,205,216)

    [9] => --> NLST .

    [10] => <-- 150 Accepted data connection

    [11] => <-- .
..
.override
DO NOT UPLOAD FILES HERE
htdocs
lazzard.org

    [12] => <-- 226-Options: -a
226 6 matches total

    [13] => --> QUIT

    [14] => <-- 221-Goodbye. You uploaded 0 and downloaded 0 kbytes.
221 Logout.

)
```	