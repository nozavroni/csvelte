<?php
namespace CSVelteTest;

use CSVelte\Taster;
use CSVelte\Flavor;
use CSVelte\IO\Stream;

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
        $stream = Stream::streamize($this->getFileContentFor('headerDoubleQuote'));
        $taster = new Taster($stream);
        $flavor = $taster->lick();
        $this->assertEquals(Flavor::QUOTE_MINIMAL, $flavor->quoteStyle);

        $stream = Stream::streamize($this->getFileContentFor('noHeaderCommaQuoteAll'));
        $taster = new Taster($stream);
        $flavor = $taster->lick();
        $this->assertEquals(Flavor::QUOTE_ALL, $flavor->quoteStyle);

        $stream = Stream::streamize($this->getFileContentFor('headerCommaQuoteNonnumeric'));
        $taster = new Taster($stream);
        $flavor = $taster->lick();
        $this->assertEquals(Flavor::QUOTE_NONNUMERIC, $flavor->quoteStyle);

        $stream = Stream::streamize($this->getFileContentFor('noHeaderCommaNoQuotes'));
        $taster = new Taster($stream);
        $flavor = $taster->lick();
        $this->assertEquals(Flavor::QUOTE_NONE, $flavor->quoteStyle);
    }

    /**
     * @covers ::lickHeader()
     */
    public function testLickHeaderNowAcceptsReader()
    {
        $header_stream = Stream::streamize($this->getFileContentFor('headerDoubleQuote'));
        $no_header_stream = $stream = Stream::streamize($this->getFileContentFor('noHeaderCommaNoQuotes'));

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
        $input = Stream::streamize('');
        $taster = new Taster($input);
    }
}
