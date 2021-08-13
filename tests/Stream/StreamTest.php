<?php

namespace Lazzard\FtpBridge\Tests\Stream;

use Lazzard\FtpBridge\Exception\StreamException;
use Lazzard\FtpBridge\FtpBridge;
use Lazzard\FtpBridge\Logger\Logger;
use Lazzard\FtpBridge\Stream\Stream;
use Lazzard\FtpBridge\Util\StreamWrapper;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class StreamTest extends TestCase
{
    public function testWriteReturnsTrue()
    {
        $wrapper = $this->getMockBuilder(StreamWrapper::class)
            ->onlyMethods(['fwrite'])
            ->getMock();

        $wrapper->expects($this->once())
            ->method('fwrite')
            ->willReturn(true);

        $stream = $this->getMockBuilder(Stream::class)
            ->onlyMethods([])
            ->getMockForAbstractClass();

        $stream->streamWrapper = $wrapper;

        $this->assertTrue($stream->write('HELP'));
    }

    public function testWriteReturnsFalse()
    {
        $wrapper = $this->getMockBuilder(StreamWrapper::class)
            ->onlyMethods(['fwrite'])
            ->getMock();

        $wrapper->expects($this->once())
            ->method('fwrite')
            ->willReturn(false);

        $stream = $this->getMockBuilder(Stream::class)
            ->onlyMethods([])
            ->getMockForAbstractClass();

        $stream->streamWrapper = $wrapper;

        $this->assertFalse($stream->write('HELP'));
    }

    public function testWriteWhereLoggerIsAvailable()
    {
        $wrapper = $this->getMockBuilder(StreamWrapper::class)
            ->onlyMethods(['fwrite'])
            ->getMock();

        $wrapper->expects($this->once())
            ->method('fwrite')
            ->willReturn(true);

        $logger = $this->getMockBuilder(Logger::class)
            ->onlyMethods(['command'])
            ->getMockForAbstractClass();

        $logger->expects($this->once())
            ->method('command');

        $stream = $this->getMockBuilder(Stream::class)
            ->getMockForAbstractClass();

        $stream->streamWrapper = $wrapper;
        $stream->logger        = $logger;

        $stream->write('USER username');
    }

    public function testClose()
    {
        $stream = $this->getMockBuilder(Stream::class)
            ->getMockForAbstractClass();

        $stream->stream = self::getFakeStream();

        $this->assertTrue($stream->close());
    }

    public function testLogReturnsNullWhereLoggerIsNotAvailable()
    {
        $stream = $this->getMockBuilder(Stream::class)
            ->getMockForAbstractClass();

        $method = self::getMethod('log');

        $this->assertNull($method->invoke($stream, "220 FTP Server ready."));
    }

    public function testLogItLogsWithInfoLevel()
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->onlyMethods(['info'])
            ->getMockForAbstractClass();

        $message = "220 FTP Server ready.";

        $logger->expects($this->once())
            ->method('info')
            ->with($message);

        $stream = $this->getMockBuilder(Stream::class)
            ->getMockForAbstractClass();

        $stream->logger = $logger;

        $method = self::getMethod('log');

        $this->assertNull($method->invoke($stream, $message));
    }

    public function testLogItLogsWithErrorLevel()
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->onlyMethods(['error'])
            ->getMockForAbstractClass();

        $message = "500 UNKNOWN not understood";

        $logger->expects($this->once())
            ->method('error')
            ->with($message);

        $stream = $this->getMockBuilder(Stream::class)
            ->getMockForAbstractClass();

        $stream->logger = $logger;

        $method = self::getMethod('log');

        $this->assertNull($method->invoke($stream, $message));
    }

    public function testOpenSocketConnectionReturnsTrue()
    {
        $host     = 'foo.bar.com';
        $port     = 21;
        $timeout  = 90;
        $blocking = true;

        $wrapper = $this->getMockBuilder(StreamWrapper::class)
            ->onlyMethods(['streamSocketClient'])
            ->getMock();

        $wrapper->expects($this->once())
            ->method('streamSocketClient')
            //->with($host, $port, $timeout, $blocking)
            ->willReturn(true);

        $stream = $this->getMockBuilder(Stream::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMockForAbstractClass();

        $stream->streamWrapper = $wrapper;

        $method = self::getMethod('openSocketConnection');

        $this->assertTrue($method->invoke($stream, $host, $port, $timeout, $blocking));
    }

    public function testOpenSocketConnectionReturnsFalse()
    {
        $host     = 'foo.bar.com';
        $port     = 21;
        $timeout  = 90;
        $blocking = true;

        $wrapper = $this->getMockBuilder(StreamWrapper::class)
            ->onlyMethods(['streamSocketClient'])
            ->getMock();

        $wrapper->expects($this->once())
            ->method('streamSocketClient')
            ->with("tcp://$host:$port", $timeout, $blocking)
            ->willReturn(false);

        $stream = $this->getMockBuilder(Stream::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMockForAbstractClass();

        $stream->streamWrapper = $wrapper;

        $method = self::getMethod('openSocketConnection');

        $this->assertFalse($method->invoke($stream, $host, $port, $timeout, $blocking));
    }

    public function testOpenSocketConnectionThrowsStreamException()
    {
        $wrapper = $this->getMockBuilder(StreamWrapper::class)
            ->onlyMethods([])
            ->getMock();

        $stream = $this->getMockBuilder(Stream::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $stream->streamWrapper = $wrapper;

        $method = self::getMethod('openSocketConnection');

        $this->expectException(StreamException::class);
        $method->invoke($stream, 'foo.bar.com', 21, 90, true);
    }

    public function testPrepareCommand()
    {
        $stream = $this->getMockBuilder(Stream::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $crlf = FtpBridge::CRLF;

        $method = self::getMethod('prepareCommand');

        $command = " USER username ";

        $this->assertSame("USER username$crlf", $method->invoke($stream, $command));
    }

    protected static function getFakeStream($mode = 'r+')
    {
        return fopen('php://memory', $mode);
    }

    protected static function getMethod($name)
    {
        $class  = new ReflectionClass(Stream::class);
        $method = $class->getMethod($name);

        $method->setAccessible(true);
        return $method;
    }
}
