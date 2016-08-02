<?php namespace CSVelte;

use CSVelte\Reader;
use CSVelte\Flavor;
use CSVelte\Input\File;
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
        $infile = new File($filename);
        return new Reader($infile, $flavor);
    }

    /**
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
