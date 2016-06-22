<?php namespace CSVelte;

use CSVelte\File;
// use CSVelte\Exception\FileNotFoundException;

/**
 * CSVelte
 * A PHP CSV utility library (formerly PHP CSV Utilities).
 *
 * @package   CSVelte
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class Reader
{
    protected $file;
    /**
     * Class constructor
     * @todo Replace CSVelte\File hint with CSVelte\FileInterface
     */
    public function __construct(File $file)
    {
        $this->file = $file;
    }

    public function file()
    {
        return $this->file;
    }
}
