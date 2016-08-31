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

use CSVelte\Contract\Readable;
//use CSVelte\Contract\Writable;
//use CSVelte\Contract\Seekable;
//use CSVelte\Exception\FileNotFoundException;

/**
 * CSVelte Stream.
 *
 * Represents a stream for input/output. Implements both readable and writable
 * interfaces so that it can be passed to either ``CSVelte\Reader`` or
 * ``CSVelte\Writer``.
 *
 * @package    CSVelte
 * @subpackage CSVelte\IO
 * @copyright  (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author     Luke Visinoni <luke.visinoni@gmail.com>
 * @since      v0.2
 */
class Stream implements Readable/*, Writable, Seekable*/
{
    /**
     * @var resource An open stream resource
     */
    protected $stream;

    /**
     * Stream Object Constructor.
     *
     * Instantiates the stream object
     *
     * @param string|resource $stream Either a valid stream URI or an open
     *     stream resource (using fopen, fsockopen, et al.)
     */
    public function __construct($stream)
    {
        if (is_string($stream)) {
            $stream = fopen($stream, 'r+');
        }
        if (is_resource($stream) && get_resource_type($stream) == 'stream') {
            $this->stream = $stream;
        }
    }

    /**
     * Accessor for internal stream resource.
     *
     * Returns the internal stream resource pointer
     *
     * @return resource The open stream resource pointer
     */
    public function getResource()
    {
        return $this->stream;
    }

    public function fread($length)
    {

    }

    /**
     * Read single line.
     * Read the next line from the file (moving the internal pointer down a line).
     * Returns multiple lines if newline character(s) fall within a quoted string.
     *
     * @return string A single line read from the file.
     */
    public function fgets()
    {

    }

    public function eof()
    {

    }

    public function rewind()
    {

    }

}
