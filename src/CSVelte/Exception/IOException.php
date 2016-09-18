<?php
/**
 * CSVelte: Slender, elegant CSV for PHP
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV
 * standardization efforts, CSVelte was written in an effort to take all the
 * suck out of working with CSV.
 *
 * @version   v0.2.1
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace CSVelte\Exception;
/**
 * CSVelte\Exception\IOException
 * Thrown when user attempts to access/read a file in a way that it doesn't allow
 *
 * @package   CSVelte\Exception
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @since     v0.2
 */
class IOException extends CSVelteException
{
    /**
     * @var constant Used as code for exception thrown for unreadable stream
     */
    const ERR_NOT_READABLE = 101;

    /**
     * @var constant Used as code for exception thrown for unwritable stream
     */
    const ERR_NOT_WRITABLE = 102;

    /**
     * @var constant Used as code for exception thrown for unseekable stream
     */
    const ERR_NOT_SEEKABLE = 103;

    /**
     * @var constant Used as code for exception thrown for missing file
     */
    const ERR_INVALID_STREAM_URI = 201;

    /**
     * @var constant Used as code for exception thrown for missing directory
     */
    const ERR_INVALID_STREAM_RESOURCE = 202;

    /**
     * @var constant Used as code for exception thrown for missing file
     */
    const ERR_FILE_PERMISSION_DENIED = 301;

    /**
     * @var constant Used as code for exception thrown for missing directory
     */
    const ERR_FILE_NOT_FOUND = 302;

    /**
     * @var constant Used as code for exception thrown for connection failed
     */
    const ERR_STREAM_CONNECTION_FAILED = 401;
}
