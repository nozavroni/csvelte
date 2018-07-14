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

class SniffDelimiterByConsistency extends AbstractSniffer
{
    /**
     * Guess delimiter in a string of data
     *
     * Guesses the delimiter character by analyzing the count consistency of possible delimiters across several lines.
     * Basically, the character that occurs roughly the same number of times on each line will be returned. It is
     * possible for this sniffer to return multiple characters if there is a tie.
     *
     * @param string $data The data to analyze
     *
     * @return string[]
     */
    public function sniff($data)
    {
        // build a table of characters and their frequencies for each line. We
        // will use this frequency table to then build a table of frequencies of
        // each frequency (in 10 lines, "tab" occurred 5 times on 7 of those
        // lines, 6 times on 2 lines, and 7 times on 1 line)

        $delimiters = $this->getOption('delimiters');
        $lineTerminator = $this->getOption('lineTerminator') ?: "\n";
        // @todo it would probably make for more consistent results if you popped the last line since it will most likely be truncated due to the arbitrary nature of the sample size
        $lines = collect(explode($lineTerminator, $this->removeQuotedStrings($data)));
        $frequencies = $lines->map(function($line) use ($delimiters) {
            $preferred = array_flip($delimiters);
            return collect($preferred)
                ->map(function() { return 0; })
                ->merge(collect(s($line)->chars())->frequency()->kintersect($preferred))
                ->toArray();
        });

        // now determine the mode for each char to decide the "expected" amount
        // of times a char (possible delim) will occur on each line...
        $modes = collect($delimiters)
            ->flip()
            ->map(function($freq, $delim) use ($frequencies) {
                return $frequencies->getColumn($delim)->mode();
            })
            ->filter();

        /** @var Collection $consistencies */
        $consistencies = $frequencies->fold(function(Collection $accum, $freq, $line_no) use ($modes) {

            $modes->each(function($expected, $char) use ($accum, $freq) {
                /** @var Collection $freq */
                if (collect($freq)->get($char) == $expected) {
                    $matches = $accum->get($char, 0);
                    $accum->set($char, ++$matches);
                }
            });
            return $accum;

        }, new Collection)
            ->sort()
            ->reverse();

        $winners = $consistencies->filter(function($freq) use ($consistencies) {
                return $freq === $consistencies->max();
            })
            ->keys();

        // return winners in order of preference
        return collect($delimiters)
            ->intersect($winners)
            ->values()
            ->toArray();
    }
}