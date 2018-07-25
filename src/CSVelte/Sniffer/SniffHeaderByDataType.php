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
use CSVelte\Reader;
use Noz\Collection\Collection;

use function CSVelte\to_stream;
use function Noz\collect;
use function Stringy\create as s;

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
        $getFieldInfo = function($val) {
            return [
                'value' => $val,
                'type' => $this->getType($val),
                'length' => s($val)->length()
            ];
        };
        $reader = new Reader(to_stream($data), new Dialect(['delimiter' => $delimiter, 'header' => false]));
        $lines = collect($reader->toArray());
        $header = collect($lines->shift()) ->map($getFieldInfo);
        $lines->pop(); // get rid of the last line because it may be incomplete
        $comparison = $lines->slice(0, 10)->map(function($fields) use ($getFieldInfo) {
            return array_map($getFieldInfo, $fields);
        });

        /**
         * @var Collection $header
         * @var Collection $noHeader
         */
        list($header, $noHeader) = $header->map(function($hval, $hind) use ($comparison) {

                $isHeader = 0;
                $type = $comparison->getColumn($hind)->getColumn('type');
                $length = $comparison->getColumn($hind)->getColumn('length');
                if ($distinct = $type->distinct()) {
                    if ($distinct->count() == 1) {
                        if ($distinct->getValueAt(1) != $hval['type']) {
                            $isHeader = 1;
                        }
                    }
                }

                if (!$isHeader) {
                    // use standard deviation to determine if header is wildly different length than others
                    $mean = $length->average();
                    $sd = sqrt($length->map(function ($len) use ($mean) {
                        return pow($len - $mean, 2);
                    })->average());

                    $diff_head_avg = abs($hval['length'] - $mean);
                    if ($diff_head_avg > $sd) {
                        $isHeader = 1;
                    }
                }
                return $isHeader;

            })
            ->partition(function($val) {
                return (bool) $val;
            });

        return $header->count() > $noHeader->count();
    }

    /**
     * Get string's type
     *
     * Returns one of a handful of "types".
     *
     * @param string $value A string to get the type of
     *
     * @return string
     */
    protected function getType($value)
    {
        $str = s($value);
        switch (true) {
            case is_numeric($value):
                return 'numeric';
            // note - the order of these is important, do not change unless you know what you're doing
            case is_string($value):
                if (preg_match('/^([a-z0-9_\.-]+)@([\da-z\.-]+)\.([a-z\.]{2,6})$/i', $value)) {
                    return 'email';
                }
                if (preg_match('/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/i', $value)) {
                    return 'url';
                }
                if (strtotime($value) !== false) {
                    return 'datetime';
                }
                if (preg_match('/^[+-]?[¥£€$]\d+(\.\d+)$/', $value)) {
                    return 'currency';
                }
                if (preg_match('/^[a-z0-9_-]{1,35}$/i', $value)) {
                    return 'identifier';
                }
                if (preg_match('/^[a-z0-9 _\/&\(\),\.?\'!-]{1,50}$/i', $value)) {
                    return 'text_short';
                }
                if (preg_match('/^[a-z0-9 _\/&\(\),\.?\'!-]{100,}$/i', $value)) {
                    return 'text_long';
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
        return 'other';
    }
}
