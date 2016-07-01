<?php

use PHPUnit\Framework\TestCase;
use CSVelte\Reader;
use CSVelte\Reader\Row;

/**
 * CSVelte\Reader\Row Tests
 *
 * @package   CSVelte Unit Tests
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class ReaderRowTest extends TestCase
{
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
}
