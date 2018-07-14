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
namespace CSVelte\Sniffer;

class SniffLineTerminatorByCount extends AbstractSniffer
{
    /**
     * End-of-line constants
     */
    const EOL_WINDOWS = 0;
    const EOL_UNIX    = 1;
    const EOL_OTHER   = 2;

    /**
     * Guess line terminator in a string of data
     *
     * Using the number of times it occurs, guess which line terminator is most likely.
     *
     * @param string $data The data to analyze
     *
     * @return string
     */
    public function sniff($data)
    {
        // in this case we really only care about newlines so we pass in a comma as the delim
        $str = $this->replaceQuotedSpecialChars($data, ',');
        $eols = [
            static::EOL_WINDOWS => "\r\n",  // 0x0D - 0x0A - Windows, DOS OS/2
            static::EOL_UNIX    => "\n",    // 0x0A -      - Unix, OSX
            static::EOL_OTHER   => "\r",    // 0x0D -      - Other
        ];

        $curCount = 0;
        $curEol = PHP_EOL;
        foreach ($eols as $k => $eol) {
            if (($count = substr_count($str, $eol)) > $curCount) {
                $curCount = $count;
                $curEol   = $eol;
            }
        }
        return $curEol;
    }
}