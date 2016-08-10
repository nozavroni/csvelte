<?php
/**
 * CSVelte\Flavor Examples
 *
 * This file contains examples of how to use a CSVelte\Flavor object.
 *
 * @version   v0.1
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */

/**
 * Create a new flavor object with all available attributes defined
 */
use CSVelte\Flavor;

$flavor = new Flavor(array(
    'delimiter' => ",",
    'quoteChar' => '"',
    'escapeChar' => null,
    'doubleQuote' => true,
    'quoteStyle' => Flavor::QUOTE_MINIMAL,
    'lineTerminator' => "\r\n",
    'header' => true
));
