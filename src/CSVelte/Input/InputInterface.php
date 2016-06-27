<?php namespace CSVelte\Input;
/**
 * InputInterface
 * Implement this interface to provide a source of input for CSVelte\Reader
 *
 * @package   CSVelte\Input
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
interface InputInterface
{
    /**
     * Class constructor
     *
     * @return void
     */
    public function __construct();

    /**
     * Read in the specified amount of characters from the input source
     *
     * @param integer Amount of characters to read from input source
     * @return string The specified amount of characters read from input source
     */
    public function read($chars);
}
