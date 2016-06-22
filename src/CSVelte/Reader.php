<?php namespace CSVelte;

// use CSVelte\Exception\FileNotFoundException;

/**
 * CSVelte
 * A PHP CSV utility library (formerly PHP CSV Utilities).
 *
 * @package   CSVelte
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class Reader implements \Countable
{
    protected $file;
    /**
     * Class constructor
     * @todo Replace CSVelte\File hint with CSVelte\InputInterface so that reader
     *       can accept streams and any other type of input object you can cook up
     */
    public function __construct(File $file)
    {
        $this->file = $file;
    }

    public function file()
    {
        return $this->file;
    }

    public function count()
    {
        return 100;
    }
}
