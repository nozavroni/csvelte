<?php
/**
 * CSVelte
 * Slender, elegant CSV for PHP
 *
 * @version v0.2.1
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author Luke Visinoni <luke.visinoni@gmail.com>
 * @license See LICENSE file
 */
namespace CSVelte\Contract;
/**
 * Writable Interface
 *
 * Implement this interface to be writable by a CSVelte\Writer object
 *
 * @package CSVelte
 * @subpackage Contract (Interfaces)
 * @since v0.1
 */
interface Writable
{
    /**
     * Write data to the output.
     *
     * @param string The data to write
     * @return int The number of bytes written
     * @access public
     */
    public function write($data);

    /**
     * Write single line to output.
     *
     * Writes a line to the output (including end of line char/str).
     *
     * @param string The line to be written to the stream
     * @param string The end of line string
     * @return int The number of bytes written to the stream
     */
    public function writeLine($line, $eol);

    /**
     * Returns true if file is writable.
     *
     * Although this interface is called "Writable", implementing it is no
     * guarantee that the resource it represents will be writable. It is possible
     * to open an otherwise "writable" file in "read mode", rendering it
     * unwritable. This method will tell you if a resource is, indeed, writable.
     *
     * @return boolean True if writable, false otherwise
     */
    public function isWritable();
}
