<?php
/**
 * CSVelte: Slender, elegant CSV for PHP.
 *
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV
 * standardization efforts, CSVelte was written in an effort to take all the
 * suck out of working with CSV.
 *
 * @version   v0.2
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace CSVelte\IO;

use CSVelte\Traits\ReadLine;

use \SplFileObject;
use CSVelte\Contract\Readable;
use CSVelte\Contract\Writable;
use CSVelte\Contract\Seekable;

use CSVelte\Exception\NotYetImplementedException;

/**
 * CSVelte File.
 *
 * Represents a file for reading/writing. Implements both readable and writable
 * interfaces so that it can be passed to either ``CSVelte\Reader`` or
 * ``CSVelte\Writer``.
 *
 * @package    CSVelte
 * @subpackage CSVelte\IO
 * @copyright  (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author     Luke Visinoni <luke.visinoni@gmail.com>
 * @since      v0.2
 */
class File extends SplFileObject implements Readable, Writable, Seekable
{
    use ReadLine;

    public function getName()
    {
        return parent::getPath();
    }

    /**
     * Read in the specified amount of characters from the file
     *
     * @param integer Amount of characters to read from file
     * @return string The specified amount of characters read from file
     * @access public
     */
    public function read($length)
    {
        return parent::fread($length);
    }

    /**
     * Read the entire contents of file
     *
     * @param void
     * @return string The entire file contents
     * @access public
     */
    public function getContents()
    {
        return $this->read(parent::getSize());
    }

    /**
     * Write data to the output
     *
     * @param string The data to write
     * @return int The number of bytes written
     * @access public
     */
    public function write($data)
    {
        return parent::fwrite($data);
    }

    /**
     * Write data to the output
     *
     * @param string The data to write
     * @return int The number of bytes written
     * @access public
     */
    public function writeLine($line, $eol = PHP_EOL)
    {
        return $this->write($line . $eol);
    }

    /**
     * Seek to a position within an input
     *
     * @param integer Offset to seek to
     * @param integer Position from whence the offset should be applied
     * @return boolean True if seek was successful
     * @access public
     */
    public function seek($pos, $whence = SEEK_SET)
    {
        return parent::fseek($pos, $whence);
    }

    /**
     * Seek to specific line (beginning)
     * @param int Offset to seek to
     * @param int Position from whence to seek from
     * @param string The line terminator string/char
     * @return boolean True if successful
     * @todo Add to interface?
     */
    public function seekLine($offset, $whence = SEEK_SET, $eol = PHP_EOL)
    {
        throw new NotYetImplementedException("This method not yet implemented.");
    }

}
