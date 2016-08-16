<?php
/**
 * CSVelte: Slender, elegant CSV for PHP.
 *
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV
 * standardization efforts, CSVelte was written in an effort to take all the
 * suck out of working with CSV.
 *
 * @version   v0.1
 *
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace CSVelte;

use CSVelte\Excaption\PermissionDeniedException;
use CSVelte\Exception\FileNotFoundException;
use CSVelte\Input\File as InFile;
use CSVelte\Input\String;
use CSVelte\Output\File as OutFile;

/**
 * CSVelte Facade.
 *
 * This class consists of static factory methods for easily generating commonly
 * used objects such as readers and writers, as well as convenience methods for
 * commonly used functionality such as exporting CSV data to a file.
 *
 * @since v0.1
 */
class CSVelte
{
    /**
     * CSVelte\Reader Factory.
     *
     * Factory method for creating a new CSVelte\Reader object
     * Used to create a local file CSV reader object.
     *
     * @param string The filename to read
     * @param CSVelte\Flavor An explicit flavor object that will be passed to the reader
     *
     * @throws CSVelte\Exception\PermissionDeniedException
     * @throws CSVelte\Exception\FileNotFoundException
     *
     * @return CSVelte\Reader An iterator for specified CSV file
     */
    public static function reader($filename, Flavor $flavor = null)
    {
        self::assertFileIsReadable($filename);
        $infile = new InFile($filename);

        return new Reader($infile, $flavor);
    }

    /**
     * String Reader Factory.
     *
     * Factory method for creating a new CSVelte\Reader object for reading
     * from a PHP string
     *
     * @param string The CSV data to read
     * @param CSVelte\Flavor An explicit flavor object that will be passed to the reader
     *
     * @return CSVelte\Reader An iterator for provided CSV data
     */
    public static function stringReader($str, Flavor $flavor = null)
    {
        $infile = new String($str);

        return new Reader($infile, $flavor);
    }

    /**
     * CSVelte\Writer Factory.
     *
     * Factory method for creating a new CSVelte\Writer object for writing
     * CSV data to a file. If file doesn't exist, it will be created. If it
     * already contains data, it will be overwritten.
     *
     * @param string The filename to write to.
     * @param CSVelte\Flavor An explicit flavor object for the writer to use
     *
     * @return CSVelte\Writer A writer object for writing to given filename
     */
    public static function writer($filename, Flavor $flavor = null)
    {
        $outfile = new OutFile($filename);

        return new Writer($outfile, $flavor);
    }

    /**
     * Export CSV data to local file.
     *
     * Facade method for exporting data to given filename. IF file doesn't exist
     * it will be created. If it does exist it will be overwritten.
     *
     * @param string The filename to export data to
     * @param Iterator|array Data to write to CSV file
     * @param CSVelte\Flavor An explicit flavor object that will be passed to the writer
     *
     * @return int Number of rows written
     */
    public static function export($filename, $data, Flavor $flavor = null)
    {
        $outfile = new OutFile($filename);
        $writer = new Writer($outfile, $flavor);

        return $writer->writeRows($data);
    }

    /**
     * Assert that file is readable.
     *
     * Assert that a particular file exists and is readable (user has permission
     * to read/access it)
     *
     * @param string The name of the file you wish to check
     *
     * @throws CSVelte\Exception\PermissionDeniedException
     *
     * @return void
     *
     * @internal
     */
    protected static function assertFileIsReadable($filename)
    {
        self::assertFileExists($filename);
        if (!is_readable($filename)) {
            throw new PermissionDeniedException('Permission denied for: '.$filename);
        }
    }

    /**
     * Assert that a particular file exists.
     *
     * @param string The name of the file you wish to check
     *
     * @throws CSVelte\Exception\FileNotFoundException
     *
     * @return void
     *
     * @internal
     */
    protected static function assertFileExists($filename)
    {
        if (!file_exists($filename)) {
            throw new FileNotFoundException('File does not exist: '.$filename);
        }
    }
}
