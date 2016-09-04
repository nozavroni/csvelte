<?php
namespace CSVelteTest;

use CSVelte\IO\File;
use CSVelte\IO\Stream;
use CSVelte\Reader;
use CSVelte\Flavor;
use CSVelte\Table\Row;
//use org\bovigo\vfs\vfsStream;
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
    /**
     * @covers ::__construct()
     */
    public function testReaderCanUseIOFileReadable()
    {
        $readable = new File($this->getFilePathFor('shortQuotedNewlines'));
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
        $in = new Stream($this->getFilePathFor('headerDoubleQuote'));
        $reader = new Reader($in, $flavor);
        $this->assertTrue($reader->hasHeader());
    }

    public function testReaderCurrent()
    {
        $flavor = new Flavor(array('header' => false));
        $reader = new Reader($this->getFileContentFor('noHeaderCommaNoQuotes'), $flavor);
        $this->assertInstanceOf($expected = Row::class, $reader->current());
        $this->assertEquals($expected = array("1","Eldon Base for stackable storage shelf platinum","Muhammed MacIntyre","3","-213.25","38.94","35","Nunavut","Storage & Organization","0.8"), $reader->current()->toArray());
    }

}
