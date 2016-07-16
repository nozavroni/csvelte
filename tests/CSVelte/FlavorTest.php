<?php

use PHPUnit\Framework\TestCase;
use Mockery as m;
use Mockery\Adapter\PHPUnit\MockeryPHPUnitIntegration;
use CSVelte\Exception\UnknownFlavorException;
use CSVelte\Flavor;
use CSVelte\Flavor\Excel;
use CSVelte\Flavor\ExcelTab;
use CSVelte\Flavor\Unix;
use CSVelte\Flavor\UnixTab;

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
        $flavor = new Flavor(array('delimiter' => "\tab!!"));
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

    // public function testFlavorCreateFactoryByName()
    // {
    //     $excel = Flavor::create('excel-tab');
    //     $this->assertInstanceOf(Excel::class, $excel);
    // }
    //
    // public function testConcreteFlavorClasses()
    // {
    //     $expected = array(
    //         // this is the RFC standard for CSV according to https://tools.ietf.org/html/rfc4180
    //         'excel' => array(
    //             'delimiter' => ',',
    //             'quoteChar' => '"',
    //             'doubleQuote' => true,
    //             'skipInitialSpace' => false,
    //             'lineTerminator' => "\r\n",
    //             'quoteStyle' => Flavor::QUOTE_MINIMAL
    //         ),
    //         // these will be all the same as excel with the exception of delimiter
    //         'excel-tab' => array(
    //             'delimiter' => "\t"
    //         ),
    //         // I'm not sure if this is actually the correct set of parameters I just guessed
    //         'unix' => array(
    //             'delimiter' => ',',
    //             'quoteChar' => '"',
    //             'escapeChar' => '\\',
    //             'doubleQuote' => false,
    //             'skipInitialSpace' => false,
    //             'lineTerminator' => "\n",
    //             'quoteStyle' => Flavor::QUOTE_NONNUMERIC
    //         ),
    //         'unix-tab' => array(
    //             'delimiter' => "\t",
    //         )
    //     );
    //
    //     $excel = Flavor::create('excel');
    //     $excel_tab = Flavor::create('excel-tab');
    //     $unix = Flavor::create('unix');
    //     $unix_tab = Flavor::create('unix-tab');
    //
    //     $excel_flavor = new CSVelte\Flavor\Excel;
    //     $exceltab_flavor = new CSVelte\Flavor\ExcelTab;
    //     // $unix_flavor = new CSVelte\Flavor\Unix;
    //     // $unixtab_flavor = new CSVelte\Flavor\UnixTab;
    //
    //     $this->assertEquals($excel_flavor, $excel);
    //     $this->assertEquals($exceltab_flavor, $excel_tab);
    //     $this->assertEquals($unix_flavor, $unix);
    //     $this->assertEquals($unixtab_flavor, $unix_tab);
    //
    //     $this->assertEquals($excel_flavor, $excel->copy($expected['excel']));
    //     $this->assertEquals($exceltab_flavor, $excel_tab->copy($expected['excel-tab']));
    //     $this->assertEquals($unix_flavor, $unix->copy($expected['unix']));
    //     $this->assertEquals($unixtab_flavor, $unix_tab->copy($expected['unix-tab']));
    // }

    public function testConcreteFlavors()
    {
        $excel = new CSVelte\Flavor\Excel;
        $this->assertEquals("\r\n", $excel->lineTerminator);
        $this->assertEquals('"', $excel->quoteChar);
        $this->assertEquals(",", $excel->delimiter);
        $this->assertEquals(Flavor::QUOTE_MINIMAL, $excel->quoteStyle);
        $this->assertTrue($excel->doubleQuote);
        $this->assertFalse($excel->skipInitialSpace);
        $this->assertNull($excel->escapeChar);
    }

}
