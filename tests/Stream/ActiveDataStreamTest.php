<?php

namespace Lazzard\FtpBridge\Tests\Stream;

use DG\BypassFinals;
use Lazzard\FtpBridge\Exception\ActiveDataStreamException;
use Lazzard\FtpBridge\Exception\ResponseException;
use PHPUnit\Framework\TestCase;
use Lazzard\FtpBridge\Stream\ActiveDataStream;
use Lazzard\FtpBridge\Stream\StreamInterface;
use Lazzard\FtpBridge\Util\StreamWrapper;
use Lazzard\FtpBridge\Stream\CommandStream;

class ActiveDataStreamTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        BypassFinals::enable();
    }

    public function testInstanceImplementsStreamInterface()
    {
        $activeStream = $this->getMockBuilder(ActiveDataStream::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertInstanceOf(StreamInterface::class, $activeStream);
    }

    public function testOpenReturnsTrue()
    {
        $port = 25601;

        $streamWrapper = $this->getMockBuilder(StreamWrapper::class)
            ->onlyMethods(['streamSocketServer'])
            ->getMock();

        $streamWrapper->expects($this->once())
            ->method('streamSocketServer')
            ->with("tcp://0.0.0.0:$port")
            ->willReturn(true);

        $streamWrapperForCommandStream = $this->getMockBuilder(StreamWrapper::class)
            ->onlyMethods(['streamSocketGetName'])
            ->getMock();

        $streamWrapperForCommandStream->expects($this->once())
            ->method('streamSocketGetName');

        $commandStream = $this->getMockBuilder(CommandStream::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['write', 'read'])
            ->getMock();

        $commandStream->streamWrapper = $streamWrapperForCommandStream;

        $commandStream->expects($this->once())
            ->method('write')
            ->willReturn(true);

        $commandStream->expects($this->once())
            ->method('read')
            ->willReturn("200 PORT command successful.\r\n");

        $activeStream = $this->getMockBuilder(ActiveDataStream::class)
            ->setConstructorArgs([$commandStream, $streamWrapper, null])
            ->onlyMethods(['calculatePortNumber'])
            ->getMock();

        $activeStream->expects($this->once())
            ->method('calculatePortNumber')
            ->willReturn($port);

        $this->assertTrue($activeStream->open());
    }

    public function testOpenThrowsExceptionIfCannotWriteToControlStream()
    {
        $port = 25601;

        $streamWrapper = $this->getMockBuilder(StreamWrapper::class)
            ->onlyMethods(['streamSocketServer'])
            ->getMock();

        $streamWrapper->expects($this->once())
            ->method('streamSocketServer')
            ->with("tcp://0.0.0.0:$port")
            ->willReturn(true);

        $streamWrapperForCommandStream = $this->getMockBuilder(StreamWrapper::class)
            ->onlyMethods(['streamSocketGetName'])
            ->getMock();

        $streamWrapperForCommandStream->expects($this->once())
            ->method('streamSocketGetName');

        $commandStream = $this->getMockBuilder(CommandStream::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['write'])
            ->getMock();

        $commandStream->streamWrapper = $streamWrapperForCommandStream;

        $commandStream->expects($this->once())
            ->method('write')
            ->willReturn(false);

        $activeStream = $this->getMockBuilder(ActiveDataStream::class)
            ->setConstructorArgs([$commandStream, $streamWrapper, null])
            ->onlyMethods(['calculatePortNumber'])
            ->getMock();

        $activeStream->expects($this->once())
            ->method('calculatePortNumber')
            ->willReturn($port);

        $this->expectException(ActiveDataStreamException::class);
        $this->expectExceptionMessage('Unable to send the PORT command to the server.');

        $activeStream->open();
    }

    public function testOpenThrowsExceptionIfUnexpectedResponseSentFromTheServer()
    {
        $port = 25601;

        $streamWrapper = $this->getMockBuilder(StreamWrapper::class)
            ->onlyMethods(['streamSocketServer'])
            ->getMock();

        $streamWrapper->expects($this->once())
            ->method('streamSocketServer')
            ->with("tcp://0.0.0.0:$port")
            ->willReturn(true);

        $streamWrapperForCommandStream = $this->getMockBuilder(StreamWrapper::class)
            ->onlyMethods(['streamSocketGetName'])
            ->getMock();

        $streamWrapperForCommandStream->expects($this->once())
            ->method('streamSocketGetName');

        $commandStream = $this->getMockBuilder(CommandStream::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['write', 'read'])
            ->getMock();

        $commandStream->expects($this->once())
            ->method('read')
            ->willReturn("421 Service not available, closing control connection.\r\n");

        $commandStream->streamWrapper = $streamWrapperForCommandStream;

        $commandStream->expects($this->once())
            ->method('write')
            ->willReturn(true);

        $activeStream = $this->getMockBuilder(ActiveDataStream::class)
            ->setConstructorArgs([$commandStream, $streamWrapper, null])
            ->onlyMethods(['calculatePortNumber'])
            ->getMock();

        $activeStream->expects($this->once())
            ->method('calculatePortNumber')
            ->willReturn($port);

        $this->expectException(ResponseException::class);
        $this->expectExceptionMessage('Service not available, closing control connection.');

        $activeStream->open();
    }

    public function testOpenReturnsFalse()
    {
        $streamWrapper = $this->getMockBuilder(StreamWrapper::class)
            ->onlyMethods(['streamSocketServer'])
            ->getMock();

        $streamWrapper->expects($this->once())
            ->method('streamSocketServer')
            ->withAnyParameters()
            ->willReturn(false);

        $activeStream = $this->getMockBuilder(ActiveDataStream::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $activeStream->streamWrapper = $streamWrapper;

        $this->assertFalse($activeStream->open());
    }

    public function testReadString()
    {
        $streamWrapper = $this->getMockBuilder(StreamWrapper::class)
            ->onlyMethods([
                'streamSocketAccept',
                'fread',
                'feof',
                'fclose'
            ])
            ->getMock();

        $crlf = "\r\n";
        $data = "file1.txt{$crlf}file2.txt{$crlf}";

        $streamWrapper->expects($this->exactly(1))
            ->method('fread')
            ->willReturn($data);

        $streamWrapper->expects($this->once())
            ->method('feof')
            ->willReturn(true);

        $activeStream = $this->getMockBuilder(ActiveDataStream::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $activeStream->streamWrapper = $streamWrapper;

        $this->assertSame($data, $activeStream->read());
    }

    public function testReadReturnsFalse()
    {
        $streamWrapper = $this->getMockBuilder(StreamWrapper::class)
            ->onlyMethods(['streamSocketAccept'])
            ->getMock();

        $streamWrapper->expects($this->exactly(1))
            ->method('streamSocketAccept')
            ->willReturn(false);

        $activeStream = $this->getMockBuilder(ActiveDataStream::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $activeStream->streamWrapper = $streamWrapper;

        $this->assertFalse($activeStream->read());
    }
}