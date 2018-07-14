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
namespace CSVelte\Exception;

class SnifferException extends CSVelteException
{
    /**
     * Could not determine delimiter.
     */
    const ERR_DELIMITER = 1;

    /**
     * Could not determine quote and delimiter at the same time.
     */
    const ERR_QUOTE_AND_DELIM = 2;

    /**
     * Invalid data sample.
     */
    const ERR_INVALID_SAMPLE = 3;
}
