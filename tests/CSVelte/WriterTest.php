<?php
namespace CSVelteTest;

use \ArrayIterator;
use CSVelte\IO\File;
use CSVelte\IO\Stream;
use CSVelte\Writer;
use CSVelte\Reader;
use CSVelte\Flavor;
use CSVelte\Table\Row;

/**
 * CSVelte\Writer Tests.
 * New Format for refactored tests -- see issue #11
 *
 * @package   CSVelte Unit Tests
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @todo      Move all of the tests from OldReaderTest.php into this class
 * @coversDefaultClass CSVelte\Writer
 */
class WriterTest extends UnitTestCase
{
    protected $sampledata = [
        ['1','luke','visinoni','luke.visinoni@gmail.com'],
        ['2','bob','smith','bob.smith@yahoo.com'],
        ['3','larry','johnson','ljh@johnsonhome.com'],
    ];

    public function testWriterCustomFlavor()
    {
        $out = new Stream('php://memory');
        $writer = new Writer($out, $expectedFlavor = new Flavor(array('delimiter' => '|')));
        $this->assertSame($expectedFlavor, $writer->getFlavor());
    }

    public function testWriterCanAcceptArrayForFlavor()
    {
        $flavorArr = (new Flavor())->toArray();
        $flavorArr['delimiter'] = "\t";
        $flavorArr['lineTerminator'] = "\n";
        $flavorArr['quoteChar'] = "'";
        $flavorArr['quoteStyle'] = Flavor::QUOTE_ALL;
        $writer = new Writer(new Stream($this->getFilePathFor('veryShort'), 'a+'), $flavorArr);
        $this->assertEquals($flavorArr, $writer->getFlavor()->toArray());
    }

    public function testWriterWriteWriteSingleRowUsingArray()
    {
        $out = new Stream('php://memory');
        $writer = new Writer($out);
        $data = array('one','two', 'three');
        $this->assertEquals(strlen(implode(',', $data)) + strlen("\r\n"), $writer->writeRow($data));
    }

    public function testWriterWriteWriteSingleRowUsingIterator()
    {
        $out = new Stream('php://memory');
        $writer = new Writer($out);
        $data = new ArrayIterator(array('one','two', 'three'));
        $this->assertEquals(strlen(implode(',', $data->getArrayCopy())) + strlen("\r\n"), $writer->writeRow($data));
    }

    public function testWriterWritesHeaderRow()
    {
        $temp = new Stream('php://temp');
        $writer = new Writer($temp);
        $writer->setHeaderRow(['id','firstname','lastname','email']);
        $writer->writeRows($this->sampledata);
        $temp->seek(0);
        $this->assertEquals("id,firstname,lastname,email\r\n1,luke,visinoni,luke.visinoni@gmail.com\r\n2,bob,smith,bob.smith@yahoo.com\r\n3,larry,johnson,ljh@johnsonhome.com\r\n", $temp->read(200));
    }

    /**
     * @expectedException CSVelte\Exception\WriterException
     */
    public function testWriterThrowsExceptionIfUserAttemptsToSetHeaderAfterRowsHaveBeenWritten()
    {
        $writer = new Writer(new Stream('php://temp'));
        $writer->writeRow(array('foo','bar','baz'));
        $writer->setHeaderRow(array('this','shouldnt','work'));
    }

    public function testWriterWriteWriteSingleRowUsingCSVReader()
    {
        $reader = new Reader(new Stream($this->getFilePathFor('veryShort')));
        $writer = new Writer($stream = new Stream('php://temp'));
        $writer->writeRow($reader->current());
        $stream->rewind();
        $this->assertEquals("foo,bar,baz\r\n", $stream->read(15));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testWriterWriteRowsThrowsExceptionIfPassedNonIterable()
    {
        $out = new Stream('php://temp');
        $writer = new Writer($out);
        $writer->writeRows('foo');
    }

    public function testWriterWriteMultipleRows()
    {
        $out = new Stream('php://temp');
        $writer = new Writer($out);
        $reader = new Reader(new Stream($this->getFilePathFor('commaNewlineHeader')));
        $data = array();
        $i = 0;
        foreach ($reader as $row) {
            if ($i == 10) break;
            $data []= $row->toArray();
            $i++;
        }
        $written_rows = $writer->writeRows($data);
        $this->assertEquals(10, $written_rows);
    }

    // public function testWriteWriteRowsAcceptsReader()
    // {
    //     $out = new Stream('php://temp');
    //     $writer = new Writer($out);
    //     $reader = new Reader(new Stream($this->getFilePathFor('headerDoubleQuote')));
    //     $this->assertEquals(10, $writer->writeRows($reader));
    // }
}
