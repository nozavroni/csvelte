<?php

use PHPUnit\Framework\TestCase;
use Carbon\Carbon;
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
    public function setUp()
    {
        date_default_timezone_set('America/Los_Angeles');
    }

    public function testTextCastToString()
    {
        $text = new Text($expected = 'I like text');
        $this->assertSame($expected, (string) $text);

    }

    public function testDataTypeFromTextToText()
    {
        $data = new Text($expected = 'I am some text.');
        $this->assertSame($expected, $data->getValue());
        $textDigits = new Text(423);
        $this->assertSame($expected = '423', $textDigits->getValue());
        $textTrue = new Text(true);
        $this->assertSame($expected = (string) Boolean::TRUE, $textTrue->getValue());
        $textFalse = new Text(false);
        $this->assertSame($expected = (string) Boolean::FALSE, $textFalse->getValue());
        $nulltext = new Text(null);
        $this->assertSame("", $nulltext->getValue(), "Ensure null value is converted to blank string.");
    }

    public function testDataTypeFromTextToNumeric()
    {
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
    }

    public function testDataTypeFromTextToBoolean()
    {
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
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBooleanAddBinarySetThrowsExceptionIfInvalidSet()
    {
        Boolean::addBinarySet(array('cat turds', 'kitty litter', 'almond roca for beagles'));
    }

    public function testNullDataTypeDoesntRequireOrAcceptAnInitValue()
    {
        $null = new Null();
        $this->assertSame(null, $null->getValue());

        // @todo I don't know if this should quietly ignore the init value or if it should bitch about it with an exception...
        $nonnull = new Null(25);
        $this->assertNull($nonnull->getValue());
    }

    public function testDateTimeDataTypeDefaultsToNowWhenGivenNoInitValue()
    {
        $testnow = Carbon::create(2016, 7, 21, 6, 11, 23, new \DateTimeZone(date_default_timezone_get()));
        Carbon::setTestNow($testnow);
        $dt = new DateTime();
        $this->assertInstanceOf(CSVelte\Table\DataType\DateTime::class, $dt);
        $this->assertEquals($testnow->toIso8601String(), (string) $dt);
    }

    public function testDateTimeDataTypeFromStringAssumesPHPDefaultTimeZone()
    {
        $this->assertEquals('America/Los_Angeles', date_default_timezone_get());
        $cdate = Carbon::create(1986, 4, 23, 2, 14, 0, new \DateTimeZone(date_default_timezone_get()));
        $dt = new DateTime('1986-04-23 2:14am');
        $this->assertInstanceOf(CSVelte\Table\DataType\DateTime::class, $dt);
        $this->assertEquals($cdate->getTimezone(), $dt->getValue()->getTimezone());
        $this->assertSame((string) $cdate, (string) $dt->getValue());

        date_default_timezone_set('America/New_York');
        $this->assertEquals('America/New_York', date_default_timezone_get());
        $cdate = Carbon::create(1986, 4, 23, 2, 14, 0, new \DateTimeZone(date_default_timezone_get()));
        $dt = new DateTime('1986-04-23 2:14am');
        $this->assertInstanceOf(CSVelte\Table\DataType\DateTime::class, $dt);
        $this->assertEquals($cdate->getTimezone(), $dt->getValue()->getTimezone());
        $this->assertSame((string) $cdate, (string) $dt->getValue());
    }

    public function testDateTimeDataTypeAcceptsVariousInitDateTypes()
    {
        $testnow = Carbon::create(2016, 7, 21, 6, 11, 23, new \DateTimeZone(date_default_timezone_get()));
        Carbon::setTestNow($testnow);

        $phpDateTimeObj = new \DateTime($phpDateTimeObjInit = '2004-06-05 2:30pm');
        $datetime = new DateTime($phpDateTimeObj);
        $this->assertEquals($expDTObjStr = '2004-06-05T14:30:00-0700', (string) $datetime); // @todo cant figure out why its 0700

        $phpNow = time();
        $phpNowFormatted = date(\DateTime::ISO8601, $phpNow);
        $datetime = new DateTime($phpNow);
        $this->assertEquals($phpNowFormatted, (string) $datetime);

        $dataTypeText = new Text($phpDateTimeObj->format(\DateTime::ISO8601));
        $datetime = new DateTime($dataTypeText);
        $this->assertEquals($expDTObjStr, (string) $datetime);

        $strNow = 'now';
        $datetime = new DateTime($strNow);
        $this->assertEquals(Carbon::now()->toIso8601String(), (string) $datetime);
    }

    public function testDateTimeDataTypeAllowsCustomStringConversionFormatUsingSameInterfaceAsCarbon()
    {
        $testnow = Carbon::create(2016, 7, 21, 6, 11, 23, new \DateTimeZone(date_default_timezone_get()));
        Carbon::setTestNow($testnow);

        $datetime = new DateTime();
        $this->assertEquals($testnow->toIso8601String(), (string) $datetime, 'DateTime data type\'s string format should default to ISO-8601.');

        DateTime::setToStringFormat(\DateTime::RSS);
        $this->assertEquals('Thu, 21 Jul 2016 06:11:23 -0700', $datetime->__toString(), 'DateTime data type\'s string format should be customizable using PHP\'s standard date() strings');
        DateTime::resetToStringFormat();
        $this->assertEquals('2016-07-21T06:11:23-0700', $datetime->__toString(), 'DateTime data type\'s string format should return to default when reset');
    }
}
