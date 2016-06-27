<?php

use PHPUnit\Framework\TestCase;
use Mockery as m;
use Mockery\Adapter\PHPUnit\MockeryPHPUnitIntegration;
use CSVelte\Reader;
use CSVelte\Flavor;
use CSVelte\Input\InputInterface;

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
        $stub = $this->createMock(InputInterface::class);
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
}
