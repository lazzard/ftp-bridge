<?php

namespace Lazzard\FtpBridge\Tests\Unit\Stream;

use Lazzard\FtpBridge\Exception\PassiveDataStreamException;
use Lazzard\FtpBridge\Exception\ResponseException;
use Lazzard\FtpBridge\Logger\Logger;
use Lazzard\FtpBridge\Stream\CommandStream;
use Lazzard\FtpBridge\Stream\PassiveDataStream;
use Lazzard\FtpBridge\Util\StreamWrapper;
use PHPUnit\Framework\TestCase;

class PassiveDataStreamTest extends TestCase
{

    public function testOpenReturnsTrue()
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->getMockForAbstractClass();

        $commandStream = $this->getMockBuilder(CommandStream::class)
            ->setConstructorArgs(['foo.bar.com', 21, 90, true, new StreamWrapper, $logger])
            ->onlyMethods(['write', 'read'])
            ->getMock();

        $commandStream->expects($this->once())
            ->method('write')
            ->with('PASV');

        $commandStream->expects($this->once())
            ->method('read')
            ->willReturn("227 Entering Passive Mode (192,168,1,9,140,108).");

        $passiveStream = $this->getMockBuilder(PassiveDataStream::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['openSocketConnection'])
            ->getMock();

        $passiveStream->commandStream = $commandStream;

        $passiveStream->expects($this->once())
            ->method('openSocketConnection')
            ->with('192.168.1.9', 35948, 90, true)
            ->willReturn(true);

        $this->assertTrue($passiveStream->open());
    }

    public function testOpenReturnsFalse()
    {
        $commandStream = $this->getMockBuilder(CommandStream::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['write', 'read'])
            ->getMock();

        $commandStream->expects($this->once())
            ->method('write')
            ->with('PASV');

        $commandStream->expects($this->once())
            ->method('read')
            ->willReturn("227 Entering Passive Mode (127,0,0,1,140,108).");

        $passiveStream = $this->getMockBuilder(PassiveDataStream::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['openSocketConnection'])
            ->getMock();

        $passiveStream->commandStream = $commandStream;

        $passiveStream->expects($this->once())
            ->method('openSocketConnection')
            ->willReturn(false);

        $this->assertFalse($passiveStream->open());
    }

    public function testOpenThrowsPassiveDataStreamExceptionWithAnExpectedReplyCode()
    {
        $commandStream = $this->getMockBuilder(CommandStream::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['write', 'read'])
            ->getMock();

        $commandStream->expects($this->once())
            ->method('write')
            ->with('PASV');

        $commandStream->expects($this->once())
            ->method('read')
            ->willReturn("200 Entering Passive Mode (192,168,1,9,140,108).");

        $passiveStream = $this->getMockBuilder(PassiveDataStream::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $passiveStream->commandStream = $commandStream;

        $this->expectException(ResponseException::class);
        $passiveStream->open();
    }

    public function testOpenThrowsPassiveDataStreamExceptionWhereCannotParsingPortNumbers()
    {
        $commandStream = $this->getMockBuilder(CommandStream::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['write', 'read'])
            ->getMock();

        $commandStream->expects($this->once())
            ->method('write')
            ->with('PASV');

        $commandStream->expects($this->once())
            ->method('read')
            ->willReturn("227 Entering Passive Mode (192,168,1,9,140,108.");

        $passiveStream = $this->getMockBuilder(PassiveDataStream::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $passiveStream->commandStream = $commandStream;

        $this->expectException(PassiveDataStreamException::class);
        $this->expectExceptionMessage('Failed to parse the data port numbers from the "PASV" command reply.');
        $passiveStream->open();
    }

    public function testOpenThrowsPassiveDataStreamExceptionWhereCannotParsingHostIP()
    {
        $commandStream = $this->getMockBuilder(CommandStream::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['write', 'read'])
            ->getMock();

        $commandStream->expects($this->once())
            ->method('write')
            ->with('PASV');

        $commandStream->expects($this->once())
            ->method('read')
            ->willReturn("227 Entering Passive Mode 109.106.246.248,140,108).");

        $passiveStream = $this->getMockBuilder(PassiveDataStream::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $passiveStream->commandStream = $commandStream;

        $this->expectException(PassiveDataStreamException::class);
        $this->expectExceptionMessage('Failed to parse the host IP from the "PASV" command reply.');
        $passiveStream->open();
    }

    public function testRead()
    {
        $commandStream = $this->getMockBuilder(CommandStream::class)
            ->disableOriginalConstructor()
            ->getMock();

        $streamWrapper = $this->getMockBuilder(StreamWrapper::class)
            ->onlyMethods(['feof', 'fread'])
            ->getMock();

        $streamWrapper->expects($this->exactly(2))
            ->method('feof')
            ->willReturnOnConsecutiveCalls(false, true);

        $crlf = "\r\n";
        $data = "file1.txt{$crlf}file2.txt{$crlf}";

        $streamWrapper->expects($this->once())
            ->method('fread')
            ->willReturn($data);

        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['log'])
            ->getMockForAbstractClass();

        $logger->expects($this->once())
            ->method('log');

        $passiveStream = $this->getMockBuilder(PassiveDataStream::class)
            ->setConstructorArgs([$commandStream, $streamWrapper, $logger])
            ->onlyMethods([])
            ->getMock();

        $this->assertSame($data, $passiveStream->read());
    }
}
