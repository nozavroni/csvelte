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

use CSVelte\Sniffer;
use CSVelte\Exception\SnifferException;
use RuntimeException;

use function Noz\collect;

class SniffQuoteAndDelimByAdjacency extends AbstractSniffer
{
     /**
     * Guess quote and delimiter character(s)
     *
     * If there are quoted values within the data, it is often easiest to guess the quote and delimiter characters at
     * the same time by analyzing their adjacency to one-another. That is to say, in cases where certain values are
     * wrapped in quotes, it can often be determined what not only that quote character is, but also the delimiter
     * because it is often on either side of the quote character.
     *
     * @param string $data The data to analyze
     *
     * @return string[]
     */
    public function sniff($data)
    {
        /**
         * @var array An array of pattern matches
         */
        $matches = null;
        /**
         * @var array An array of patterns (regex)
         */
        $patterns = [];
        $lineTerminator = $this->getOption('lineTerminator') ?: PHP_EOL;
        // delim can be anything but line breaks, quotes, alphanumeric, underscore, backslash, or any type of spaces
        $antidelims = implode(["\r", "\n", "\w", preg_quote('"', '/'), preg_quote("'", '/'), preg_quote(chr(Sniffer::SPACE), '/')]);
        $delim      = "(?P<delim>[^{$antidelims}])";
        $quote      = "(?P<quoteChar>\"|'|`)"; // @todo I think MS Excel uses some strange encoding for fancy open/close quotes
        // @todo something happeened when I changed to double quotes that causes this to match things like ,"0.8"\n"2", as one when it should be two
        $patterns[] = "/{$delim} ?{$quote}.*?\\2\\1/ms"; // ,"something", - anything but whitespace or quotes followed by a possible space followed by a quote followed by anything followed by same quote, followed by same anything but whitespace
        $patterns[] = "/(?:^|{$lineTerminator}){$quote}.*?\\1{$delim} ?/ms"; // 'something', - beginning of line or line break, followed by quote followed by anything followed by quote followed by anything but whitespace or quotes
        $patterns[] = "/{$delim} ?{$quote}.*?\\2(?:$|{$lineTerminator})/ms"; // ,'something' - anything but whitespace or quote followed by possible space followed by quote followed by anything followed by quote, followed by end of line
        $patterns[] = "/(?:^|{$lineTerminator}){$quote}.*?\\2(?:$|{$lineTerminator})/ms"; // 'something' - beginning of line followed by quote followed by anything followed by quote followed by same quote followed by end of line
        foreach ($patterns as $pattern) {
            // @todo I had to add the error suppression char here because it was
            //     causing undefined offset errors with certain data sets. strange...
            if (preg_match_all($pattern, $data, $matches) && $matches) {
                break;
            }
        }
        if ($matches) {
            try {
                return collect($matches)
                    ->kintersect(array_flip(['quoteChar', 'delim']))
                    ->map(function($val) {
                        return collect($val)->frequency()->sort()->reverse()->getKeyAt(1);
                    })
                    ->ksort()
                    ->reverse()
                    ->values()
                    ->toArray();
            } catch (RuntimeException $e) {
                // eat this exception and let the sniffer exception below be thrown instead...
            }
        }
        throw new SnifferException('quoteChar and delimiter cannot be determined', SnifferException::ERR_QUOTE_AND_DELIM);
    }
}