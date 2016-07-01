<?php namespace CSVelte\Input;
/**
 * CSVelte\Input\File
 * Represents a csv file
 *
 * @todo You could probably change the name of this to "Stream" and then just
 *     subclass it for "File"  and not have to change much (if anything). Only
 *     provide it for easier reading of code. (new File() is more understandable )
 *     than new Stream() to most people)
 * @package   CSVelte
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class File implements InputInterface
{
    /**
     * @var resource The stream resource to input file
     */
    protected $source;

    /**
     * Class constructor
     *
     * @param string The path and filename of the input file to read from
     * @return void
     * @access public
     */
    public function __construct($path)
    {
        if (($this->source = @fopen($path, 'r')) === false) {
            // throw exception
            return;
        }
        $this->fileinfo = stream_get_meta_data($this->source);
    }

    /**
     * Returns the file's name
     *
     * @return string The name of in the input file
     * @access public
     */
    public function name()
    {
        return basename($this->fileinfo['uri']);
    }

    /**
     * @inheritDoc
     */
    public function read($chars)
    {

    }

    /**
     * @inheritDoc
     */
    public function readLine()
    {

    }
}
