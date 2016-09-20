<?php
namespace CSVelteTest;

use CSVelte\Taster;
use CSVelte\Flavor;
use CSVelte\IO\Stream;
use \SplFileObject;
use function CSVelte\streamize;

/**
 * CSVelte\Taster Tests.
 * New Format for refactored tests -- see issue #11
 *
 * @package   CSVelte Unit Tests
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @todo      Move all of the tests from OldReaderTest.php into this class
 * @coversDefaultClass CSVelte\Taster
 */
class TasterTest extends UnitTestCase
{
    public function testLickQuotingStyleDoesntNeedSampleInParams()
    {
        $stream = streamize($this->getFileContentFor('headerDoubleQuote'));
        $taster = new Taster($stream);
        $flavor = $taster->lick();
        $this->assertEquals(Flavor::QUOTE_MINIMAL, $flavor->quoteStyle);

        $stream = streamize($this->getFileContentFor('noHeaderCommaQuoteAll'));
        $taster = new Taster($stream);
        $flavor = $taster->lick();
        $this->assertEquals(Flavor::QUOTE_ALL, $flavor->quoteStyle);

        $stream = streamize($this->getFileContentFor('headerCommaQuoteNonnumeric'));
        $taster = new Taster($stream);
        $flavor = $taster->lick();
        $this->assertEquals(Flavor::QUOTE_NONNUMERIC, $flavor->quoteStyle);

        $stream = streamize($this->getFileContentFor('noHeaderCommaNoQuotes'));
        $taster = new Taster($stream);
        $flavor = $taster->lick();
        $this->assertEquals(Flavor::QUOTE_NONE, $flavor->quoteStyle);
    }

    /**
     * @covers ::lickHeader()
     */
    public function testLickHeaderNowAcceptsReader()
    {
        $header_stream = streamize($this->getFileContentFor('headerDoubleQuote'));
        $no_header_stream = $stream = streamize($this->getFileContentFor('noHeaderCommaNoQuotes'));

        $header_taster = new Taster($header_stream);
        $header_flavor = $header_taster->lick();
        $this->assertTrue($header_flavor->header);

        $no_header_taster = new Taster($no_header_stream);
        $no_header_flavor = $no_header_taster->lick();
        $this->assertFalse($no_header_flavor->header);
    }

    /**
     * @expectedException CSVelte\Exception\TasterException
     * @expectedExceptionCode CSVelte\Exception\TasterException::ERR_INVALID_SAMPLE
     */
    public function testTasterThrowsExceptionIfPassedInputWithNoData()
    {
        $input = streamize('');
        $taster = new Taster($input);
    }

    public function testTasterInvokeMethodIsAliasToLickMethod()
    {
        $stream = Stream::open($this->getFilePathFor('headerDoubleQuote'));
        $taster = new Taster($stream);
        $flavor = $taster();
        $this->assertInstanceOf(Flavor::class, $flavor);
    }

    public function testTasterInvokeWithSplFileObject()
    {
        $fileObj = new SplFileObject($this->getFilePathFor('headerTabSingleQuotes'));
        $taster = new Taster(streamize($fileObj));
        $flavor = $taster();
        $this->assertEquals("\t", $flavor->delimiter);
        $this->assertEquals("\n", $flavor->lineTerminator);
        $this->assertEquals(Flavor::QUOTE_MINIMAL, $flavor->quoteStyle);
        $this->assertTrue($flavor->doubleQuote);
        $this->assertEquals("'", $flavor->quoteChar);
    }
}
