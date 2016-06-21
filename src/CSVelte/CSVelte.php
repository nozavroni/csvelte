<?php namespace CSVelte;

// use CSVelte\File;
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
    public function __construct()
    {

    }

    public function import($filename)
    {
        $this->assertFileExists($filename);
    }

    public function headers()
    {
        return [];
    }

    protected function assertFileExists($filename)
    {
        if (!file_exists($filename)) {
            throw new FileNotFoundException('File does not exist: ' . $filename);
        }
    }
}
