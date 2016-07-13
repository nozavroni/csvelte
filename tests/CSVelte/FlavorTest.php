<?php

use PHPUnit\Framework\TestCase;
use Mockery as m;
use Mockery\Adapter\PHPUnit\MockeryPHPUnitIntegration;
use CSVelte\Flavor;

/**
 * CSVelte\FlavorTest
 *
 * @package   CSVelte\Flavor Unit Tests
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class FlavorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * This is just a really simple test to get things started...
     */
    public function testCSVelteFlavor()
    {
        $this->assertInstanceOf($expected = 'CSVelte\Flavor', new Flavor);
    }

    /**
     * Test that CSVelte\Flavor provides reasonable default values for its attributes
     */
    public function testCSVelteFlavorDefaults()
    {
        $flavor = new Flavor;
        $this->assertEquals($delimiter = ",", $flavor->delimiter);
        $this->assertEquals($quoteChar = "\"", $flavor->quoteChar);
        $this->assertEquals($escapeChar = "\\", $flavor->escapeChar);
        $this->assertEquals($lineTerminator = "\r\n", $flavor->lineTerminator);
        $this->assertEquals($quoting = Flavor::QUOTE_MINIMAL, $flavor->quoteStyle);
        $this->assertFalse($flavor->doubleQuote);
        $this->assertFalse($flavor->skipInitialSpace);
    }

    /**
     * @expectedException CSVelte\Exception\UnknownAttributeException
     */
    public function testCSVelteFlavorGetNonExistAttributeThrowsException()
    {
        $flavor = new Flavor;
        $foo = $flavor->foo;
    }

    /**
     * These objects are immutable, so any attempt to set an attribute should
     * result in an exception being thrown.
     * @expectedException CSVelte\Exception\ImmutableException
     */
    public function testCSVelteFlavorSetAttributeThrowsImmutableException()
    {
        $flavor = new Flavor;
        $flavor->foo = 'bar';
    }

    /**
     *
     */
    public function testInitializeFlavorUsingAssociativeArray()
    {
        $attribs = [
            'delimiter' => "\t",
            'quoteChar' => "'",
            'escapeChar' => "'",
            'lineTerminator' => "\r\n",
            'quoteStyle' => Flavor::QUOTE_MINIMAL
        ];
        $flavor = new Flavor($attribs);
        $this->assertEquals($attribs['delimiter'], $flavor->delimiter);
        $this->assertEquals($attribs['quoteChar'], $flavor->quoteChar);
        $this->assertEquals($attribs['escapeChar'], $flavor->escapeChar);
        $this->assertEquals($attribs['lineTerminator'], $flavor->lineTerminator);
        $this->assertEquals($attribs['quoteStyle'], $flavor->quoteStyle);
    }

    public function testInitializeFlavorWithProperties()
    {
        $flavor = new Flavor(null, array(
            'hasHeader' => true
        ));
        $this->assertTrue($flavor->getProperty('hasHeader'));
        $flavor->setProperty('hasHeader', false);
        $this->assertFalse($flavor->getProperty('hasHeader'));
    }

    public function testSettingNonExistentPropertyIsAllowed()
    {
        // I'm thinking I might allow "creating" properties that don't already
        // exist within the Flavor class on-the-fly. This would allow for end-users
        // to provide their own properties. Until I find a reason not to, I'll
        // allow it...
        $flavor = new Flavor(null, array('foo' => 'bar'));
        $this->assertEquals($expected = "bar", $flavor->getProperty('foo'));
        $flavor->setProperty('foo', 'baz');
        $flavor->setProperty('bar', 'foobar');
        $this->assertEquals($expected = "baz", $flavor->getProperty('foo'));
        $this->assertEquals($expected = "foobar", $flavor->getProperty('bar'));
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testGetNonExistentPropertyThrowsException()
    {
        $flavor = new Flavor();
        $flavor->getProperty('poopoo');
    }
}
