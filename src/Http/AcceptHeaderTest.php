<?php

namespace JDesrosiers\Http;

class AcceptHeaderTest extends \PHPUnit_Framework_TestCase
{
    public function dataProviderType()
    {
        return array(
            array("application/json", "application"),
            array("text/html", "text"),
            array("text/*", "text"),
        );
    }

    /**
     * @dataProvider dataProviderType
    */
    public function testType($header, $type)
    {
        $accept = new AcceptHeader($header);
        $this->assertEquals($type, $accept->type);
    }

    public function dataProviderSubType()
    {
        return array(
            array("application/json", "json"),
            array("application/javascript", "javascript"),
        );
    }

    /**
     * @dataProvider dataProviderSubType
     */
    public function testSubType($header, $subType)
    {
        $accept = new AcceptHeader($header);
        $this->assertEquals($subType, $accept->subType);
    }

    public function dataProviderMediaRange()
    {
        return array(
            array("application/json", "application/json"),
            array("text/html", "text/html"),
        );
    }

    /**
     * @dataProvider dataProviderMediaRange
     */
    public function testMediaRange($header, $mediaRange)
    {
        $accept = new AcceptHeader($header);
        $this->assertEquals($mediaRange, $accept->mediaRange);
    }

    public function dataProviderQValue()
    {
        return array(
            array("application/json", 1),
            array("application/json; q=1", 1),
            array("application/json; q=0", 0),
            array("application/json; q=1.0", 1),
            array("application/json; q=1.00", 1),
            array("application/json; q=1.000", 1),
            array("application/json; q=0.5", 0.5),
            array("application/json; q=0.50", 0.5),
            array("application/json; q=0.0", 0),
            array("application/json; q=0.00", 0),
            array("application/json; q=0.000", 0),
            array("application/json; q=0.500", 0.5),
        );
    }

    /**
     * @dataProvider dataProviderQValue
     */
    public function testQValue($header, $expectedQ)
    {
        $accept = new AcceptHeader($header);
        $this->assertEquals($expectedQ, $accept->q);
    }

    public function dataProviderQSpacing()
    {
        return array(
            array("application/json", 1),
            array("application/json;q=1", 1),
            array("application/json; q=1", 1),
            array("application/json;q =1", 1),
            array("application/json;q= 1", 1),
            array("application/json;  q=1", 1),
            array("application/json;q  =1", 1),
            array("application/json;q=  1", 1),
            array("application/json; q = 1", 1),
        );
    }

    /**
     * @dataProvider dataProviderQSpacing
     */
    public function testQSpacing($header, $expectedQ)
    {
        $accept = new AcceptHeader($header);
        $this->assertEquals($expectedQ, $accept->q);
    }

    public function dataProviderInvalidExtension()
    {
        return array(
            array("application/json;"),
            array("application/json; foo"),
            array("application/json; foo="),
            array('application/json; foo="'),
            array('application/json; foo="bar'),
        );
    }

    /**
     * @dataProvider dataProviderInvalidExtension
     * @expectedException DomainException
     */
    public function testInvalidExtension($header)
    {
        $accept = new AcceptHeader($header);
    }

    public function testExtension()
    {
        $accept = new AcceptHeader("application/json; foo=bar");
        $this->assertEquals(array("foo" => "bar"), $accept->extensions);
    }

    public function testMultipleExtensions()
    {
        $accept = new AcceptHeader("application/json; foo=bar; something=else");
        $this->assertEquals(array("foo" => "bar", "something" => "else"), $accept->extensions);
    }

    public function testQAndExtension()
    {
        $accept = new AcceptHeader("application/json; q=0.25; foo=bar");
        $this->assertEquals(array("foo" => "bar"), $accept->extensions);
        $this->assertEquals(0.25, $accept->q);
    }

    public function testQMustBeFirst()
    {
        $accept = new AcceptHeader("application/json; foo=bar; q=0.25");
        $this->assertEquals(array("foo" => "bar", "q" => 0.25), $accept->extensions);
    }

    public function dataProviderInvalidQBecomesExtension()
    {
        return array(
            array("application/json; q=2", "2"),
            array("application/json; q=-0.1", "-0.1"),
            array("application/json; q=1.1", "1.1"),
            array("application/json; q=1.0000", "1.0000"),
            array("application/json; q=0.0000", "0.0000"),
            array("application/json; q=0.0005", "0.0005"),
        );
    }

    /**
     * @dataProvider dataProviderInvalidQBecomesExtension
     */
    public function testInvalidQBecomesExtension($header, $value)
    {
        $accept = new AcceptHeader($header);
        $this->assertEquals(1, $accept->q);
        $this->assertEquals(array("q" => $value), $accept->extensions);
    }

    public function dataProviderExtensionSpaces()
    {
        return array(
            array("application/json;foo=bar"),
            array("application/json; foo=bar"),
            array("application/json;foo =bar"),
            array("application/json;foo= bar"),
            array("application/json;  foo=bar"),
            array("application/json;foo  =bar"),
            array("application/json;foo=  bar"),
        );
    }

    /**
     * @dataProvider dataProviderExtensionSpaces
     */
    public function testExtensionSpaces($header)
    {
        $accept = new AcceptHeader($header);
        $this->assertEquals(array("foo" => "bar"), $accept->extensions);
    }

    public function testQuotedExtension()
    {
        $accept = new AcceptHeader('application/json; foo="bar"');
        $this->assertEquals(array("foo" => "bar"), $accept->extensions);
    }

    public function testMixedExtensions()
    {
        $accept = new AcceptHeader('application/json; foo="bar"; something=else');
        $this->assertEquals(array("foo" => "bar", "something" => "else"), $accept->extensions);
    }
}
