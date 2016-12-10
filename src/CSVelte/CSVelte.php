<?php
/**
 * CSVelte: Slender, elegant CSV for PHP
 *
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV
 * standardization efforts, CSVelte was written in an effort to take all the
 * suck out of working with CSV.
 *
 * @version   v0.2.2
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace CSVelte;

use \Iterator;
use CSVelte\IO\Stream;

use CSVelte\Exception\IOException;

/**
 * CSVelte Facade
 *
 * This class consists of static factory methods for easily generating commonly
 * used objects such as readers and writers, as well as convenience methods for
 * commonly used functionality such as exporting CSV data to a file.
 *
 * @package CSVelte
 * @subpackage Factory/Adapter
 * @since v0.1
 */
class CSVelte
{
    /**
     * CSVelte\Reader Factory
     *
     * Factory method for creating a new CSVelte\Reader object
     * Used to create a local file CSV reader object.
     *
     * @param string $filename The filename to read
     * @param Flavor|array|null $flavor An explicit flavor object that will be
     *     passed to the reader or an array of flavor attributes to override the
     *     default flavor attributes
     * @return \CSVelte\Reader An iterator for specified CSV file
     * @throws IOException
     */
    public static function reader($filename, $flavor = null)
    {
        self::assertFileIsReadable($filename);
        $file = Stream::open($filename);
        return new Reader($file, $flavor);
    }

    /**
     * String Reader Factory
     *
     * Factory method for creating a new CSVelte\Reader object for reading
     * from a PHP string
     *
     * @param string $str The CSV data to read
     * @param Flavor|array|null $flavor An explicit flavor object that will be
     *     passed to the reader or an array of flavor attributes to override the
     *     default flavor attributes
     * @return Reader An iterator for provided CSV data
     */
    public static function stringReader($str, $flavor = null)
    {
        return new Reader(streamize($str), $flavor);
    }

    /**
     * CSVelte\Writer Factory
     *
     * Factory method for creating a new CSVelte\Writer object for writing
     * CSV data to a file. If file doesn't exist, it will be created. If it
     * already contains data, it will be overwritten.
     *
     * @param string $filename The filename to write to.
     * @param Flavor|array|null $flavor An explicit flavor object that will be
     *     passed to the reader or an array of flavor attributes to override the
     *     default flavor attributes
     * @return Writer A writer object for writing to given filename
     */
    public static function writer($filename, $flavor = null)
    {
        $file = Stream::open($filename, 'w+');
        return new Writer($file, $flavor);
    }

    /**
     * Export CSV data to local file
     *
     * Facade method for exporting data to given filename. IF file doesn't exist
     * it will be created. If it does exist it will be overwritten.
     *
     * @param string $filename The filename to export data to
     * @param Iterator|array $data Data to write to CSV file
     * @param Flavor|array|null $flavor An explicit flavor object that will be
     *     passed to the reader or an array of flavor attributes to override the
     *     default flavor attributes
     * @return int Number of rows written
     */
    public static function export($filename, $data, $flavor = null)
    {
        $file = Stream::open($filename, 'w+');
        $writer = new Writer($file, $flavor);
        return $writer->writeRows($data);
    }

    /**
     * Assert that file is readable
     *
     * Assert that a particular file exists and is readable (user has permission
     * to read/access it)
     *
     * @param string $filename The name of the file you wish to check
     * @throws IOException
     * @internal
     */
    protected static function assertFileIsReadable($filename)
    {
        self::assertFileExists($filename);
        if (!is_readable($filename)) {
            throw new IOException('Permission denied for: ' . $filename, IOException::ERR_FILE_PERMISSION_DENIED);
        }
    }

    /**
     * Assert that a particular file exists
     *
     * @param string $filename The name of the file you wish to check
     * @throws IOException
     * @internal
     */
    protected static function assertFileExists($filename)
    {
        if (!file_exists($filename)) {
            throw new IOException('File does not exist: ' . $filename, IOException::ERR_FILE_NOT_FOUND);
        }
    }
}
