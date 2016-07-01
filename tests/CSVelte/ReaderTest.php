<?php

use PHPUnit\Framework\TestCase;
use Mockery as m;
use Mockery\Adapter\PHPUnit\MockeryPHPUnitIntegration;
use CSVelte\Reader;
use CSVelte\Flavor;
use CSVelte\Contract\Readable;
use CSVelte\Input\Stream;

/**
 * CSVelte\Reader Tests
 *
 * @package   CSVelte Unit Tests
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class ReaderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testReaderWillAutomaticallyDetectFlavorIfNoneProvided()
    {
        $stub = $this->createMock(Readable::class);
        $stub->method('read')
             ->willReturn(file_get_contents(realpath(__DIR__ . '/../files/SampleCSVFile_2kb.csv')));
        $reader = new Reader($stub);
        $expected = new Flavor(array(
            'delimiter' => ',',
            'quoteChar' => '"',
            'quoteStyle' => Flavor::QUOTE_MINIMAL,
            'escapeChar' => '\\',
            'lineTerminator' => "\r\n"
        ));
        $this->assertInstanceOf(Flavor::class, $flavor = $reader->getFlavor());
        $this->assertEquals($expected, $flavor);
    }

    // it is useful for a CSV reader class to have a method for determining
    // whether or not its source input contains a header column, so this provides
    // one for convenience, although it is just a proxy to Taster with a sort of
    // cache so that the expensive Taster::lickHeader method is only ran when it
    // has to be (when input source changes or something)
    public function testReaderHasHeader()
    {
        $no_header_stub = $this->createMock(Readable::class);
        $no_header_stub->method('read')
             ->willReturn(file_get_contents(realpath(__DIR__ . '/../files/SampleCSVFile_2kb.csv')));
        $no_header_reader = new Reader($no_header_stub);
        $this->assertEquals(false, $no_header_reader->hasHeader());

        $header_stub = $this->createMock(Readable::class);
        $header_stub->method('read')
             ->willReturn(substr(file_get_contents(realpath(__DIR__ . '/../files/banklist.csv')), 0, 2500));
        $header_reader = new Reader($header_stub);
        $this->assertEquals(true, $header_reader->hasHeader());
    }

    public function testReaderStillRunsLickHeaderIfFlavorWasPassedInWithNullHasHeaderProperty()
    {
        $flavor = new Flavor();
        $reader = new Reader(new Stream('file://' . realpath(__DIR__ . '/../files/banklist.csv')), $flavor);
        $this->assertTrue($reader->hasHeader());
    }

    public function testReaderCurrent()
    {
        $flavor = new Flavor(null, array('hasHeader' => false));
        $reader = new Reader(new Stream(realpath(__DIR__ . '/../files/SampleCSVFile_2kb.csv')), $flavor);
        $this->assertInstanceOf($expected = Reader\Row::class, $reader->current());
        $this->assertEquals($expected = array("1","Eldon Base for stackable storage shelf, platinum","Muhammed MacIntyre","3","-213.25","38.94","35","Nunavut","Storage & Organization","0.8"), $reader->current()->toArray());
    }

    public function testReaderNext()
    {
        $flavor = new Flavor(null, array('hasHeader' => false));
        $reader = new Reader(new Stream(realpath(__DIR__ . '/../files/SampleCSVFile_2kb.csv')), $flavor);
        $this->assertEquals($expected = array("1","Eldon Base for stackable storage shelf, platinum","Muhammed MacIntyre","3","-213.25","38.94","35","Nunavut","Storage & Organization","0.8"), $reader->current()->toArray());
        $this->assertEquals($expected = array("2","1.7 Cubic Foot Compact \"\"Cube\"\" Office Refrigerators","Barry French","293","457.81","208.16","68.02","Nunavut","Appliances","0.58"), $reader->next()->toArray());
        $this->assertEquals($expected = array("2","1.7 Cubic Foot Compact \"\"Cube\"\" Office Refrigerators","Barry French","293","457.81","208.16","68.02","Nunavut","Appliances","0.58"), $reader->current()->toArray());
    }

    public function testReaderValid()
    {
        $flavor = new Flavor(null, array('hasHeader' => false));
        $reader = new Reader(new Stream(realpath(__DIR__ . '/../files/SampleCSVFile_2kb.csv')), $flavor);
        $this->assertEquals($expected = array("1","Eldon Base for stackable storage shelf, platinum","Muhammed MacIntyre","3","-213.25","38.94","35","Nunavut","Storage & Organization","0.8"), $reader->current()->toArray());
        $this->assertEquals($expected = array("2","1.7 Cubic Foot Compact \"\"Cube\"\" Office Refrigerators","Barry French","293","457.81","208.16","68.02","Nunavut","Appliances","0.58"), $reader->next()->toArray());
        // there are 10 lines in the source file...
        $reader->next(); // 7...
        $reader->next(); // 6...
        $reader->next(); // 5...
        $reader->next(); // 4...
        $reader->next(); // 3...
        $reader->next(); // 2...
        $reader->next(); // 1...
        $reader->next(); // 0...
        $reader->next(); // now we should have reached EOF...
        $this->assertFalse($reader->valid());
    }

    public function testReaderKey()
    {
        $flavor = new Flavor(null, array('hasHeader' => false));
        $reader = new Reader(new Stream(realpath(__DIR__ . '/../files/SampleCSVFile_2kb.csv')), $flavor);
        $this->assertEquals($expected = array("1","Eldon Base for stackable storage shelf, platinum","Muhammed MacIntyre","3","-213.25","38.94","35","Nunavut","Storage & Organization","0.8"), $reader->current()->toArray());
        $this->assertEquals($expected = 1, $reader->key());
        $this->assertEquals($expected = array("2","1.7 Cubic Foot Compact \"\"Cube\"\" Office Refrigerators","Barry French","293","457.81","208.16","68.02","Nunavut","Appliances","0.58"), $reader->next()->toArray());
        $this->assertEquals($expected = 2, $reader->key());
        // there are 10 lines in the source file...
        $reader->next(); // 7...
        $this->assertEquals($expected = 3, $reader->key());
        $reader->next(); // 6...
        $this->assertEquals($expected = 4, $reader->key());
        $reader->next(); // 5...
        $this->assertEquals($expected = 5, $reader->key());
        $reader->next(); // 4...
        $this->assertEquals($expected = 6, $reader->key());
        $reader->next(); // 3...
        $this->assertEquals($expected = 7, $reader->key());
        $reader->next(); // 2...
        $this->assertEquals($expected = 8, $reader->key());
        $reader->next(); // 1...
        $this->assertEquals($expected = 9, $reader->key());
        $reader->next(); // 0...
        $this->assertEquals($expected = 10, $reader->key());
        $reader->next(); // now we should have reached EOF...
        $this->assertEquals($expected = 10, $reader->key());
        $this->assertFalse($reader->valid());
    }
}
