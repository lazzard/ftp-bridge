<?php

namespace Lazzard\FtpBridge\Tests\Unit\Logger;

use Lazzard\FtpBridge\FtpBridge;
use Lazzard\FtpBridge\Logger\FileLogger;
use Lazzard\FtpBridge\Logger\LogLevel;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class FileLoggerTest extends LoggerTest
{

    /** @var vfsStreamDirectory */
    private $root;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = vfsStream::setup();
    }

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$logger = FileLogger::class;
    }

    public function testLog()
    {
        $file = 'logs.txt';

        vfsStream::newFile($file, 0777)
            ->at($this->root);

        $logger = new FileLogger($file);

        $this->assertNull($logger->log(LogLevel::$command, 'USER username'));
        $this->assertFileExists($file);
        $this->assertStringNotEqualsFile($file, '');
    }

    public function testGetLogs()
    {
        $file = 'logs.txt';

        vfsStream::newFile($file, 0777)
            ->at($this->root);

        $logger = new FileLogger($file);

        $this->assertEmpty($logger->getLogs());

        self::logFakeSession($logger);

        $crlf = FtpBridge::CRLF;

        $infoLvl = LogLevel::$info;
        $commandLvl = LogLevel::$command;
        $errorLvl = LogLevel::$error;

        $expected =
            "{$infoLvl} 220 FTP Server ready.{$crlf}" .
            "{$commandLvl} USER username{$crlf}" .
            "{$infoLvl} 331 Password required for username{$crlf}" .
            "{$commandLvl} PASS password{$crlf}" .
            "{$infoLvl} 230 User u852470563 logged in{$crlf}" .
            "{$commandLvl} PWD{$crlf}" .
            "{$infoLvl} 257 \"root\" is the current directory{$crlf}" .
            "{$commandLvl} NLST .{$crlf}" .
            "{$infoLvl} 150 Opening ASCII mode data connection for file list{$crlf}" .
            "{$infoLvl} file1.txt{$crlf}" .
            "file2.txt{$crlf}" .
            "file3.txt{$crlf}" .
            "{$infoLvl} 226 Transfer complete{$crlf}" .
            "{$commandLvl} UNKNOWN{$crlf}" .
            "{$errorLvl} 500 UNKNOWN not understood{$crlf}" .
            "{$commandLvl} QUIT{$crlf}" .
            "{$infoLvl} 221 Goodbye.{$crlf}";

        $this->assertSame($expected, file_get_contents($file));
    }

    public function testClear()
    {
        $file = 'logs.txt';

        vfsStream::newFile($file, 0777)
            ->at($this->root);

        $logger = new FileLogger($file);

        self::logFakeSession($logger);

        $this->assertNull($logger->clear());
        $this->assertStringEqualsFile($file, '');
    }

    public function testCount()
    {
        $file = 'logs.txt';

        vfsStream::newFile($file, 0777)
            ->at($this->root);

        $logger = new FileLogger($file);

        $this->assertSame(0, $logger->count());

        self::logFakeSession($logger);

        $this->assertSame(17, $logger->count());
    }
}
