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

    /**
     * @expectedException CSVelte\Exception\FileNotFoundException
     */
    public function testGenerateReaderWillThrowExceptionIfFileDoesNotExist()
    {
        $reader = CSVelte::reader(__DIR__ . '/../files/banklust.csv', $flavor);
    }

    /**
     * @todo use vfsStream lib to test that CSVelte::reader() checks for file readability
     */

     public function testCSVelteReaderCanBeUsedDirectlyInsideOfAForeachLoop()
     {
         $rows = 0;
         foreach (CSVelte::reader(__DIR__ . '/../files/banklist.csv') as $row) {
             $rows++;
         }
         $this->assertEquals(545, $rows);
     }

     public function testCSVelteReaderString()
     {
         $string = "foo,bar,baz\ncolbert,4,prez\nyou,2,silly\ntoocool,4,school\n";
         $reader = CSVelte::stringReader($string, new Flavor(array('lineTerminator' => "\n", 'header' => true)));
         $this->assertEquals($reader->current()[0], 'colbert');
     }

     // .. WRITER ...



}
