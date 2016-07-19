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
    protected $testdata = array(
        array('id', 'title', 'summary', 'address', 'city', 'state', 'zip', 'notes'),
        array('1', 'This is a test title', '    And a summary that you want to read because it is so summarrific', '123 Address St.', 'Cityville', 'ST', '12345', 'Notes are for jerks and losers'),
        array('2', 'This has, a, comma or two', 'isn\'t this apostrophe pretty?', '321 Nough Rd', 'Nonestown', 'NO', '54321', '   Notes are the best place to put "text containing quotes" or even quotes containing "text".     '),
        array('3', "I\tlike ham soda", 'I\'m a silly little summary', '555 Silly Avenue', 'Eden', 'CA', '55651', 'These; ~notes~ `cont@in, character$: _that_ *are* /sometimes/ \'used\' -in- |p|ace| \\of\\ <commas> [when] {writing} #CSV %data.'),
        array('4', 'I\'m a "title"', 'Summarize <strong>this</strong>', '87-845 Something; cool Drive', 'Coolsville', 'CT', '68452-4257', 'These notes contain no special characters at all not even a period'),
        array('5', "This is the title of it", "A summary isn't to be taken lightly", '1122 Some Rd Apt #12-A', 'The Town', 'PP', '12223', "I decided to \n put a bunch of \r random \r\n\r\nline\nterminators in this notes\r\nfield. Weird, huh?"),
    );

    // public function testWriterHandlesQuotingCorrectly()
    // {
    //     $flavor = new Flavor(array(
    //         'header' => true,
    //         'doubleQuote' => true,
    //         'escapeChar' => null,
    //         'quoteChar' => '"',
    //         'lineTerminator' => "\n",
    //         'skipInitialSpace' => false,
    //         'delimiter' => ',',
    //         'quoteStyle' => Flavor::QUOTE_MINIMAL
    //     ));
    //     $handle = fopen('php://memory', 'w+');
    //     $out = new Stream($handle);
    //     $writer = new Writer($out, $flavor);
    //     $writer->writeRows($this->testdata);
    //     rewind($handle);
    //     $this->assertEquals($expected = "id,title,summary,address,city,state,zip,notes\n", fgets($handle), "Ensure the header row is handled correctly when flavor has header set to true");
    //     $this->assertEquals($expected = "1,This is a test title,    And a summary that you want to read because it is so summarrific,123 Address St.,Cityville,ST,12345,Notes are for jerks and losers\n", fgets($handle), "Ensure whitespace is preserved at the beginning of a column");
    //     $this->assertEquals($expected = "2,\"This has, a, comma or two\",isn't this apostrophe pretty?,321 Nough Rd,Nonestown,NO,54321,\"   Notes are the best place to put \"\"text containing quotes\"\" or even quotes containing \"\"text\"\".\n\"", fgets($handle), "Ensure writer adds double double-quotes when flavor designates doubleQuote=true and a column contains double-quotes.");
    //
    //     // dd(fgets($handle), false, "header");
    //     // dd(fgets($handle), false, "line 1");
    //     // dd(fgets($handle), false, "line 2");
    //     // dd(fgets($handle), false, "line 3");
    //     // dd(fgets($handle), false, "line 4");
    // }

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

    public function testWriteWriteRowsAcceptsReader()
    {
        $flavor = new Flavor(array('lineTerminator' => "\r\n"));
        $out = new Stream('php://memory');
        // $outFlavor = new Flavor([
        //     'lineTerminator' => "\n",
        //     'delimiter' => "|",
        //     'quoteChar' => '*',
        //     'escapeChar' => null,
        //     'doubleQuote' => true,
        //     'quoteStyle' => Flavor::QUOTE_NONNUMERIC
        // ]);
        // $out = new Stream('file:///Users/luke/test.csv');
        $writer = new Writer($out);
        $reader = new Reader(new CSVelte\Input\Stream('file://' . realpath(__DIR__ . '/../files/banklist.csv')), $flavor);
        $written_rows = $writer->writeRows($reader);
        $this->assertEquals(545, $written_rows);
    }

    public function testWriterUsesCorrectDelimiterAndLineTerminator()
    {
        $flavor = new Flavor(array('delimiter' => "|", 'lineTerminator' => "\n"));
        $handle = fopen('php://memory', 'w+');
        $out = new Stream($handle);
        $writer = new Writer($out, $flavor);
        $writer->writeRow(array('1','two','thr33'));
        rewind($handle);
        $this->assertEquals($expected = "1|two|thr33\n", fgets($handle));
    }


}
