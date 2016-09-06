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

use CSVelte\Traits\IsReadable;
use CSVelte\Traits\IsWritable;
use CSVelte\Traits\IsSeekable;

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
    use IsReadable, IsWritable, IsSeekable;

    protected $seekable = true;

    /**
     * File Constructor
     *
     * Exactly the same as native SplFileObject constructor except that first it
     * resolves the filename if $use_include_path == true, to avoid weird
     * behavior with isReadable and isWritable.
     *
     * @param string  $filename         The filename to open
     * @param string  $open_mode        The fopen mode
     * @param boolean $use_include_path Should fopen search the include path
     * @param array   $context          An array of context options
     */
    public function __construct($filename, $open_mode = 'r', $use_include_path = false, $context = null)
    {
        /**
         * @note This fixes a possible bug? that causes SplFileObject to return
         *     false for isReadable() and isWritable() when $use_include_path
         *     is true, even if file exists and is both.
         */
        if ($use_include_path) {
            $filename = stream_resolve_include_path($filename);
        }
        parent::__construct(
            $filename,
            $open_mode,
            $use_include_path,
            $context
        );
    }

    /**
     * Get the file name.
     *
     * Return the entire file path and name of this file.
     *
     * @return string The file path and name
     */
    public function getName()
    {
        return $this->getPath();
    }

    /**
     * Read in the specified amount of characters from the file.
     *
     * Read $length characters from the file and return the resulting string.
     *
     * @param integer $length Amount of characters to read from file
     * @return string The specified amount of characters read from file
     */
    public function read($length)
    {
        $this->assertIsReadable();
        return $this->fread($length);
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
        return $this->read($this->getSize());
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
        $this->assertIsWritable();
        return $this->fwrite($data);
    }

    /**
     * Accessor for seekability.
     *
     * Returns true if possible to seek to a certain position within this file.
     *
     * @return boolean True if stream is seekable
     */
    public function isSeekable()
    {
        return $this->seekable;
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
        $this->assertIsSeekable();
        return $this->fseek($pos, $whence);
    }
}
