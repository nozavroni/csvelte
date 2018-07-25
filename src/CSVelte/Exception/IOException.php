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

class IOException extends CSVelteException
{
    /**
     * Used as code for exception thrown for unreadable stream.
     */
    const ERR_NOT_READABLE = 101;

    /**
     * Used as code for exception thrown for unwritable stream.
     */
    const ERR_NOT_WRITABLE = 102;

    /**
     * Used as code for exception thrown for unseekable stream.
     */
    const ERR_NOT_SEEKABLE = 103;

    /**
     * Used as code for exception thrown for missing file.
     */
    const ERR_INVALID_STREAM_URI = 201;

    /**
     * Used as code for exception thrown for missing directory.
     */
    const ERR_INVALID_STREAM_RESOURCE = 202;

    /**
     * Used as code for exception thrown for missing file.
     */
    const ERR_FILE_PERMISSION_DENIED = 301;

    /**
     * Used as code for exception thrown for missing directory.
     */
    const ERR_FILE_NOT_FOUND = 302;

    /**
     * Used as code for exception thrown for connection failed.
     */
    const ERR_STREAM_CONNECTION_FAILED = 401;

    /**
     * Used when user attempts to set a parameter on a connection
     *     that is already opened.
     */
    const ERR_STREAM_ALREADY_OPEN = 402;
}
