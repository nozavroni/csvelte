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

    public function testSniffQuoteAndDelim()
    {
        $stream = to_stream(fopen('/Users/luke/Downloads/slashdashcomma.txt', 'r+'));
        $sniffer = new Sniffer($stream);
        dd($sniffer->guessDelimByDistribution(
            $stream->read(3000),
            [",", "/"],
            "\n"
        ));
    }

//    public function testSniffQuoteAndDelimStrategy()
//    {
//
//    }
}