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

use CSVelte\Dialect;
use CSVelte\Sniffer;
use CSVelte\Exception\SnifferException;
use RuntimeException;

use function Noz\collect;
use function Stringy\create as s;

class SniffQuoteStyle extends AbstractSniffer
{
     /**
     * Guess quoting style
     *
     * The quoting style refers to which types of columns are quoted within a csv dataset. The dialect class defines
     * four possible quoting styles; all, none, minimal, or non-numeric. This class attempts to determine which of those
     * four it is by analyzing the content within each quoted value.
     *
     * @param string $data The data to analyze
     *
     * @return int
     */
    public function sniff($data)
    {
        $styles = collect([
            Dialect::QUOTE_NONE => true,
            Dialect::QUOTE_ALL => true,
            Dialect::QUOTE_MINIMAL => true,
            Dialect::QUOTE_NONNUMERIC => true
        ]);

        $delimiter = $this->getOption('delimiter');
        $lineTerminator = $this->getOption('lineTerminator') ?: "\n";
        $quoted = collect();
        collect(explode($lineTerminator, $this->replaceQuotedSpecialChars($data, $delimiter, $lineTerminator)))
            ->each(function($line, $line_no) use (&$styles, $quoted, $delimiter) {
                $values = explode($delimiter, $line);
                foreach ($values as $value) {
                    if ($this->isQuoted($value)) {
                        // remove surrounding quotes
                        $value = preg_replace('/^(["\'])(.*)\1$/', '\2', $value);
                        $styles[Dialect::QUOTE_NONE] = false;
                        if (s($value)->containsAny([static::PLACEHOLDER_DELIM, static::PLACEHOLDER_NEWLINE, '"', "'"])) {
                            $quoted->add(Dialect::QUOTE_MINIMAL);
                        } elseif (!is_numeric((string) $value)) {
                            $quoted->add(Dialect::QUOTE_NONNUMERIC);
                        } else {
                            $quoted->add(Dialect::QUOTE_ALL);
                        }
                    } else {
                        $styles[Dialect::QUOTE_ALL] = false;
                    }
                }
            });

        // @todo the following can almost certainly be cleaned up considerably
        if ($styles[Dialect::QUOTE_ALL]) {
            return Dialect::QUOTE_ALL;
        } elseif ($styles[Dialect::QUOTE_NONE]) {
            return Dialect::QUOTE_NONE;
        }

        $types = $quoted->distinct();

        if ($types->contains(Dialect::QUOTE_NONNUMERIC) && $types->contains(Dialect::QUOTE_MINIMAL)) {
            // if both non-numeric and minimal then it isn't minimal
            $types = $types->filter(function($type) {
                return $type !== Dialect::QUOTE_MINIMAL;
            });
        }

        if ($types->count() == 1) {
            return $types->getValueAt(1);
        }

        return Dialect::QUOTE_MINIMAL;
    }

    /**
     * Determine whether a particular string of data has quotes around it.
     *
     * @param string $data The data to check
     *
     * @return bool
     */
    protected function isQuoted($data)
    {
        return preg_match('/^([\'"])[^\1]*\1$/', $data);
    }
}