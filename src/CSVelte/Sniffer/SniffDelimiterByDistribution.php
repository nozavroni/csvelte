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

use function Noz\collect;
use Noz\Collection\Collection;
use function Stringy\create as s;

class SniffDelimiterByDistribution extends AbstractSniffer
{
    /**
     * Guess delimiter in a string of data
     *
     * Guesses the delimiter in a data set by analyzing which of the provided possible delimiter characters is most
     * evenly distributed (horizontally) across the dataset.
     *
     * @param string $data The data to analyze
     *
     * @return string[]
     */
    public function sniff($data)
    {
        $lineTerminator = $this->getOption('lineTerminator') ?: "\n";
        $delimiters = $this->getOption('delimiters');
        $lines = collect(explode($lineTerminator, $this->removeQuotedStrings($data)));
        return collect($delimiters)->flip()->map(function($x, $char) use ($lines) {

                // standard deviation
                $sd = $lines->map(function($line, $line_no) use ($char) {
                    $delimited = collect(s($line)->split($char))
                        ->map(function($str) {
                            return $str->length();
                        });
                    // standard deviation
                    $avg = $delimited->average();
                    return sqrt($delimited->fold(function($d, $len) use ($avg) {
                            return $d->add(pow($len - $avg, 2));
                        }, new Collection)
                            ->sum() / $delimited->count());
                });
                return $sd->average();

            })
            ->sort()
            ->getKeyAt(1);
    }
}