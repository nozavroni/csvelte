<?php
/**
 * CSVelte: Slender, elegant CSV for PHP
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV
 * standardization efforts, CSVelte was written in an effort to take all the
 * suck out of working with CSV.
 *
 * @version   v0.2
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace CSVelte\Exception;
/**
 * CSVelte\Exception\InvalidStreamUriException
 * Thrown when user attempts to open a stream using a malformed URI
 *
 * @package   CSVelte\Exception
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class InvalidStreamException extends CSVelteException
{
    /**
     * @var constant Used as code for exception thrown for missing file
     */
    const ERR_INVALID_URI = 1;

    /**
     * @var constant Used as code for exception thrown for missing directory
     */
    const ERR_INVALID_RESOURCE = 2;
}
