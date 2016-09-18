<?php
/**
 * CSVelte
 * Slender, elegant CSV for PHP5.3+
 *
 * @version v0.2.1
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author Luke Visinoni <luke.visinoni@gmail.com>
 * @license See LICENSE file
 */
namespace CSVelte\Contract;
/**
 * Seekable interface
 *
 * Implement this interface to be "seekable"
 *
 * @package CSVelte
 * @subpackage Contract (Interfaces)
 * @since v0.1
 */
interface Seekable
{
    /**
     * Seek to specified offset.
     *
     * @param integer Offset to seek to
     * @param integer Position from whence the offset should be applied
     * @return boolean True if seek was successful
     * @access public
     */
    public function seek($offset, $whence);

    /**
     * Seek to specified line offset.
     *
     * @param int Offset to seek to
     * @param int Position from whence to seek from
     * @param string The line terminator string/char
     * @return boolean True if successful
     */
    public function seekLine($offset, $whence, $eol);
}
