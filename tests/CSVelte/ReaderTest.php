<?php
namespace CSVelteTest;

use CSVelte\CSVelte;
use CSVelte\IO\Stream;
use CSVelte\Reader;
use CSVelte\Writer;
use CSVelte\Flavor;
use CSVelte\Table\Row;

/**
 * CSVelte\Reader Tests.
 * New Format for refactored tests -- see issue #11
 *
 * @package   CSVelte Unit Tests
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @todo      Move all of the tests from OldReaderTest.php into this class
 * @coversDefaultClass CSVelte\Reader
 */
class ReaderTest extends UnitTestCase
{
    public function testReaderCanAcceptArrayForFlavor()
    {
        $flavorArr = (new Flavor())->toArray();
        $flavorArr['delimiter'] = "\t";
        $flavorArr['lineTerminator'] = "\n";
        $flavorArr['quoteChar'] = "'";
        $flavorArr['quoteStyle'] = Flavor::QUOTE_ALL;
        $flavorArr['header'] = false;
        $reader = new Reader($this->getFilePathFor('veryShort'), $flavorArr);
        $this->assertEquals($flavorArr, $reader->getFlavor()->toArray());
    }

    /**
     * @covers ::__construct()
     */
    public function testReaderCanUseIOStreamForFileReadable()
    {
        $readable = Stream::open($this->getFilePathFor('shortQuotedNewlines'));
        $reader = new Reader($readable);
        $this->assertEquals(['foo','bar','baz'], $reader->current()->toArray());
        $this->assertEquals(['bin',"boz,bork\nlib,bil,ilb",'bon'], $reader->next()->toArray());
    }

    /**
     * @covers ::__construct()
     */
    public function testReaderCanUseStraightPHPString()
    {
        $readable = $this->getFileContentFor('shortQuotedNewlines');
        $reader = new Reader($readable);
        $this->assertEquals(['foo','bar','baz'], $reader->current()->toArray());
        $this->assertEquals(['bin',"boz,bork\nlib,bil,ilb",'bon'], $reader->next()->toArray());
    }

    public function testReaderTreatsQuotedNewlinesAsOneLine()
    {
        $flavor = new Flavor(array('quoteStyle' => Flavor::QUOTE_MINIMAL, 'lineTerminator' => "\n"), array('hasHeader' => false));
        $reader = new Reader($this->getFileContentFor('commaNewlineHeader'), $flavor);
        $line = $reader->current();
        $this->assertEquals($expected = "First CornerStone Bank,King of\nPrussia,PA,35312,First-Citizens Bank & Trust Company,6-May-16,25-May-16", $line->join(","));
    }

    public function testReaderWillAutomaticallyDetectFlavorIfNoneProvided()
    {
        $reader = new Reader($this->getFileContentFor('headerTabSingleQuotes'));
        $expected = new Flavor(array(
            'delimiter' => "\t",
            'quoteChar' => "'",
            'quoteStyle' => Flavor::QUOTE_MINIMAL,
            'escapeChar' => '\\',
            'lineTerminator' => "\n",
            'header' => true
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
        $no_header_reader = new Reader($this->getFileContentFor('noHeaderCommaNoQuotes'));
        $this->assertFalse($no_header_reader->hasHeader());
        $header_reader = new Reader($this->getFileContentFor('headerDoubleQuote'));
        $this->assertTrue($header_reader->hasHeader());
    }

    public function testReaderStillRunsLickHeaderIfFlavorWasPassedInWithNullHasHeaderProperty()
    {
        $flavor = new Flavor(['header' => null, 'lineTerminator' => "\n"]);
        $in = Stream::open($this->getFilePathFor('headerDoubleQuote'));
        $reader = new Reader($in, $flavor);
        $this->assertTrue($reader->hasHeader());
    }

    public function testReaderCurrent()
    {
        $flavor = new Flavor(array('header' => false, 'lineTerminator' => "\n"));
        $reader = new Reader($this->getFileContentFor('noHeaderCommaNoQuotes'), $flavor);
        $this->assertInstanceOf($expected = Row::class, $reader->current());
        $this->assertEquals($expected = array("1","Eldon Base for stackable storage shelf platinum","Muhammed MacIntyre","3","-213.25","38.94","35","Nunavut","Storage & Organization","0.8"), $reader->current()->toArray());
    }

    public function testReaderToArray()
    {
        $reader = new Reader($this->getFileContentFor('veryShort'));
        $this->assertInternalType("array", $arr = $reader->toArray());
        // "foo,bar,baz\nbin,boz,bork\nlib,bil,ilb\n"
        $this->assertEquals([
            1 => ["foo","bar","baz"],
            2 => ["bin","boz","bork"],
            3 => ["lib","bil","ilb"]
        ], $arr);
    }

    // if you need to get a stream for a uri/filename you need to use Stream::open()
    // or instantiate a resource manually
    // public function testReaderConstructorWillTreatAllTextAsCSVData()
    // {
    //     $reader = new Reader($this->getFilePathFor('veryShort'));
    //     $this->assertEquals(["vfs:","","root",'testfiles','veryShort.csv'], $reader->current()->toArray());
    //     $reader = new Reader("i,am,a\nvry,short,csv\nfile,yes,sir\n");
    //     $this->assertEquals(["i","am","a"], $reader->current()->toArray());
    // }

    public function testReaderFilteredIterator()
    {
        $reader = new Reader($this->getFileContentFor('commaNewlineHeader'));
        $reader->addFilter(function($row){
            return $row['CERT'] > 55000;
        })->addFilter(function($row){
            return stripos($row['Bank Name'], 'bank') !== false;
        });
        foreach ($reader->filter() as $line_no => $row) {
            $this->assertGreaterThan(55000, $row['CERT'], "Ensure \"CERT\" field from row #{$line_no} is greater than 55000.");
            $this->assertContains('bank', $row['Bank Name'], "Ensure \"Bank Name\" field from row #{$line_no} contains the word \"bank\".", true);
        }
        $this->assertCount(4, iterator_to_array($reader->filter()));
    }

    public function testReaderFilteredIteratorWithMultipleFiltersAddedAtOnce()
    {
        $reader = new Reader($this->getFileContentFor('commaNewlineHeader'));
        $reader->addFilters([function($row){
            return $row['CERT'] > 55000;
        }, function($row){
            return stripos($row['Bank Name'], 'bank') !== false;
        }]);
        foreach ($reader->filter() as $line_no => $row) {
            $this->assertGreaterThan(55000, $row['CERT'], "Ensure \"CERT\" field from row #{$line_no} is greater than 55000.");
            $this->assertContains('bank', $row['Bank Name'], "Ensure \"Bank Name\" field from row #{$line_no} contains the word \"bank\".", true);
        }
        $this->assertCount(4, iterator_to_array($reader->filter()));
    }

    public function testFilteredIteratorHasToArrayMethod()
    {
        $reader = CSVelte::reader($this->getFilePathFor('commaNewlineHeader'));
        $reader->addFilter(function($row){
            return ($row['CERT'] < 40000);
        });
        $this->assertInternalType("array", $reader->filter()->toArray());
        $reader->addFilter(function($row){
            return $row['Bank Name'] == "Northern Star Bank";
        });
        $this->assertInternalType("array", $arr = $reader->filter()->toArray());
        $this->assertInternalType("array", current($arr));
        $this->assertCount(1, $oneArr = $reader->filter()->toArray());
        $this->assertEquals([13 => [
            "Bank Name" => "Northern Star Bank",
            "City" => "Mankato",
            "ST" => "MN",
            "CERT" => "34983",
            "Acquiring Institution" => "BankVista",
            "Closing Date" => "19-Dec-14",
            "Updated Date" => "6-Jan-16"
        ]], $oneArr);
    }

    public function testReaderKeyReturnsLine()
    {
        $reader = new Reader($this->getFileContentFor('commaNewlineHeader'));
        // @todo This should be 1 since the first row was the header, but ill get to that later
        $this->assertEquals(2, $reader->key());
    }

    public function testEscapeCharacterEscapesQuotesWithinQuotes()
    {
        $csv = 'Bank Name,City,ST,CERT,Acquiring Institution,Closing Date,Updated Date
"Sampson \\"The King of Prussia\\" Jahosefat",Peepville,PA,35312,"First-Citizens Bank & \\"Trust\\" Company",6-May-16,25-May-16
Trust Company Bank,Memphis,TN,9956,"The Bank of \\"Gayette\\" County",29-Apr-16,25-May-16
North Milwaukee State Bank,Milwaukee,WI,20364,First-Citizens Bank & Trust Company,11-Mar-16,16-Jun-16
Hometown National Bank,Longview,WA,35156,Twin City Bank,2-Oct-15,13-Apr-16
The Bank of Georgia,Peachtree City,GA,35259,Fidelity Bank,2-Oct-15,13-Apr-16
';
        $flvr = [
            'delimiter' => ',',
            'lineTerminator' => '
',
            'escapeChar' => null,
            'doubleQuote' => true,
            'quoteStyle' => Flavor::QUOTE_MINIMAL,
            'quoteChar' => '"',
            'header' => true
        ];
        $reader = new Reader($csv, $flvr);
        $row = $reader->current();
        $this->assertEquals('Sampson \\"The King of Prussia\\" Jahosefat', $row['Bank Name']);
        $this->assertEquals('First-Citizens Bank & \\"Trust\\" Company', $row['Acquiring Institution']);

        // change the flavor to make it work...
        $flvr['doubleQuote'] = false;
        $flvr['escapeChar'] = '\\';
        $reader = new Reader($csv, $flvr);
        $row = $reader->current();
        $this->assertEquals('Sampson "The King of Prussia" Jahosefat', $row['Bank Name']);
        $this->assertEquals('First-Citizens Bank & "Trust" Company', $row['Acquiring Institution']);
    }

}
