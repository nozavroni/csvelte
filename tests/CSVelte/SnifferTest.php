<?php
/**
 * CSVelte: Slender, elegant CSV for PHP
 *
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV
 * standardization efforts, CSVelte was written in an effort to take all the
 * suck out of working with CSV.
 *
 * @copyright Copyright (c) 2018 Luke Visinoni
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   See LICENSE file (MIT license)
 */
namespace CSVelteTest;

use CSVelte\Dialect;
use CSVelte\Sniffer;

use CSVelte\Sniffer\SniffDelimiterByConsistency;
use CSVelte\Sniffer\SniffDelimiterByDistribution;
use CSVelte\Sniffer\SniffHeaderByDataType;
use CSVelte\Sniffer\SniffLineTerminatorByCount;
use CSVelte\Sniffer\SniffQuoteAndDelimByAdjacency;
use CSVelte\Sniffer\SniffQuoteStyle;
use function CSVelte\to_stream;

class SnifferTest extends UnitTestCase
{
    public function testInstantiateWithCustomDelimiterSet()
    {
        $sniffer = new Sniffer(to_stream());
        $this->assertSame([',', "\t", ';', '|', ':', '-', '_', '#', '/', '\\', '$', '+', '=', '&', '@'], $sniffer->getPossibleDelimiters());
        $sniffer->setPossibleDelimiters($delims = [',', "\t", '|']);
        $this->assertSame($delims, $sniffer->getPossibleDelimiters());
        $sniffer = new Sniffer(to_stream(), $delims);
        $this->assertSame($delims, $sniffer->getPossibleDelimiters());
    }

    public function testSniffLineTerminatorByCount()
    {
        $nl = to_stream($this->getFileContentFor('commaNewlineHeader'));
        $nlcr = to_stream(str_replace("\n", "\r\n", $this->getFileContentFor('commaNewlineHeader')));
        $cr = to_stream(str_replace("\n", "\r", $this->getFileContentFor('commaNewlineHeader')));

        $sniffer = new SniffLineTerminatorByCount;
        $this->assertSame("\n", $sniffer->sniff($nl->read(1500)));
        $this->assertSame("\r\n", $sniffer->sniff($nlcr->read(1500)));
        $this->assertSame("\r", $sniffer->sniff($cr->read(1500)));
    }

    public function testSniffQuoteAndDelimByAdjacency()
    {
        $data = $this->getFileContentFor('noHeaderCommaQuoteAll');
        $sniffer = new SniffQuoteAndDelimByAdjacency([
            'lineTerminator' => "\n"
        ]);
        $this->assertSame(['"', ','], $sniffer->sniff($data));
    }

    public function testSniffDelimiterByConsistency()
    {
        $data = $this->getFileContentFor('headerTabSingleQuotes');
        $sniffer = new SniffDelimiterByConsistency([
            'lineTerminator' => "\n",
            'delimiters' => [',', "\t", ';', '|', ':', '-', '_', '#', '/', '\\', '$', '+', '=', '&', '@']
        ]);
        $this->assertSame(["\t"], $sniffer->sniff($data));
    }

    public function testSniffDelimiterByConsistencyReturnsTie()
    {
        $data = $this->getFileContentFor('commaDelimTie');
        $sniffer = new SniffDelimiterByConsistency([
            'lineTerminator' => "\n",
            'delimiters' => [',', "\t", ';', '|', ':', '-', '_', '#', '/', '\\', '$', '+', '=', '&', '@']
        ]);
        $this->assertSame([',',':','/','@'], $sniffer->sniff($data));
    }

    public function testSniffDelimiterByDistribution()
    {
        $data = $this->getFileContentFor('commaDelimTie');
        $sniffer = new SniffDelimiterByDistribution([
            'lineTerminator' => "\n",
            'delimiters' => [',', ':', '/', '@']
        ]);
        $this->assertSame(',', $sniffer->sniff($data));
    }

    public function testSniffQuoteStyle()
    {
        $all  = $this->getFileContentFor('noHeaderCommaQuoteAll');
        $none = $this->getFileContentFor('noHeaderCommaNoQuotes');
        $min  = $this->getFileContentFor('noHeaderCommaQuoteMinimal');
        $nan  = $this->getFileContentFor('headerCommaQuoteNonnumeric');
        $sniffer = new SniffQuoteStyle([
            'delimiter' => ',',
            'lineTerminator' => "\n"
        ]);
        $this->assertSame(Dialect::QUOTE_ALL, $sniffer->sniff($all));
        $this->assertSame(Dialect::QUOTE_NONE, $sniffer->sniff($none));
        $this->assertSame(Dialect::QUOTE_MINIMAL, $sniffer->sniff($min));
        $this->assertSame(Dialect::QUOTE_NONNUMERIC, $sniffer->sniff($nan));
    }

    public function testSniffHeaderByDataType()
    {
        $no1 = $this->getFileContentFor('noHeaderCommaQuoteAll');
        $no2 = $this->getFileContentFor('noHeaderCommaNoQuotes');
        $no3 = $this->getFileContentFor('noHeaderCommaQuoteMinimal');
        $no4 = $this->getFileContentFor('commaDelimTie');
        $no5 = $this->getFileContentFor('veryShort');
        $yes1 = $this->getFileContentFor('commaNewlineHeader');
        $yes2 = $this->getFileContentFor('headerDoubleQuote');
        $yes3 = $this->getFileContentFor('headerCommaQuoteNonnumeric');
        $sniffer = new SniffHeaderByDataType([
            'delimiter' => ','
        ]);
        $this->assertFalse($sniffer->sniff($no1));
        $this->assertFalse($sniffer->sniff($no2));
        $this->assertFalse($sniffer->sniff($no3));
        $this->assertFalse($sniffer->sniff($no4));
        $this->assertTrue($sniffer->sniff($yes1));
        $this->assertTrue($sniffer->sniff($yes2));
        $this->assertTrue($sniffer->sniff($yes3));
    }

    // @todo finish this later by calling sniff(), then removing the top line, calling sniff again, remove top line again, until you get false.
    // @note it's actually not quite that simple. If I want to support multiple headers, there needs to be a way to specify which header represents the
    //       column names, allow for some headers to have different amount(s) of columns, and all kinds of other stuff that I'm
    //       just not ready to support yet. Come back to this after everything else is supported. I created an issue on github
    //       for this here:
//    public function testSniffHeaderCanSniffMultipleHeaders()
//    {
//
//    }
}