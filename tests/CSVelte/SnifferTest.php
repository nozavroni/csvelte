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

use CSVelte\Sniffer;

use CSVelte\Sniffer\SniffLineTerminatorByCount;
use CSVelte\Sniffer\SniffQuoteAndDelimByAdjacency;
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
}