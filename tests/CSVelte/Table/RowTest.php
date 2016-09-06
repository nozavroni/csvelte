<?php
namespace CSVelteTest\Table;

use CSVelteTest\UnitTestCase;
use CSVelte\Table\AbstractRow;
use CSVelte\Table\HeaderRow;
use CSVelte\Table\Row;

/**
 * CSVelte\Table\Row Tests.
 * New Format for refactored tests -- see issue #11
 *
 * @package   CSVelte Unit Tests
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @todo      Move all of the tests from OldReaderTest.php into this class
 */
class RowTest extends UnitTestCase
{
    public function testInitializeRowWithStrings()
    {
        $row = new Row($expected = array(1, 'foo', 'bar', 'baz', 'biz', 25));
        $this->assertEquals($expected, $row->toArray());
    }

    public function testNewReaderRowAcceptsArray()
    {
        $row = new Row($expected = array(1, 'foo', 'bar', 'baz', 'biz', 25));
        $this->assertEquals($expected, $row->toArray());
    }

    public function testRowIsCountable()
    {
        $row = new Row($expected = array('foo', 'bar', 'baz'));
        $this->assertEquals(count($expected), $row->count());
        $this->assertEquals(count($expected), count($row));
    }

    public function testRowGetCurrentColumn()
    {
        $row = new Row($expected = array('foo', 'bar', 'baz'));
        $this->assertEquals($expected[0], $row->current());
    }

    public function testRowGetKey()
    {
        $row = new Row($expected = array('foo', 'bar', 'baz'));
        $this->assertSame(0, $row->key());
    }

    public function testRowNextReturnsNextAndMovesToNextColumn()
    {
        $row = new Row($expected = array('foo', 'bar', 'baz'));
        $this->assertSame($expected[1], $row->next());
    }

    public function testRowRewindResetsPointerToBeginningAndReturnsValue()
    {
        $row = new Row($expected = array('foo', 'bar', 'baz'));
        $this->assertSame($expected[0], $row->rewind());
    }

    public function testRowValidChecksWhetherCurrentIsValid()
    {
        $row = new Row($expected = array('foo', 'bar', 'baz'));
        $this->assertSame(true, $row->valid());
    }

    public function testIteratorImplementationIsWorking()
    {
        $row = new Row($expected = array('foo', 'bar', 'baz'));
        foreach($row as $col) {
            $this->assertEquals(current($expected), $col);
            next($expected);
        }
    }

    public function testIteratorWhileLoop()
    {
        $row = new Row($expected = array('foo', 'bar', 'baz'));
        while ($row->valid()) {
            $this->assertEquals(current($expected), $row->current());
            next($expected);
            $row->next();
        }
    }

    public function testJoinRow()
    {
        $row = new Row($expected = array('foo', 'bar', 'baz'));
        $this->assertEquals(implode(",", $expected), $row->join(","));
    }

    public function testOffsetExists()
    {
        $row = new Row($expected = array('foo', 'bar', 'baz'));
        $this->assertTrue($row->offsetExists($expected = 0));
        $this->assertTrue($row->offsetExists($expected = 1));
        $this->assertTrue($row->offsetExists($expected = 2));
        $this->assertFalse($row->offsetExists($expected = 3));
    }

    public function testOffsetGet()
    {
        $row = new Row($expected = array('foo', 'bar', 'baz'));
        $this->assertEquals('foo', $row->offsetGet(0));
        $this->assertEquals('bar', $row->offsetGet(1));
        $this->assertEquals('baz', $row->offsetGet(2));
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testOffsetGetThrowsExceptionOnUnknownOffset()
    {
        $row = new Row($expected = array('foo', 'bar', 'baz'));
        $row->offsetGet(3);
    }

    /**
     * @expectedException CSVelte\Exception\ImmutableException
     */
    public function testOffsetSetThrowsImmutableException()
    {
        $row = new Row($expected = array('foo', 'bar', 'baz'));
        $row->offsetSet(0, 'cannotchangeme!');
        // $this->assertFalse($row->offsetExists(3));
        // // create new offset
        // $row->offsetSet(3, 'beez');
        // $this->assertTrue($row->offsetExists(3));
        // $this->assertEquals($expected = 'beez', $row->offsetGet(3));
        // // overwrite existing offset
        // $row->offsetSet(1, 'eats you');
        // $this->assertEquals($row->offsetGet(1), 'eats you');
    }

    /**
     * @expectedException CSVelte\Exception\ImmutableException
     */
    public function testOffsetUnsetThrowsImmutableException()
    {
        $row = new Row($expected = array('foo', 'bar', 'baz'));
        $row->offsetUnset(0);
        // $this->assertTrue($row->offsetExists(1));
        // $row->offsetUnset(1);
        // $this->assertFalse($row->offsetExists(1));
        // $this->assertTrue($row->offsetExists(0));
        // $row->offsetUnset(0);
        // $this->assertFalse($row->offsetExists(0));
    }

    public function testReaderRowIsAccessableAsArray()
    {
        $row = new Row($expected = array('foo', 'bar', 'baz'));
        $this->assertEquals($expected[0], $row[0]);
        $this->assertEquals($expected[1], $row[1]);
        $this->assertEquals($expected[2], $row[2]);
    }

    public function testRowDoesntAllowAssociativeIndexesAndReIndexesNumericallyIfYouAttemptToUseThem()
    {
        $row = new Row($expected = array('foo' => 'bar', 'bar' => 'baz', 'baz' => 'foo'));
        $this->assertFalse($row->offsetExists('foo'));
        $this->assertFalse($row->offsetExists('bar'));
        $this->assertFalse($row->offsetExists('baz'));
        $this->assertEquals($expected['foo'], $row[0]);
        $this->assertEquals($expected['bar'], $row[1]);
        $this->assertEquals($expected['baz'], $row[2]);
    }

    /**
     * @expectedException CSVelte\Exception\ImmutableException
     */
    public function testRowValuesAreImmutable()
    {
        $row = new Row($expected = array('foo', 'bar', 'baz'));
        $row[0] = 'boo';
    }

    /**
     * @expectedException CSVelte\Exception\HeaderException
     * @expectedExceptionCode CSVelte\Exception\HeaderException::ERR_HEADER_COUNT
     */
    public function testIncorrectHeaderCountThrowsException()
    {
        $row = new Row($expected = array('foo', 'bar', 'baz'));
        $row->setHeaderRow(new HeaderRow(array('poop')));
    }

    // public function testRowsCanBeIndexedByBothOffsetAndColumnHeaderName()
    // {
    //     $header = new HeaderRow($headers = array('first name', 'last name', 'address1', '2nd address line', 'city', 'state', 'zipcode', 'phone', 'email', 'state', 'startdate', 'enddate'));
    //     $row = new Row($values = array('Luke', 'Visinoni', '1424 Some St.', 'Apt. #26', 'Chico', 'CA', '95926', '(530) 413-1234', 'luke.visinoni@gmail.com', '423', '12-28-2015', '04-21-2016'));
    //     $row->setHeaderRow($header);
    //     $this->assertEquals('Luke', $row->first_name);
    //     $this->assertEquals('Visinoni', $row->last_name);
    //     // if column starts with a number it will be converted to its "word" version
    //     $this->assertEquals('Apt. #26', $row->twondaddress_line);
    //     // if the header row contains duplicates, each one is appended with its column's index number (from 1)
    //     $this->assertEquals('CA', $row->state6);
    //     $this->assertEquals('423', $row->state10);
    //     // you can get all of the values (indexed by header names) by calling toAssoc()
    //     $this->assertEquals(array(), $row->toAssoc());
    //     // you can get all of the values (numerically indexed) by calling toArray()
    //     $this->assertEquals(array(), $row->toArray());
    // }

    // @todo handle duplicate header names
    public function testRowsCanBeIndexedByBothOffsetAndColumnHeaderName()
    {
        $header = new HeaderRow($headers = array('first name', 'last name', 'address1', '2nd address line', 'city', 'state', 'zipcode', 'phone', 'email', 'id', 'start-date', 'end [date]'));
        $row = new Row($values = array('Luke', 'Visinoni', '1424 Some St.', 'Apt. #26', 'Chico', 'CA', '95926', '(530) 413-1234', 'luke.visinoni@gmail.com', '423', '12-28-2015', '04-21-2016'));
        $row->setHeaderRow($header);
        $this->assertEquals('Luke', $row['first name']);
        $this->assertEquals('Visinoni', $row['last name']);
        $this->assertEquals('1424 Some St.', $row['address1']);
        $this->assertEquals('Apt. #26', $row['2nd address line']);
        $this->assertEquals('Chico', $row['city']);
        $this->assertEquals('CA', $row['state']);
        $this->assertEquals('95926', $row['zipcode']);
        $this->assertEquals('(530) 413-1234', $row['phone']);
        $this->assertEquals('luke.visinoni@gmail.com', $row['email']);
        $this->assertEquals('423', $row['id']);
        $this->assertEquals('12-28-2015', $row['start-date']);
        $this->assertEquals('04-21-2016', $row['end [date]']);
    }

    public function testShortRowGetsNullValuesForExtraColumns()
    {
        $expected_header = array('first','second','third','fourth','fifth','sixth');
        $expected_values = array('one','two','three','four','five','six');
        $expected_shortvalues = array_slice(array_combine($expected_header, $expected_values), 0, 3);
        $expected_shortvalues = array(
            'first' => 'one',
            'second' => 'two',
            'third' => 'three',
            'fourth' => null,
            'fifth' => null,
            'sixth' => null
        );

        $hrow = new HeaderRow($expected_header);
        $shortrow = new Row($expected_shortvalues);
        $shortrow->setHeaderRow($hrow);
        $this->assertEquals($expected_shortvalues, $shortrow->toArray());
    }

    public function testRowCanBeCastToString()
    {
        $row = new Row($cols = array('one', 'two', 'three'));
        $this->assertEquals($expected = "one,two,three", (string) $row);
    }

    // public function testSetHeadersDirectlyInConstructorArray()
    // {
    //     // test that you can set the headers within the array you pass to Row() itself
    // }

    public function testCastToArrayUsesHeadersIfAvailable()
    {
        $row = new Row($vals = array('aone','atwo','athree','anda','four'));
        $headers = new HeaderRow($keys = array('first','second','third','fourth','fifth'));
        $row->setHeaderRow($headers);
        $this->assertEquals($expected = array_combine($keys, $vals), $row->toArray());
    }
}
