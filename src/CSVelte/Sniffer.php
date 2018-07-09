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
namespace CSVelte;

use CSVelte\Contract\Streamable;
use CSVelte\Dialect;

use function Noz\collect;
use function Stringy\create as s;

class Sniffer
{
    protected $delims = [',', "\t", ';', '|', ':', '-', '_', '#', '/', '\\', '$', '+', '=', '&', '@'];

    protected $stream;

    public function __construct(Streamable $stream, $delims = null)
    {
        $this->stream = $stream;
        if (!is_null($delims)) {
            $this->setPossibleDelimiters($delims);
        }
    }

    public function setPossibleDelimiters($delims)
    {
        $this->delims = collect($delims)
            ->filter(function($val) {
                return s($val)->length() == 1;
            })
            ->values()
            ->toArray();
    }

    public function getPossibleDelimiters()
    {
        return $this->delims;
    }

    /**
     * Sniff CSV data (determine its dialect)
     *
     * Since CSV is less a format than a collection of similar formats, you can never be certain how a particular CSV
     * file is formatted. This method inspects CSV data and returns its "dialect", an object that can be passed to
     * either a `CSVelte\Reader` or `CSVelte\Writer` object to tell it what "dialect" of CSV to use.
     *
     * @return Dialect
     */
    public function sniff()
    {

    }
}
