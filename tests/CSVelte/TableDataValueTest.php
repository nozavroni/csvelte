<?php

use PHPUnit\Framework\TestCase;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use CSVelte\Table\Data\Value;
use CSVelte\Table\Data\StringValue;
use CSVelte\Table\Data\NumberValue;
use CSVelte\Table\Data\IntegerValue;
use CSVelte\Table\Data\BooleanValue;
use CSVelte\Table\Data\DateTimeValue;
use CSVelte\Table\Data\DurationValue;
use CSVelte\Table\Data\GeoPointValue;

/**
 * CSVelte\Table\Data\Value Tests
 *
 * @package   CSVelte Unit Tests
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class TableDataValueTest extends TestCase
{
    public function setUp()
    {
        date_default_timezone_set('America/Los_Angeles');
    }

    // let's just start with an easy one, yeah?
    public function testCreateStringValueFromString()
    {
        $val = new StringValue($expected = "I am a string. I'm not all that exciting.");
        $this->assertEquals($expected, (string) $val);
        $this->assertTrue((bool) $val->isValid());
    }

    public function testCreateNumberValuesFromString()
    {
        $dollars = new NumberValue($expectedDollars = '$25,000.00');
        $this->assertEquals($expectedDollars, (string) $dollars);
        $this->assertSame(25000.00, $dollars->getValue());
        $this->assertTrue((bool) $dollars->isValid());

        $ahundred = new NumberValue($expectedAHundred = '100.001');
        $this->assertEquals($expectedAHundred, (string) $ahundred);
        $this->assertSame(100.001, $ahundred->getValue());
        $this->assertTrue((bool) $ahundred->isValid());

        $neghundred = new NumberValue($expectedNegHundred = '-100.001');
        $this->assertEquals($expectedNegHundred, (string) $neghundred);
        $this->assertSame(-100.001, $neghundred->getValue());
        $this->assertTrue((bool) $neghundred->isValid());

        $agrand = new NumberValue($expectedGrand = '1,000');
        $this->assertEquals($expectedGrand, (string) $agrand);
        $this->assertSame(1000, $agrand->getValue());
        $this->assertTrue((bool) $agrand->isValid());

        $exp = new NumberValue($expexp = '1.245e-12');
        $this->assertEquals($expexp, (string) $exp);
        $this->assertEquals(1.245e-12, $exp->getValue());
        $this->assertTrue((bool) $exp->isValid());

        // @todo Come back to this when you know how you're supposed to handle it
        // $percent = new NumberValue($expPercent = '1.25%');
        // $this->assertEquals($expPercent, (string) $percent);
        // $this->assertEquals(.0125, $percent->getValue());
        // $this->assertTrue((bool) $percent->isValid());
    }

    public function testCustomDecimalAndGroupCharForNumberValue()
    {
        $customDecimal = new NumberValue('1 423,25', ',', ' ');
        $this->assertEquals(1423.25, $customDecimal->getValue());
        $this->assertEquals('1 423,25', (string) $customDecimal);
        $this->assertTrue((bool) $customDecimal->isValid());
    }

    public function testCreateIntegerValuesFromString()
    {
        $int = new IntegerValue($intExp = '1000005');
        $this->assertEquals($intExp, (string) $int);
        $this->assertSame(1000005, $int->getValue());
        $this->assertTrue((bool) $int->isValid());
    }

    public function testCreateBooleanValuesFromString()
    {
        $bool = new BooleanValue($boolExp = 'true');
        $this->assertEquals($boolExp, (string) $bool);
        $this->assertSame(true, $bool->getValue());
        $this->assertTrue((bool) $bool->isValid());
    }

    public function testDataTypeFromTextToBoolean()
    {
        $boolean = new BooleanValue($expected = 'true');
        $this->assertSame(true, $boolean->getValue());
        $boolean = new BooleanValue($expected = 'false');
        $this->assertSame(false, $boolean->getValue());
        // $boolean = new BooleanValue($expected = 't');
        // $this->assertSame(true, $boolean->getValue());
        // $boolean = new BooleanValue($expected = 'f');
        // $this->assertSame(false, $boolean->getValue());
        $boolean = new BooleanValue($expected = 'yes');
        $this->assertSame(true, $boolean->getValue());
        $boolean = new BooleanValue($expected = 'no');
        $this->assertSame(false, $boolean->getValue());
        // $boolean = new BooleanValue($expected = 'y');
        // $this->assertSame(true, $boolean->getValue());
        // $boolean = new BooleanValue($expected = 'n');
        // $this->assertSame(false, $boolean->getValue());
        $boolean = new BooleanValue($expected = 'on');
        $this->assertSame(true, $boolean->getValue());
        $boolean = new BooleanValue($expected = 'off');
        $this->assertSame(false, $boolean->getValue());
        $boolean = new BooleanValue($expected = '1');
        $this->assertSame(true, $boolean->getValue());
        $boolean = new BooleanValue($expected = '0');
        $this->assertSame(false, $boolean->getValue());
        // $boolean = new BooleanValue($expected = '+');
        // $this->assertSame(true, $boolean->getValue());
        // $boolean = new BooleanValue($expected = '-');
        // $this->assertSame(false, $boolean->getValue());
        $binarySetList = BooleanValue::getBinarySetList();
        $binarySetCount = count($binarySetList);
        $this->assertEquals(++$binarySetCount, BooleanValue::addBinarySet('foo', 'bar'), "Ensure that BooleanValue::addBinarySet(), upon success, returns the new number of true/false string sets inside BooleanValue\$binaryStrings");
        $true = new BooleanValue('bar');
        $this->assertTrue($true->getValue(), "Ensure custom truthy value works as expected");
        $false = new BooleanValue('foo');
        $this->assertFalse($false->getValue(), "Ensure custom falsey value works as expected");
        $this->assertEquals(++$binarySetCount, BooleanValue::addBinarySet('[a-z]', '/st+u?f*/'));
        $true = new BooleanValue('/st+u?f*/');
        $this->assertTrue($true->getValue(), "Ensure that BooleanValue::addBinarySet() can handle literal special regex characters for truthy");
        $false = new BooleanValue('[a-z]');
        $this->assertFalse($false->getValue(), "Ensure that BooleanValue::addBinarySet() can handle literal special regex characters for falsey");
        $false = new BooleanValue('FaLsE');
        $this->assertFalse($false->getValue(), "Ensure true/false values are case insensitive");
        $false = new BooleanValue('TRUE');
        $this->assertTrue($false->getValue(), "Ensure true/false values are case insensitive");
    }

    public function testDateTimeDataTypeDefaultsToNowWhenGivenNoInitValue()
    {
        $testnow = Carbon::create(2016, 7, 21, 6, 11, 23, new \DateTimeZone(date_default_timezone_get()));
        Carbon::setTestNow($testnow);
        $dt = new DateTimeValue();
        $this->assertInstanceOf(DateTimeValue::class, $dt);
        $this->assertEquals($testnow->toIso8601String(), (string) $dt);
    }

    public function testDateTimeDataTypeFromStringAssumesPHPDefaultTimeZone()
    {
        $this->assertEquals('America/Los_Angeles', date_default_timezone_get());
        $cdate = Carbon::create(1986, 4, 23, 2, 14, 0, new \DateTimeZone(date_default_timezone_get()));
        $dt = new DateTimeValue('1986-04-23 2:14am');
        $this->assertInstanceOf(DateTimeValue::class, $dt);
        $this->assertEquals($cdate->getTimezone(), $dt->getValue()->getTimezone());
        $this->assertSame((string) $cdate, (string) $dt->getValue());

        date_default_timezone_set('America/New_York');
        $this->assertEquals('America/New_York', date_default_timezone_get());
        $cdate = Carbon::create(1986, 4, 23, 2, 14, 0, new \DateTimeZone(date_default_timezone_get()));
        $dt = new DateTimeValue('1986-04-23 2:14am');
        $this->assertInstanceOf(DateTimeValue::class, $dt);
        $this->assertEquals($cdate->getTimezone(), $dt->getValue()->getTimezone());
        $this->assertSame((string) $cdate, (string) $dt->getValue());
    }

    public function testDateTimeDataTypeAcceptsVariousInitDateTypes()
    {
        $testnow = Carbon::create(2016, 7, 21, 6, 11, 23, new \DateTimeZone(date_default_timezone_get()));
        Carbon::setTestNow($testnow);

        $phpDateTimeObj = new \DateTime($phpDateTimeObjInit = '2004-06-05 2:30pm');
        $datetime = new DateTimeValue($phpDateTimeObj);
        $this->assertEquals($expDTObjStr = '2004-06-05T14:30:00-0700', (string) $datetime); // @todo cant figure out why its 0700

        $phpNow = time();
        $phpNowFormatted = date(\DateTime::ISO8601, $phpNow);
        $datetime = new DateTimeValue($phpNow);
        $this->assertEquals($phpNowFormatted, (string) $datetime);

        $dataTypeString = new StringValue($phpDateTimeObj->format(\DateTime::ISO8601));
        $datetime = new DateTimeValue($dataTypeString);
        $this->assertEquals($expDTObjStr, (string) $datetime);

        $strNow = 'now';
        $datetime = new DateTimeValue($strNow);
        $this->assertEquals(Carbon::now()->toIso8601String(), (string) $datetime);
    }

    public function testDateTimeDataTypeAllowsCustomStringConversionFormatUsingSameInterfaceAsCarbon()
    {
        $testnow = Carbon::create(2016, 7, 21, 6, 11, 23, new \DateTimeZone(date_default_timezone_get()));
        Carbon::setTestNow($testnow);

        $datetime = new DateTimeValue();
        $this->assertEquals($testnow->toIso8601String(), (string) $datetime, 'DateTime data type\'s string format should default to ISO-8601.');

        DateTimeValue::setToStringFormat(\DateTime::RSS);
        $this->assertEquals('Thu, 21 Jul 2016 06:11:23 -0700', $datetime->__toString(), 'DateTime data type\'s string format should be customizable using PHP\'s standard date() strings');
        DateTimeValue::resetToStringFormat();
        $this->assertEquals('2016-07-21T06:11:23-0700', $datetime->__toString(), 'DateTime data type\'s string format should return to default when reset');
    }

    public function testDateTimeDataTypeIsValid()
    {
        $date = new DateTimeValue();
        $this->assertTrue($date->isValid());

        $date = new DateTimeValue('1986-4-23 6pm');
        $this->assertTrue($date->isValid());

        $date = new DateTimeValue('7pm');
        $this->assertTrue($date->isValid());

        $date = new DateTimeValue('October 7th, 2016');
        $this->assertTrue($date->isValid());

        $date = new DateTimeValue('tomorrow');
        $this->assertTrue($date->isValid());
    }

    /**
     * @todo I don't have time to tinker around with this right now I'll come back and perfect it later
     */
    public function testDurationDataTypeToStringUsesISO8601CompatibleDurationStringRatherThanHumanFriendlyFormat()
    {
        // I want my Duration data type to use P5Y4M2DT18H14M30S format rather
        // that the supposedly "human-readable" format it uses by default
        // $durStr = 'P3W'; // three weeks
        // $dur = new Duration($durStr);
        // $this->assertEquals('P3W', $dur->__toString());
    }

    public function testDurationDataTypeInitFromISO8601CompatibleDurationString()
    {
        $lotsofdays = 'P380D';
        $dur = new DurationValue($lotsofdays);
        $this->assertInstanceOf(CarbonInterval::class, $dur->getValue(), 'Duration data type should be represented internally by a Carbon\\CarbonInterval object.');
        // $this->assertEquals('54 weeks 2 days', (string) $dur->getValue(), 'Duration data type should normalize duration spec when storing internally (I would assume that 380 days would become 1 year, 2 weeks, and 1 day but it becomes 52 weeks, 2 days... ?)');
        // $this->assertEquals('P380D', $dur->getValue(), 'Duration data type should normalize duration spec when storing internally (I would assume that 380 days would become 1 year, 2 weeks, and 1 day but it becomes 52 weeks, 2 days... ?)');
        // in order to get normalized duration string, do __toString() on $dur->getValue()
        $this->assertEquals('54 weeks 2 days', (string) $dur->getValue(), 'Duration data type should normalize duration spec when storing internally (I would assume that 380 days would become 1 year, 2 weeks, and 1 day but it becomes 52 weeks, 2 days... ?)');
        $this->assertEquals('P380D', (string) $dur, 'Getting the string conversion of DurationValue will give you its init string back');

        // I tried to guess how __toString would normalize this and was waaayyy
        // off... it kind of has a mind of it's own I don't like it
        // @todo Write my own method to normalize duration strings and return
        // ISO-8601 duration string rather than this "human-readable" garbage
        $alottatime = 'P2Y6M8DT395M18S';
        $dur = new DurationValue($alottatime);
        // $this->assertEquals('2 years 6 months 1 week 1 day 395 minutes 18 seconds', $dur->__toString(), 'Duration data type should be able to handle complex durations as long as its increments are in order from largest to smallest');
        $this->assertEquals('P2Y6M8DT395M18S', $dur->__toString(), 'Duration data type should be able to handle complex durations as long as its increments are in order from largest to smallest');

        // @todo CarbonInterval doesn't support this type of DateInterval (created
        // from a diff between two dates) because without a start or end date
        // the interval object cannot reliably determine the user's intent for
        // intervals such as "3 months". That could mean mar, apr, may (31 + 30 + 31)
        // or it could mean sept, oct, nov (30 + 31 + 30). It could even mean
        // jan, feb, mar (which adds another piece of ambiguity due to leap year)
        // to get around this, I just take the TOTAL amount of days returned by
        // the diff and feed that to a new CarbonInterval object. But I'm not sure
        // that's going to always be reliable so I need to make sure to keep an
        // eye on this for bugs and think about other means of handling this...
        // Or NOT handling it...
        $stpattys = new Carbon('march 17, 2016 12:00am');
        $dayofreckoning = new Carbon('april 1, 2016 8:30:22');
        $darktimes = $stpattys->diff($dayofreckoning);
        $dur = new DurationValue($darktimes);
        $this->assertEquals('P15DT8H30M22S', $dur->getValue()->format("P%dDT%hH%iM%sS"));

        // test negative durations
        $sentence = new CarbonInterval(1, 8, 0, 14, 0, 0, 0);
        $sentence->invert = 1;
        $dur = new DurationValue($sentence);
        $this->assertEquals('-P1Y8M14D', (string) $dur->getValue()->format("%rP%yY%mM%dD"));

        $dur = new DurationValue('-P1Y8M14D');
        $this->assertEquals('-P1Y8M14D', (string) $dur->getValue()->format("%rP%yY%mM%dD"));
    }

    public function testDurationPattern()
    {
        $dur = new DurationValue('-P1Y8M14D');
        $this->assertTrue((bool) $dur->isValid());

        $dur = new DurationValue('P1Y2M14DT0S');
        $this->assertTrue($dur->isValid());

        $dur = new DurationValue('-PT9123723487S');
        $this->assertTrue($dur->isValid());
    }

    /**
     * @expectedException \Exception
     */
    public function testDurationDataTypeThrowsExceptionWhenInitializedWithISO8601DurationStringAndIncrementsAreInWrongOrder()
    {
        $dur = new DurationValue('P2D1YT10H');
    }

    public function testDurationDataTypeDoesAllowEmptyString()
    {
        $dur = new DurationValue("");
        $this->assertEquals("", $dur->__toString());
        $this->assertEquals("", $dur->getValue());
    }

    public function testDurationDataTypeDoesAllowNull()
    {
        $dur = new DurationValue(null);
        $this->assertEquals("", $dur->__toString());
        $this->assertEquals("", $dur->getValue());
    }

    public function testDurationDataTypeDoesAllowZero()
    {
        $dur = new DurationValue(0);
        $this->assertEquals("", $dur->__toString());
        $this->assertEquals("", $dur->getValue());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDurationDataTypeDoesntAllowBoolean()
    {
        $dur = new DurationValue(true);
    }

    /**
     * @todo There's still quite a bit I could do with GeoPoint but I'll come back
     */
    public function testGeoPointDataTypeFromString()
    {
        $geo = new GeoPointValue($strexp = '39.7518470, -121.8256950');
        $this->assertEquals(array(39.7518470, -121.8256950), $geo->getValue());
        $this->assertEquals($strexp, (string) $geo);
        $this->assertTrue((bool) $geo->isValid());
    }

    /**
     * @todo Worry about this later
     */
    // public function testStringValueWithFormats()
    // {
    //     $url = new StringValue($expected = "http://www.example.com/namespace/doc#hashtag", StringValue::FORMAT_URI);
    // }
}
