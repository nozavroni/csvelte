<?php

/*
 * CSVelte: Slender, elegant CSV for PHP
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV
 * standardization efforts, CSVelte was written in an effort to take all the
 * suck out of working with CSV.
 *
 * @version   {version}
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace CSVelte\Exception;

/**
 * CSVelte\Exception\HeaderException
 * There are various methods throughout the library that expect a CSV source to
 * have a header row. Rather than doing something like:.

 if (if $file->hasHeader()) {
 $header = $file->getHeader()
 }

 * you can instead simply call $header->getHeader() and handle this exception if
 * said file has no header
 *
 * @package   CSVelte\Exception
 *
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 *
 * @since     v0.2
 */
class HeaderException extends CSVelteException
{
    /**
     * Error code for invalid/inconsistent header/column count.
     */
    const ERR_HEADER_COUNT = 1;
}
