<?php
use PHPUnit\Framework\TestCase;
use CSVelte\Writer;
use CSVelte\Reader;
use CSVelte\Input\String;
use CSVelte\Output\Stream;
use CSVelte\Contract\Writable;
use CSVelte\Flavor;
/**
 * CSVelte\Writer Tests
 *
 * @package   CSVelte Unit Tests
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class WriterTest extends TestCase
{
    public function testWriterCustomFlavor()
    {
        $out = new Stream('php://memory');
        $writer = new Writer($out, $expectedFlavor = new Flavor(array('delimiter' => '|')));
        $this->assertSame($expectedFlavor, $writer->getFlavor());
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

    public function testWriterWriteWriteSingleRowUsingCSVReader()
    {
        $out = new Stream('php://memory');
        $in = new String($string = "foo,bar,baz\r\nbar,bin,boz\r\nboo,bat,biz\r\n");
        $writer = new Writer($out);
        $data = new Reader($in, new Flavor);
        $this->assertEquals(strlen("foo,bar,baz\r\n"), $writer->writeRow($data->current()));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testWriterWriteRowsThrowsExceptionIfPassedNonIterable()
    {
        $out = new Stream('php://memory');
        $writer = new Writer($out);
        $writer->writeRows('foo');
    }

    public function testWriterWriteMultipleRows()
    {
        $out = new Stream('php://memory');
        $writer = new Writer($out);
        $reader = new Reader(new CSVelte\Input\Stream('file://' . realpath(__DIR__ . '/../files/banklist.csv')));
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
}
