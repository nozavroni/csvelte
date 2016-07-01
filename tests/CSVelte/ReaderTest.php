<?php

use PHPUnit\Framework\TestCase;
use Mockery as m;
use Mockery\Adapter\PHPUnit\MockeryPHPUnitIntegration;
use CSVelte\Reader;
use CSVelte\Flavor;
use CSVelte\Contract\Readable;

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
}
