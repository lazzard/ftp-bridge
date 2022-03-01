[![Version](https://img.shields.io/github/v/release/lazzard/ftp-bridge?include_prereleases)](https://packagist.org/packages/lazzard/ftp-bridge)
[![Minimum PHP version](https://img.shields.io/badge/php-%3E%3D5.3.0-blue)](https://packagist.org/packages/lazzard/ftp-bridge)
[![tests](https://github.com/lazzard/ftp-bridge/actions/workflows/tests.yml/badge.svg)](https://github.com/lazzard/ftp-bridge/actions/workflows/tests.yml)
[![codecov](https://img.shields.io/codecov/c/github/lazzard/ftp-bridge)](https://codecov.io/gh/lazzard/ftp-bridge)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/quality/g/lazzard/ftp-bridge/master)](https://scrutinizer-ci.com/g/lazzard/ftp-bridge/?branch=master)
[![LICENSE](https://img.shields.io/packagist/l/lazzard/ftp-bridge)](https://packagist.org/packages/lazzard/ftp-bridge)

# Lazzard/FtpBridge

Allows free communication with FTP servers according to RFC959 specification and others related RFC extensions.

## Getting started

```console
composer require lazzard/ftp-bridge:v1.0.0-RC2
```

### Usage Example

 ```php
<?php

require __DIR__ . "/vendor/autoload.php";

use Lazzard\FtpBridge\Logger\ArrayLogger;
use Lazzard\FtpBridge\Logger\LogLevel;
use Lazzard\FtpBridge\FtpBridge;

try {
    // Logger is optional
    $logger = new ArrayLogger;

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
    
} catch (Exception $ex) {
    print_r($ex->getMessage();
}
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
