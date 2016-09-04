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
    /**
     * @covers ::lickQuotingStyle()
     */
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

    }
}
