<?php

namespace Lazzard\FtpBridge\Tests\Unit\Stream;

use DG\BypassFinals;
use Lazzard\FtpBridge\FtpBridge;
use Lazzard\FtpBridge\Logger\Logger;
use Lazzard\FtpBridge\Stream\CommandStream;
use Lazzard\FtpBridge\Util\StreamWrapper;
use PHPUnit\Framework\TestCase;

class CommandStreamTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        BypassFinals::enable();
    }

    public function testReadWithSingleLineReply()
    {
        $reply = "227 Entering Passive Mode (192,168,1,9,139,252).";

        $wrapper = $this->getMockBuilder(StreamWrapper::class)
            ->getMock();

        $wrapper->expects($this->once())
            ->method('fgets')
            ->willReturn($reply);

        $stream = $this->getMockBuilder(CommandStream::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['log'])
            ->getMock();

        $stream->expects($this->once())
            ->method('log')
            ->with($reply);

        $stream->streamWrapper = $wrapper;

        $this->assertSame($reply, $stream->read());
    }

    public function testReadWithMultilineReply()
    {
        $crlf = FtpBridge::CRLF;
        $reply = "214-The following commands are recognized (* =>'s unimplemented):{$crlf}" .
            "CWD     XCWD    CDUP    XCUP    SMNT*   QUIT    PORT    PASV{$crlf}" .
            "EPRT    EPSV    ALLO*   RNFR    RNTO    DELE    MDTM    RMD{$crlf}" .
            "XRMD    MKD     XMKD    PWD     XPWD    SIZE    SYST    HELP{$crlf}" .
            "NOOP    FEAT    OPTS    HOST    CLNT    AUTH    CCC*    CONF*{$crlf}" .
            "ENC*    MIC*    PBSZ    PROT    TYPE    STRU    MODE    RETR{$crlf}" .
            "STOR    STOU    APPE    REST    ABOR    USER    PASS    ACCT*{$crlf}" .
            "REIN*   LIST    NLST    STAT    SITE    MLSD    MLST{$crlf}" .
            "214 Direct comments to root@localhost{$crlf}";

        $wrapper = $this->getMockBuilder(StreamWrapper::class)
            ->getMock();

        $wrapper->expects($this->exactly(9))
            ->method("fgets")
            ->willReturnOnConsecutiveCalls(
                "214-The following commands are recognized (* =>'s unimplemented):{$crlf}",
                "CWD     XCWD    CDUP    XCUP    SMNT*   QUIT    PORT    PASV{$crlf}",
                "EPRT    EPSV    ALLO*   RNFR    RNTO    DELE    MDTM    RMD{$crlf}",
                "XRMD    MKD     XMKD    PWD     XPWD    SIZE    SYST    HELP{$crlf}",
                "NOOP    FEAT    OPTS    HOST    CLNT    AUTH    CCC*    CONF*{$crlf}",
                "ENC*    MIC*    PBSZ    PROT    TYPE    STRU    MODE    RETR{$crlf}",
                "STOR    STOU    APPE    REST    ABOR    USER    PASS    ACCT*{$crlf}",
                "REIN*   LIST    NLST    STAT    SITE    MLSD    MLST{$crlf}",
                "214 Direct comments to root@localhost{$crlf}"
            );

        $stream = $this->getMockBuilder(CommandStream::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['log'])
            ->getMock();

        $stream->expects($this->once())
            ->method('log')
            ->with($reply);

        $stream->streamWrapper = $wrapper;

        $this->assertSame($reply, $stream->read());
    }

    public function testReadReturnsEmpty()
    {
        $wrapper = $this->getMockBuilder(StreamWrapper::class)
            ->getMock();

        $wrapper->expects($this->once())
            ->method('fgets')
            ->willReturn(false);

        $stream = $this->getMockBuilder(CommandStream::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['log'])
            ->getMock();

        $stream->expects($this->once())
            ->method('log');

        $stream->streamWrapper = $wrapper;

        $this->assertEmpty($stream->read());
    }

    public function testOpenReturnsTrue()
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $host = "foo.bar.com";
        $port = 21;
        $timeout = 90;
        $blocking = true;

        $stream = $this->getMockBuilder(CommandStream::class)
            ->setConstructorArgs([$host, $port, $timeout, $blocking, new StreamWrapper, $logger])
            ->onlyMethods(['openSocketConnection', 'read'])
            ->getMock();

        $stream->expects($this->once())
            ->method('openSocketConnection')
            ->with($host, $port, $timeout, $blocking)
            ->willReturn(true);

        $stream->expects($this->once())
            ->method('read');

        $this->assertTrue($stream->open());
    }

    public function testOpenReturnsFalse()
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $host = "foo.bar.com";
        $port = 21;
        $timeout = 90;
        $blocking = true;

        $stream = $this->getMockBuilder(CommandStream::class)
            ->setConstructorArgs([$host, $port, $timeout, $blocking, new StreamWrapper, $logger])
            ->onlyMethods(['openSocketConnection', 'read'])
            ->getMock();

        $stream->expects($this->once())
            ->method('openSocketConnection')
            ->with($host, $port, $timeout, $blocking)
            ->willReturn(false);

        $stream->expects($this->never())
            ->method('read');

        $this->assertFalse($stream->open());
    }
}
