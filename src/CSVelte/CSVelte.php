<?php namespace CSVelte;

use CSVelte\Reader;
use CSVelte\Flavor;
use CSVelte\Input\File as InFile;
use CSVelte\Output\File as OutFile;
use CSVelte\Input\String;
use CSVelte\Excaption\PermissionDeniedException;
use CSVelte\Exception\FileNotFoundException;

/**
 * CSVelte
 * A PHP CSV utility library (formerly PHP CSV Utilities).
 *
 * @package   CSVelte
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class CSVelte
{
    /**
     * CSVelte\Reader Factory
     *
     * Convenience method for creating a new CSVelte\Reader object
     * Used to create a local file CSV reader object.
     *
     * @param string The filename to read
     * @param CSVelte\Flavor An explicit flavor object for the reader to use
     * @return CSVelte\Reader
     * @throws CSVelte\Exception\PermissionDeniedException
     * @throws CSVelte\Exception\FileNotFoundException
     * @access public
     */
    public static function reader($filename, Flavor $flavor = null)
    {
        self::assertFileIsReadable($filename);
        $infile = new InFile($filename);
        return new Reader($infile, $flavor);
    }

    /**
     * String Reader Factory
     *
     * Convenience method for creating a new CSVelte\Reader object for reading
     * from a PHP string
     *
     * @param string The CSV data to read
     * @param CSVelte\Flavor An explicit flavor object for the reader to use
     * @return CSVelte\Reader
     * @access public
     */
    public static function stringReader($str, Flavor $flavor = null)
    {
        $infile = new String($str);
        return new Reader($infile, $flavor);
    }

    /**
     * CSVelte\Writer Factory
     *
     * Convenience method for creating a new CSVelte\Writer object for writing
     * CSV data to a file
     *
     * @param string The filename to read
     * @param CSVelte\Flavor An explicit flavor object for the writer to use
     * @return CSVelte\Writer
     * @access public
     */
    public static function writer($filename, Flavor $flavor = null)
    {
        $outfile = new OutFile($filename);
        return new Writer($outfile, $flavor);
    }

    /**
     * Export CSV data to local file
     *
     * Convenience method for exporting data to a file
     *
     * @param string The filename to read
     * @param Iterator|array Data to write to CSV file
     * @param CSVelte\Flavor An explicit flavor object for the writer to use
     * @return int Number of rows written
     * @access public
     */
    public static function export($filename, $data, Flavor $flavor = null)
    {
        $outfile = new OutFile($filename);
        $writer = new Writer($outfile, $flavor);
        return $writer->writeRows($data);
    }

    /**
     * Assert that a particular file exists and is readable (user has permission
     * to read/access it)
     *
     * @param string The name of the file you wish to check
     * @return void
     * @access protected
     * @throws CSVelte\Exception\PermissionDeniedException
     */
    protected static function assertFileIsReadable($filename)
    {
        self::assertFileExists($filename);
        if (!is_readable($filename)) {
            throw new PermissionDeniedException('Permission denied for: ' . $filename);
        }
    }

    /**
     * Assert that a particular file exists
     *
     * @param string The name of the file you wish to check
     * @return void
     * @access protected
     * @throws CSVelte\Exception\FileNotFoundException
     */
    protected static function assertFileExists($filename)
    {
        if (!file_exists($filename)) {
            throw new FileNotFoundException('File does not exist: ' . $filename);
        }
    }
}
