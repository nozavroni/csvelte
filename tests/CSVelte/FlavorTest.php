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
        $this->assertNull($flavor->header);
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

    public function testFlavorCanCopyItself()
    {
        $flavor = new Flavor($exp_attribs = array('delimiter' => '|', 'quoteChar' => "'", 'escapeChar' => '&', 'doubleQuote' => true, 'skipInitialSpace' => true, 'quoteStyle' => Flavor::QUOTE_NONE, 'lineTerminator' => "\r", 'header' => null));
        $this->assertEquals($flavor, $flavor->copy());
        $this->assertNotSame($flavor, $flavor->copy());
    }

    public function testFlavorCanCopyItselfWithAlteredAttribs()
    {
        $flavor = new Flavor($attribs = array('delimiter' => '|', 'quoteChar' => "'", 'escapeChar' => '&', 'doubleQuote' => true, 'skipInitialSpace' => true, 'quoteStyle' => Flavor::QUOTE_NONE, 'lineTerminator' => "\r", 'header' => null));

        $new_attribs = array(
            'header' => true,
            'lineTerminator' => "\n",
            'delimiter' => "\t"
        );
        $exp_attribs = array_merge($attribs, $new_attribs);

        $this->assertEquals(new Flavor($exp_attribs), $flavor->copy($new_attribs));
    }

}
