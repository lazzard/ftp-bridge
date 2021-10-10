<?php

namespace Lazzard\FtpBridge\Tests\Response;

use Lazzard\FtpBridge\FtpBridge;
use Lazzard\FtpBridge\Response\ResponseParser;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ResponseParserTest extends TestCase
{
    public function testToArrayWithMultilineReply()
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

        $response = (new ResponseParser($reply))->toArray();

        $this->assertSame([
            'code'      => 214,
            'message'   => 'The following commands are recognized (* =>\'s unimplemented):',
            'multiline' => true,
        ], $response);
    }

    public function testToArrayWithOneLineReply()
    {
        $reply = "227 Entering Passive Mode (192,168,1,9,140,108).";

        $response = (new ResponseParser($reply))->toArray();

        $this->assertSame([
            'code'      => 227,
            'message'   => 'Entering Passive Mode (192,168,1,9,140,108).',
            'multiline' => false,
        ], $response);
    }

    public function testParseCodeReturnsIntegerWithAValidReply()
    {
        $reply = "227 Entering Passive Mode (192,168,1,9,140,108).";

        $method = self::getMethod('parseCode');
        $parser = new ResponseParser($reply);

        $this->assertSame(227, $method->invoke($parser));
    }

    public function testParseCodeReturnsFalseWithAnInvalidReply()
    {
        $reply = "invalid ftp reply";

        $method = self::getMethod('parseCode');
        $parser = new ResponseParser($reply);

        $this->assertFalse($method->invoke($parser));
    }

    public function testParseMessageReturnsStringWithOneLineReply()
    {
        $reply = "227 Entering Passive Mode (192,168,1,9,140,108).";

        $method = self::getMethod('parseMessage');
        $parser = new ResponseParser($reply);

        $this->assertSame("Entering Passive Mode (192,168,1,9,140,108).", $method->invoke($parser));
    }

    public function testParseMessageReturnsStingWithMultilineReply()
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

        $method = self::getMethod('parseMessage');
        $parser = new ResponseParser($reply);

        $this->assertSame("The following commands are recognized (* =>'s unimplemented):", $method->invoke($parser));
    }

    public function testParseMessageReturnsFalseWithAnInvalidReply()
    {
        $reply = "invalid ftp reply";

        $method = self::getMethod('parseMessage');
        $parser = new ResponseParser($reply);

        $this->assertFalse($method->invoke($parser));
    }

    public function testIsMultilineReturnsFalseWithOneLineReply()
    {
        $method = self::getMethod('isMultiline');

        $parser = new ResponseParser("227 Entering Passive Mode (192,168,1,9,140,108).");

        $this->assertFalse($method->invoke($parser));
    }

    public function testIsMultilineReturnsTrueWithMultilineReply()
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

        $method = self::getMethod('isMultiline');
        $parser = new ResponseParser($reply);

        $this->assertTrue($method->invoke($parser));
    }

    protected static function getMethod($name)
    {
        $class  = new ReflectionClass(ResponseParser::class);
        $method = $class->getMethod($name);

        $method->setAccessible(true);
        return $method;
    }
}
