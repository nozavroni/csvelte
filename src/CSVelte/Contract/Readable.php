<?php
/**
 * CSVelte.
 *
 * Slender, elegant CSV for PHP
 *
 * @version v0.2.1
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author Luke Visinoni <luke.visinoni@gmail.com>
 * @license See LICENSE file
 */
namespace CSVelte\Contract;
/**
 * Readable Interface
 *
 * Implement this interface to be "readable". This means that the CSVelte\Reader
 * class can read you (use you as a source of CSV data).
 *
 * @package CSVelte
 * @subpackage Contract (Interfaces)
 * @since v0.1
 */
interface Readable
{
    /**
     * Return some type of identifying information about this readable
     * @return string Typicallly a file name or stream uri
     */
    public function getName();

    /**
     * Read in the specified amount of characters from the input source
     *
     * @param integer Amount of characters to read from input source
     * @return string|boolean The specified amount of characters read from input source
     * @access public
     */
    public function read($chars);

    /**
     * Read a single line from input source and return it (and move pointer to )
     * the beginning of the next line)
     *
     * @param string $eol Line terminator sequence/character
     * @param int $maxLength The maximum line length to return
     * @return string The next line from the input source
     * @access public
     */
    public function readLine($eol = PHP_EOL, $maxLength = null);

    /**
     * Read the entire contents of stream/file
     *
     * @param void
     * @return string The entire stream/file contents
     * @access public
     */
    public function getContents();

    /**
     * Determine whether the end of the readable resource has been reached
     *
     * @param void
     * @return boolean
     * @access public
     */
    public function eof();

    /**
     * File must be able to be rewound when the end is reached
     *
     * @param void
     * @return void
     * @access public
     */
    public function rewind();

    /**
     * Returns true if file is readable.
     *
     * Although this interface is called "Readable", implementing it is no
     * guarantee that the resource it represents will be readable. It is possible
     * to open an otherwise "readable" file in "write mode", rendering it
     * unreadable. This method will tell you if a resource is, indeed, readable.
     *
     * @return boolean True if readable, false otherwise
     */
    public function isReadable();
}
