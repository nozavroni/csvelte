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
use function Stringy\create as s;
use Stringy\Stringy;

class SniffHeaderByDataType extends AbstractSniffer
{
     /**
     * Guess whether there is a header row
     *
     * Guesses whether the data has a header row by comparing the data types of the first row with the types of
     * corresponding columns in other rows.
     *
     * @note Unlike the original version of this method, this one will be used to ALSO determine HOW MANY header rows
     *       there likely are. So, compare the header to rows at the END of the sample.
     *
     * @param string $data The data to analyze
     *
     * @return bool
     */
    public function sniff($data)
    {
        $delimiter = $this->getOption('delimiter');
        $data = s($data);
        $lines = collect($data->lines())
            ->map(function($line) use ($delimiter) {
                return s($this->replaceQuotedSpecialChars($line, $delimiter));
            });
        $header = collect($lines->shift()->split($delimiter))
            ->map(function($val){ return $this->unQuote($val); })
            ->map(function($val) {
                return [
                    'type' => $this->getType($val),
                    'length' => s($val)->length()
                ];
            });
        $lines->pop(); // get rid of the last line because it may be incomplete
        $comparison = $lines->slice(0, 10)
            ->map(function($line, $line_no) use ($header, $delimiter) {
                /** @var Stringy $line */
                $values = collect($line->split($delimiter));
                return $values->map(function($str, $pos) use ($header) {
                    $comp = $header->get($pos);
                    $type = $this->getType($str);
                    return [
                        // true if same, false otherwise
                        'type' => $comp['type'] == $type,
                        // return the difference in length
                        'length' => $comp['length'] - s($str)->length()
                    ];
                });
            });

        $hasHeader = collect();
        $comparison->each(function($line) use ($hasHeader) {
            foreach ($line as $val) {
                if ($val['type']) {
                    $hasHeader->add(1);
                } else {
                    if ($val['length'] === 0) {
                        $hasHeader->add(1);
                    } else {
                        $hasHeader->add(-1);
                    }
                }
            }
        });

        return $hasHeader->sum() > 0;
    }

    protected function getType($value)
    {
        $str = s($value);
        switch (true) {
            case is_numeric($value):
                return 'numeric';
            case is_string($value):
                if (strtotime($value) !== false) {
                    return 'datetime';
                }
                if (preg_match('/^[+-]?[¥£€$]\d+(\.\d+)$/', $value)) {
                    return 'currency';
                }
                if ($str->isAlpha()) {
                    return 'alpha';
                }
                if ($str->isAlphanumeric()) {
                    return 'alnum';
                }
                if ($str->isBlank()) {
                    return 'blank';
                }
                if ($str->isJson()) {
                    return 'json';
                }
        }
        return 'unknown';
    }
}