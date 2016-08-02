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
use CSVelte\Flavor;

class CSVelteTest extends TestCase
{
    public function testGenerateReaderObject()
    {
        $reader = CSVelte::reader(__DIR__ . '/../files/banklist.csv');
        $this->assertInstanceOf(Reader::class, $reader);
    }

    public function testGenerateReaderObjectWithCustomFlavor()
    {
        $flavor = new Flavor(array('delimiter' => '!', 'header' => false));
        $reader = CSVelte::reader(__DIR__ . '/../files/banklist.csv', $flavor);
        $this->assertInstanceOf(Flavor::class, $flavor);
        $this->assertSame($flavor, $reader->getFlavor());
    }
}
