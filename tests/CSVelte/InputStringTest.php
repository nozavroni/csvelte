<?php

use PHPUnit\Framework\TestCase;
use CSVelte\Input\String;

/**
 * CSVelte\Input\String Tests
 *
 * @package   CSVelte Unit Tests
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class InputStringTest extends TestCase
{
    public function testNewInputString()
    {
        $str = new String(file_get_contents(__DIR__ . '/../files/banklist.csv'));
        $this->assertEquals($expected = "Bank Name,City,", $str->read(15));
    }
}
