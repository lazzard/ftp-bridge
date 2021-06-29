<?php

namespace Response;

use Lazzard\FtpBridge\Response\ResponseParser;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ResponseParserTest extends TestCase
{
    public function testConstructor()
    {
        $parser = $this->getMockBuilder(ResponseParser::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertInstanceOf(ResponseParser::class, $parser);
    }

    public function testParseWithMultilineResponse()
    {
        // testing with response is multiline
        $rawResponse = "214-The following SITE commands are recognized
 ALIAS
 CHMOD
 IDLE
 UTIME
214 Pure-FTPd - http://pureftpd.org/
";

        $parser   = new ResponseParser($rawResponse);
        $response = $parser->parseToArray();

        $this->assertSame(214, $response['code']);
        $this->assertSame("The following SITE commands are recognized", $response['message']);
        $this->assertTrue($response['multiline']);
    }

    public function testParseWithOneLineResponse()
    {
        $rawResponse = "200 Zzz..";

        $parser   = new ResponseParser($rawResponse);
        $response = $parser->parseToArray();

        $this->assertSame(200, $response['code']);
        $this->assertSame(" Zzz..", $response['message']);
        $this->assertFalse($response['multiline']);
    }

    public function testParseCodeWithRegularResponse()
    {
        $rawResponse = "200 Zzz..";

        $method = self::getMethod('parseCode');
        $parser = new ResponseParser($rawResponse);

        $this->assertSame(200, $method->invoke($parser));
    }

    public function testParseCodeWithNoCodeInResponse()
    {
        $rawResponse = "foo";

        $method = self::getMethod('parseCode');
        $parser = new ResponseParser($rawResponse);

        $this->assertFalse($method->invoke($parser));
    }

    public function testIsMultilineWithMultilineResponse()
    {
        $rawResponse = "214-The following SITE commands are recognized
 ALIAS
 CHMOD
 IDLE
 UTIME
214 Pure-FTPd - http://pureftpd.org/";

        $method = self::getMethod('isMultiline');
        $parser = new ResponseParser($rawResponse);

        $this->assertTrue($method->invoke($parser));
    }

    public function testIsMultilineWithOneLineResponse()
    {
        $rawResponse = "200 Zzz..";

        $method = self::getMethod('isMultiline');
        $parser = new ResponseParser($rawResponse);

        $this->assertFalse($method->invoke($parser));
    }

    public function testParseMessageWithOneLineResponse()
    {
        $rawResponse = "200 Zzz..";

        $method = self::getMethod('parseMessage');
        $parser = new ResponseParser($rawResponse);

        $this->assertSame(" Zzz..", $method->invoke($parser));
    }

    public function testParseMessageWithMultilineResponse()
    {
        $rawResponse = "214-The following SITE commands are recognized
 ALIAS
 CHMOD
 IDLE
 UTIME
214 Pure-FTPd - http://pureftpd.org/";

        $method = self::getMethod('parseMessage');
        $parser = new ResponseParser($rawResponse);

        $this->assertSame("The following SITE commands are recognized", $method->invoke($parser));
    }

    protected static function getMethod($name)
    {
        $class  = new ReflectionClass(ResponseParser::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }
}
