<?php

namespace Lazzard\FtpBridge\Tests\Stream;

use Lazzard\FtpBridge\FtpBridge;
use Lazzard\FtpBridge\Logger\Logger;
use Lazzard\FtpBridge\Stream\Stream;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class StreamTest extends TestCase
{
    public function testWriteReturnsTrue()
    {
        $stream = $this->getMockBuilder(Stream::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fwrite'])
            ->getMockForAbstractClass();

        $stream->expects($this->once())
            ->method('fwrite')
            ->willReturn(true);

        $this->assertTrue($stream->write('USER username'));
    }

    public function testWriteReturnsFalse()
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['command'])
            ->getMockForAbstractClass();

        $logger->expects($this->never())
            ->method('command');

        $stream = $this->getMockBuilder(Stream::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fwrite'])
            ->getMockForAbstractClass();

        $stream->expects($this->once())
            ->method('fwrite')
            ->willReturn(false);

        $stream->logger = $logger;

        $this->assertFalse($stream->write('USER username'));
    }

    public function testWriteWhereLoggerIsAvailable()
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['command'])
            ->getMockForAbstractClass();

        $logger->expects($this->once())
            ->method('command');

        $stream = $this->getMockBuilder(Stream::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fwrite'])
            ->getMockForAbstractClass();

        $stream->expects($this->once())
            ->method('fwrite')
            ->willReturn(true);

        $stream->logger = $logger;

        $this->assertTrue($stream->write('USER username'));
    }

    public function testClose()
    {
        $stream = $this->getMockBuilder(Stream::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $stream->stream = fopen('php://memory', 'r');

        $this->assertTrue($stream->close());
    }

    public function testLogItLogsWithInfoLevel()
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['info'])
            ->getMockForAbstractClass();

        $message = "220 FTP Server ready.";

        $logger->expects($this->once())
            ->method('info')
            ->with($message);

        $stream = $this->getMockBuilder(Stream::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $stream->logger = $logger;

        $method = self::getMethod('log');

        $this->assertNull($method->invoke($stream, $message));
    }

    public function testLogItLogsWithErrorLevel()
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['error'])
            ->getMockForAbstractClass();

        $message = "500 UNKNOWN not understood";

        $logger->expects($this->once())
            ->method('error')
            ->with($message);

        $stream = $this->getMockBuilder(Stream::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $stream->logger = $logger;

        $method = self::getMethod('log');

        $this->assertNull($method->invoke($stream, $message));
    }

    public function testOpenStreamSocket()
    {
        $stream = $this->getMockBuilder(Stream::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['openSocketConnection'])
            ->getMockForAbstractClass();

        $stream->expects($this->once())
            ->method('openSocketConnection')
            ->willReturn(fopen('php://memory', 'r+'));

        $method = self::getMethod('openStreamSocket');

        $this->assertTrue($method->invoke($stream, 'foo.bar.com', 21, 90, true));
    }

    public function testPrepareCommand()
    {
        $stream = $this->getMockBuilder(Stream::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $crlf = FtpBridge::CRLF;

        $method = self::getMethod('prepareCommand');

        $command = "USER username";

        $this->assertSame("$command$crlf", $method->invoke($stream, "USER username"));
    }

    protected static function getMethod($name)
    {
        $class  = new ReflectionClass(Stream::class);
        $method = $class->getMethod($name);

        $method->setAccessible(true);
        return $method;
    }
}
