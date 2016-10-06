<?php
namespace CSVelteTest;

use \ArrayIterator;
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

    public function testWriterWriteRowsAcceptsReader()
    {
        $out = new Stream($fname = $this->root->url() . '/reader2writer.csv', 'w');
        $writer = new Writer($out, ['lineTerminator' => "\n"]);
        $in = new Stream($this->getFilePathFor('headerDoubleQuote'), 'r+b');
        $reader = new Reader($in, ['lineTerminator' => "\n"]);
        $this->assertEquals(29, $writer->writeRows($reader));
        $this->assertEquals($this->getFileContentFor('headerDoubleQuote'), file_get_contents($fname));
    }

    public function testWriterWriteRowsAcceptsReaderToReformat()
    {
        $out = new Stream($fname = $this->root->url() . '/reformatme.csv', 'w');
        $in = new Stream($this->getFilePathFor('headerDoubleQuote'));
        $writer = new Writer($out, [
            'delimiter' => '|',
            'quoteStyle' => Flavor::QUOTE_NONNUMERIC,
            'lineTerminator' => "\r\n",
            'doubleQuote' => false,
            'escapeChar' => '\\'
        ]);
        $reader = new Reader($in, ['lineTerminator' => "\n"]);
        $writer->writeRows($reader);
        $lines = file($fname);
        $this->assertEquals("\"Bank Name\"|\"City\"|\"ST\"|\"CERT\"|\"Acquiring Institution\"|\"Closing Date\"|\"Updated Date\"\r\n", $lines[0]);
        $this->assertEquals("\"First CornerStone Bank\"|\"King of\n\\\"Prussia\\\"\"|\"PA\"|35312|\"First-Citizens Bank & Trust Company\"|\"6-May-16\"|\"25-May-16\"\r\n", $lines[1] . $lines[2]);
        $out->close();
        unlink($fname);
    }

    public function testWriterWritesCorrectOutputForFlavorWithQuoteAll()
    {
        $stream = new Stream('php://temp');
        $writer = new Writer($stream, ['quoteStyle' => Flavor::QUOTE_ALL]);
        $this->assertEquals(24, $writer->writeRow(['bacon','cheese','ham']));
        $this->assertEquals(3, $writer->writeRows([['monkey','lettuce','spam'],['table','hair','blam'],['chalk','talk','caulk']]));
        $stream->rewind();
        $this->assertEquals("\"bacon\",\"cheese\",\"ha", $stream->read(20));
    }

    public function testWriterWritesCorrectOutputForFlavorWithQuoteNone()
    {
        $stream = new Stream('php://temp');
        $writer = new Writer($stream, ['quoteStyle' => Flavor::QUOTE_NONE]);
        $this->assertEquals(18, $writer->writeRow(['bacon','cheese','ham']));
        $this->assertEquals(3, $writer->writeRows([['monkey','lettuce','spam'],['table','hair','blam'],['chalk','talk','caulk']]));
        $stream->rewind();
        $this->assertEquals("bacon,cheese,ham\r\nmo", $stream->read(20));
    }
}
