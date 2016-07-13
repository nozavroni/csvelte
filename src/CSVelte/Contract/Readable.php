<?php namespace CSVelte\Contract;
/**
 * Readable Interface
 * Implement this interface to be "readable". This means that the CSVelte\Reader
 * class can read you (use you as a source of CSV data).
 *
 * @package   CSVelte\Contract
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
interface Readable
{
    /**
     * Read in the specified amount of characters from the input source
     *
     * @param integer Amount of characters to read from input source
     * @return string The specified amount of characters read from input source
     * @access public
     * @todo Renaming this method "readChars" might be a little more consistent..
     */
    public function read($chars);

    /**
     * Read a single line from input source and return it (and move pointer to )
     * the beginning of the next line)
     *
     * @return string The next line from the input source
     * @access public
     */
    public function readLine();

    /**
     * Determine whether the end of the readable resource has been reached
     *
     * @return boolean
     * @access public
     */
    public function isEof();

    /**
     * File must be able to be rewound when the end is reached
     *
     * @return void
     * @access public
     */
    public function rewind();
}
