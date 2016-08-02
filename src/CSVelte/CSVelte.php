<?php namespace CSVelte;

use CSVelte\Reader;
use CSVelte\Flavor;
use CSVelte\Input\File;
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
    public static function reader($filename, Flavor $flavor = null)
    {
        $infile = new File($filename);
        return new Reader($infile, $flavor);
    }
    /**
     * Assert that a particular file exists and is readable (user has permission
     * to read/access it)
     *
     * @access protected
     * @var string The name of the file you wish to check
     * @return void
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
     * @access protected
     * @var string The name of the file you wish to check
     * @return void
     */
    protected static function assertFileExists($filename)
    {
        if (!file_exists($filename)) {
            throw new FileNotFoundException('File does not exist: ' . $filename);
        }
    }
}
