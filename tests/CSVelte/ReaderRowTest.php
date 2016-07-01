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
}
