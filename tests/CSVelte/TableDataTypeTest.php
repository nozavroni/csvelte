<?php

use PHPUnit\Framework\TestCase;
use CSVelte\Table\Data;
use CSVelte\Table\DataType\Numeric;
use CSVelte\Table\DataType\Text;
use CSVelte\Table\DataType\Boolean;
use CSVelte\Table\DataType\DateTime;
use CSVelte\Table\DataType\Duration;
use CSVelte\Table\DataType\Null;

/**
 * CSVelte\Table\DataType Tests
 * DataTypes, as the name implies, represent the data type of a particular data
 * cell or column or item, whatever you want to call it. I have a set of pre-
 * defined datatypes and then a custom datatype that can be customized using a
 * regular expression for validation and type detection/conversion from a string.
 *
 * @package   CSVelte Unit Tests
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class TableDataTypeTest extends TestCase
{
    public function testDataTypeFromText()
    {
        $data = new Text($expected = 'I am some text.');
        $this->assertSame($expected, $data->getValue());

        $numeric = new Numeric($expected = '1000000');
        $this->assertSame((int) $expected, $numeric->getValue());
        $numericfancy = new Numeric('1,000,000');
        $this->assertSame((int) $expected, $numericfancy->getValue());
        $numericdecimal = new Numeric($expecteddecimal = '1000.14');
        $this->assertSame((float) $expecteddecimal, $numericdecimal->getValue());
        $numericfancydecimal = new Numeric($expectedfancydecimal = '1,000.14');
        $this->assertSame(1000.14, $numericfancydecimal->getValue());
        $currency = new Numeric($expcurrency = '$1,000.14');
        $this->assertSame(1000.14, $currency->getValue());

        $boolean = new Boolean($expected = 'true');
        $this->assertSame(true, $boolean->getValue());
        $boolean = new Boolean($expected = 'false');
        $this->assertSame(false, $boolean->getValue());
        $boolean = new Boolean($expected = 'yes');
        $this->assertSame(true, $boolean->getValue());
        $boolean = new Boolean($expected = 'no');
        $this->assertSame(false, $boolean->getValue());
        $boolean = new Boolean($expected = 'on');
        $this->assertSame(true, $boolean->getValue());
        $boolean = new Boolean($expected = 'off');
        $this->assertSame(false, $boolean->getValue());
        $boolean = new Boolean($expected = '1');
        $this->assertSame(true, $boolean->getValue());
        $boolean = new Boolean($expected = '0');
        $this->assertSame(false, $boolean->getValue());
        $boolean = new Boolean($expected = '+');
        $this->assertSame(true, $boolean->getValue());
        $boolean = new Boolean($expected = '-');
        $this->assertSame(false, $boolean->getValue());
        $binarySetList = Boolean::getBinarySetList();
        $binarySetCount = count($binarySetList);
        $this->assertEquals(++$binarySetCount, Boolean::addBinarySet(array('foo', 'bar')), "Ensure that Boolean::addBinarySet(), upon success, returns the new number of true/false string sets inside Boolean\$binaryStrings");
        $true = new Boolean('bar');
        $this->assertTrue($true->getValue(), "Ensure custom truthy value works as expected");
        $false = new Boolean('foo');
        $this->assertFalse($false->getValue(), "Ensure custom falsey value works as expected");
        $this->assertEquals(++$binarySetCount, Boolean::addBinarySet(array($falsey = '[a-z]', $truthy = '/st+u?f*/')));
        $true = new Boolean($truthy);
        $this->assertTrue($true->getValue(), "Ensure that Boolean::addBinarySet() can handle literal special regex characters for truthy");
        $false = new Boolean($falsey);
        $this->assertFalse($false->getValue(), "Ensure that Boolean::addBinarySet() can handle literal special regex characters for falsey");
        $this->assertEquals(++$binarySetCount, Boolean::addBinarySet(array(Boolean::TRUE => 'tweedles', Boolean::FALSE => 'needles')));
        $true = new Boolean('tweedles');
        $this->assertTrue($true->getValue(), "Ensure that Boolean::addBinarySet() accepts an associative array with Boolean class constants as keys (using true value).");
        $false = new Boolean('needles');
        $this->assertTrue($true->getValue(), "Ensure that Boolean::addBinarySet() accepts an associative array with Boolean class constants as keys (using false value).");

        // @todo Test that Boolean::addBinarySet() accepts either a two-element array in the form of [false, true] or an associative array in the form of ['true' => 'truevalue', 'false' => 'falsevalue']. It also should take [Boolean::TRUE => 'truthvalue', Boolean::FALSE => 'falsevalue']

    }

    public function testBooleanAddBinarySetThrowsExceptionIfInvalidSet()
    {

    }

    public function testNullDataTypeDoesntHaveAValue()
    {

    }
}
