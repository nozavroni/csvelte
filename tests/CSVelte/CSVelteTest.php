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
    protected $dummydata = array(
        array('foo','bar','baz'),
        array('1','luke','visinoni'),
        array('2','margaret','kelly'),
        array('3','jerry','rafferty')
    );

    protected $tmpdir;

    public function setUp()
    {
        if (!is_dir($this->tmpdir = realpath(__DIR__ . '/../files') . '/temp')) {
            if (!mkdir($this->tmpdir, 0755)) {
                throw new \Exception('Cannot create temp dir');
            }
        }
    }

    public function tearDown()
    {
        @unlink(realpath(__DIR__ . '/../files/temp/deleteme.csv'));
        @rmdir(realpath(__DIR__ . '/../files/temp'));
    }

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
         $this->assertEquals($reader->current()->offsetGet('foo'), 'colbert');
     }

     // .. WRITER ...

     public function testCSVelteWriterCreatesFile()
     {
         $filename = $this->tmpdir . '/deleteme.csv';
         $writer = CSVelte::writer($filename);
         $this->assertEquals(4, $writer->writeRows($this->dummydata));
     }

     public function testCSVelteWriterCreatesFileWithFlavor()
     {
         $filename = $this->tmpdir . '/deleteme.csv';
         $writer = CSVelte::writer($filename, $flavor = new Flavor(array('delimiter' => "\t", 'lineTerminator' => "\n")));
         $this->assertEquals(4, $writer->writeRows($this->dummydata));
         $this->assertEquals("foo\tbar\tbaz\n1\tluke\tvisinoni\n2\tmargaret\tkelly\n3\tjerry\trafferty\n", file_get_contents($filename));
     }

     public function testExportMethod()
     {
         $filename = $this->tmpdir . '/deleteme.csv';
         $this->assertEquals(4, CSVelte::export($filename, $this->dummydata));
         $this->assertEquals("foo,bar,baz\r\n1,luke,visinoni\r\n2,margaret,kelly\r\n3,jerry,rafferty\r\n", file_get_contents($filename));
     }

     public function testExportMethodWithFlavor()
     {
         $filename = $this->tmpdir . '/deleteme.csv';
         $this->assertEquals(4, CSVelte::export($filename, $this->dummydata, $flavor = new Flavor(array('delimiter' => "\t", 'lineTerminator' => "\n"))));
         $this->assertEquals("foo\tbar\tbaz\n1\tluke\tvisinoni\n2\tmargaret\tkelly\n3\tjerry\trafferty\n", file_get_contents($filename));
     }

}
