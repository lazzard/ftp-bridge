<?php

namespace Lazzard\FtpBridge\Tests\Unit;

use DG\BypassFinals;
use Lazzard\FtpBridge\Exception\ActiveDataStreamException;
use Lazzard\FtpBridge\Exception\CommandStreamException;
use Lazzard\FtpBridge\Exception\PassiveDataStreamException;
use Lazzard\FtpBridge\Exception\ResponseException;
use Lazzard\FtpBridge\FtpBridge;
use Lazzard\FtpBridge\Response\Response;
use Lazzard\FtpBridge\Stream\ActiveDataStream;
use Lazzard\FtpBridge\Stream\CommandStream;
use Lazzard\FtpBridge\Stream\DataStream;
use Lazzard\FtpBridge\Stream\PassiveDataStream;
use PHPUnit\Framework\TestCase;

class FtpBridgeTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        BypassFinals::enable();
    }

    public function test_send_throws_exception(): void
    {
        $commandStreamMock = $this->getMockBuilder(CommandStream::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['write'])
            ->getMock();

        $commandStreamMock->expects($this->once())
            ->method('write')
            ->willReturn(false);

        $ftpBridge = new FtpBridge;
        $ftpBridge->setCommandStream($commandStreamMock);

        $this->expectException(CommandStreamException::class);

        $ftpBridge->send("NOOP");
    }

    public function test_send_returns_true(): void
    {
        $commandStreamMock = $this->getMockBuilder(CommandStream::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['write'])
            ->getMock();

        $commandStreamMock->expects($this->once())
            ->method('write')
            ->willReturn(true);

        $ftpBridge = new FtpBridge;
        $ftpBridge->setCommandStream($commandStreamMock);

        $this->assertTrue($ftpBridge->send("NOOP"));
    }

    public function test_write_throws_exception_with_passive_mode(): void
    {
        $passiveDataStreamMock = $this->getMockBuilder(PassiveDataStream::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['write'])
            ->getMock();

        $passiveDataStreamMock->expects($this->once())
            ->method('write')
            ->willReturn(false);

        $ftpBridge = new FtpBridge;
        $ftpBridge->setDataStream($passiveDataStreamMock);

        $this->expectException(PassiveDataStreamException::class);

        $ftpBridge->write("010101");
    }

    public function test_write_throws_exception_with_active_mode(): void
    {
        $activeDataStreamMock = $this->getMockBuilder(ActiveDataStream::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['write'])
            ->getMock();

        $activeDataStreamMock->expects($this->once())
            ->method('write')
            ->willReturn(false);

        $ftpBridge = new FtpBridge;
        $ftpBridge->setDataStream($activeDataStreamMock);

        $this->expectException(ActiveDataStreamException::class);

        $ftpBridge->write("010101");
    }

    public function test_write_return_true(): void
    {
        $dataStreamMock = $this->getMockBuilder(DataStream::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['write'])
            ->getMockForAbstractClass();

        $dataStreamMock->expects($this->once())
            ->method('write')
            ->willReturn(true);

        $ftpBridge = new FtpBridge;
        $ftpBridge->setDataStream($dataStreamMock);

        $this->assertTrue($ftpBridge->write("010101"));
    }

    public function test_receive_throws_exception(): void
    {
        $commandStreamMock = $this->getMockBuilder(CommandStream::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['read'])
            ->getMock();

        $commandStreamMock->expects($this->once())
            ->method('read')
            ->willReturn(false);

        $ftpBridge = new FtpBridge;
        $ftpBridge->setCommandStream($commandStreamMock);

        $this->expectException(CommandStreamException::class);

        $ftpBridge->receive();
    }

    public function test_receive_returns_response_object(): void
    {
        $commandStreamMock = $this->getMockBuilder(CommandStream::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['read'])
            ->getMock();

        $commandStreamMock->expects($this->once())
            ->method('read')
            ->willReturn("220 FTP Server ready.\r\n");

        $ftpBridge = new FtpBridge;
        $ftpBridge->setCommandStream($commandStreamMock);

        $this->assertInstanceOf(Response::class, $ftpBridge->receive());
        $this->assertInstanceOf(Response::class, $ftpBridge->response);
    }

    public function test_receiveData_throws_exception_with_passive_mode(): void
    {
        $passiveDataStreamMock = $this->getMockBuilder(PassiveDataStream::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['read'])
            ->getMock();

        $passiveDataStreamMock->expects($this->once())
            ->method('read')
            ->willReturn(false);

        $ftpBridge = new FtpBridge;
        $ftpBridge->setDataStream($passiveDataStreamMock);

        $this->expectException(PassiveDataStreamException::class);

        $ftpBridge->receiveData();
    }

    public function test_receiveData_throws_exception_with_active_mode(): void
    {
        $activeDataStreamMock = $this->getMockBuilder(ActiveDataStream::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['read'])
            ->getMock();

        $activeDataStreamMock->expects($this->once())
            ->method('read')
            ->willReturn(false);

        $ftpBridge = new FtpBridge;
        $ftpBridge->setDataStream($activeDataStreamMock);

        $this->expectException(ActiveDataStreamException::class);

        $ftpBridge->receiveData();
    }

    public function test_receiveData_returns_true(): void
    {
        $dataStreamMock = $this->getMockBuilder(DataStream::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['read'])
            ->getMockForAbstractClass();

        $dataStreamMock->expects($this->once())
            ->method('read')
            ->willReturn(true);

        $ftpBridge = new FtpBridge;
        $ftpBridge->setDataStream($dataStreamMock);

        $this->assertTrue($ftpBridge->receiveData());
    }

    public function test_login_returns_true_if_user_already_logged(): void
    {
        $commandStreamMock = $this->getMockBuilder(CommandStream::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['write', 'read'])
            ->getMock();

        $commandStreamMock->expects($this->once())
            ->method('write')
            ->with('USER username')
            ->willReturn(true);

        $commandStreamMock->expects($this->once())
            ->method('read')
            ->willReturn('230 User username logged in\r\n');

        $ftpBridge = new FtpBridge;
        $ftpBridge->setCommandStream($commandStreamMock);

        $ftpBridge->login("username", "1234");
    }

    public function test_login_throws_exception(): void
    {
        $commandStreamMock = $this->getMockBuilder(CommandStream::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['write', 'read'])
            ->getMock();

        $commandStreamMock->expects($this->once())
            ->method('write')
            ->with('USER username')
            ->willReturn(true);

        $commandStreamMock->expects($this->once())
            ->method('read')
            ->willReturn('Unexpected FTP reply\r\n');

        $ftpBridge = new FtpBridge;
        $ftpBridge->setCommandStream($commandStreamMock);

        $this->expectException(ResponseException::class);

        $ftpBridge->login("username", "1234");
    }

    public function test_login_returns_true_login_correct(): void
    {
        $commandStreamMock = $this->getMockBuilder(CommandStream::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['write', 'read'])
            ->getMock();

        $commandStreamMock->expects($this->exactly(2))
            ->method('write')
            ->withConsecutive(['USER username'], ['PASS 1234'])
            ->willReturnOnConsecutiveCalls(true, true);

        $commandStreamMock->expects($this->exactly(2))
            ->method('read')
            ->willReturnOnConsecutiveCalls(
                '331 Password required for username\r\n',
                '230 User username logged in'
            );

        $ftpBridge = new FtpBridge;
        $ftpBridge->setCommandStream($commandStreamMock);

        $this->assertTrue($ftpBridge->login("username", "1234"));
    }

    public function test_login_throws_exception_login_incorrect(): void
    {
        $commandStreamMock = $this->getMockBuilder(CommandStream::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['write', 'read'])
            ->getMock();

        $commandStreamMock->expects($this->exactly(2))
            ->method('write')
            ->withConsecutive(['USER username'], ['PASS 1234'])
            ->willReturnOnConsecutiveCalls(true, true);

        $commandStreamMock->expects($this->exactly(2))
            ->method('read')
            ->willReturnOnConsecutiveCalls(
                '331 Password required for username\r\n',
                '530 Login incorrect.'
            );

        $ftpBridge = new FtpBridge;
        $ftpBridge->setCommandStream($commandStreamMock);

        $this->expectException(ResponseException::class);
        $this->expectExceptionMessage("Login incorrect.");

        $ftpBridge->login("username", "1234");
    }

    public function test_setTransferType_throws_exception(): void
    {
        $commandStreamMock = $this->getMockBuilder(CommandStream::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['write', 'read'])
            ->getMock();

        $commandStreamMock->expects($this->once())
            ->method('write')
            ->with("TYPE X")
            ->willReturn(true);

        $commandStreamMock->expects($this->once())
            ->method('read')
            ->willReturn('504 TYPE not implemented for \'X\' parameter\r\n');

        $ftpBridge = new FtpBridge;
        $ftpBridge->setCommandStream($commandStreamMock);

        $this->expectException(ResponseException::class);
        $this->expectExceptionMessage("TYPE not implemented for 'X' parameter");

        $ftpBridge->setTransferType("X");
    }

    public function test_setTransferType_returns_true(): void
    {
        $commandStreamMock = $this->getMockBuilder(CommandStream::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['write', 'read'])
            ->getMock();

        $commandStreamMock->expects($this->once())
            ->method('write')
            ->with("TYPE " . FtpBridge::TR_TYPE_BINARY)
            ->willReturn(true);

        $commandStreamMock->expects($this->once())
            ->method('read')
            ->willReturn('200 Type set to I\r\n');

        $ftpBridge = new FtpBridge;
        $ftpBridge->setCommandStream($commandStreamMock);

        $this->assertTrue($ftpBridge->setTransferType(FtpBridge::TR_TYPE_BINARY));
    }
}
