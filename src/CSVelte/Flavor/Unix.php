<?php

/*
 * CSVelte: Slender, elegant CSV for PHP
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV
 * standardization efforts, CSVelte was written in an effort to take all the
 * suck out of working with CSV.
 *
 * @version   v0.2.3
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace CSVelte\Flavor;

use CSVelte\Flavor as FlavorBase;

/**
 * Excel CSV "flavor"
 * This is the most common flavor of CSV as it is what is produced by Excel, the
 * 900 pound Gorilla of CSV importing/exporting. It is also technically the
 * "standard" CSV format according to RFC 4180.
 *
 * @package   CSVelte\Reader
 *
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 *
 * @see       https://tools.ietf.org/html/rfc4180
 */
class Unix extends FlavorBase
{
    protected $quoteChar      = '"';
    protected $escapeChar     = '\\';
    protected $doubleQuote    = false;
    protected $lineTerminator = "\n";
    protected $quoteStyle     = FlavorBase::QUOTE_NONNUMERIC;
}
