<?php
/**
 * CSVelteTest
 *
 * @package   CSVelte Unit Tests
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
use PHPUnit\Framework\TestCase;
use CSVelte\CSVelte;
use CSVelte\Reader;

class CSVelteTest extends TestCase
{
    public function testGenerateReaderObject()
    {
        $reader = CSVelte::reader(__DIR__ . '/../files/banklist.csv');
        $this->assertInstanceOf(Reader::class, $reader);
    }
}
