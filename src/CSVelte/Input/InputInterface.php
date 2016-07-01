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
     * Retrieve the name of the input source
     *
     * @return string The name of the input source
     * @access public
     */
    public function name();
    
    /**
     * Read in the specified amount of characters from the input source
     *
     * @param integer Amount of characters to read from input source
     * @return string The specified amount of characters read from input source
     * @access public
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
}
