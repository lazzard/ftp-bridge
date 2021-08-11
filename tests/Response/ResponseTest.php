<?php

namespace Lazzard\FtpBridge\Tests\Response;

use Lazzard\FtpBridge\FtpBridge;
use Lazzard\FtpBridge\Response\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    protected static $reply;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $crlf = FtpBridge::CRLF;

        self::$reply = "214-The following commands are recognized (* =>'s unimplemented):{$crlf}" .
            "CWD     XCWD    CDUP    XCUP    SMNT*   QUIT    PORT    PASV{$crlf}" .
            "EPRT    EPSV    ALLO*   RNFR    RNTO    DELE    MDTM    RMD{$crlf}" .
            "XRMD    MKD     XMKD    PWD     XPWD    SIZE    SYST    HELP{$crlf}" .
            "NOOP    FEAT    OPTS    HOST    CLNT    AUTH    CCC*    CONF*{$crlf}" .
            "ENC*    MIC*    PBSZ    PROT    TYPE    STRU    MODE    RETR{$crlf}" .
            "STOR    STOU    APPE    REST    ABOR    USER    PASS    ACCT*{$crlf}" .
            "REIN*   LIST    NLST    STAT    SITE    MLSD    MLST{$crlf}" .
            "214 Direct comments to root@localhost{$crlf}";
    }

    public function testGetRaw()
    {
        $this->assertSame(self::$reply, (new Response(self::$reply))->getRaw());
    }

    public function testGetCode()
    {
        $this->assertSame(214, (new Response(self::$reply))->getCode());
    }

    public function testGetMessage()
    {
        $this->assertSame("The following commands are recognized (* =>'s unimplemented):", (new Response(self::$reply))->getMessage());
    }

    public function testIsMultiline()
    {
        $this->assertTrue((new Response(self::$reply))->isMultiline());
    }

    public function testHasCodeReturnsTrue()
    {
        $this->assertTrue((new Response(self::$reply))->hasCode(214));
    }

    public function testHasCodeReturnsFalse()
    {
        $this->assertFalse((new Response(self::$reply))->hasCode(500));
    }

}
