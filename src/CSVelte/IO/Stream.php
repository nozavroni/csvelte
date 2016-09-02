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
use CSVelte\Exception\InvalidStreamException;
//use CSVelte\Contract\Writable;
//use CSVelte\Contract\Seekable;

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
     * Initialization options for this stream
     * @var array These options are set when instantiating this stream object.
     *            These values are just defaults.
     *      open_mode: Same as mode for fopen
     *      context: See stream_context_create()
     *               http://php.net/manual/en/function.stream-context-create.php
     */
    protected $options = [
        'open_mode' => 'rb+',
        'context' => null
    ];

    /**
     * Stream Object Constructor.
     *
     * Instantiates the stream object
     *
     * @param string|resource $stream Either a valid stream URI or an open
     *     stream resource (using fopen, fsockopen, et al.)
     * @param array $options An array of any/none of the following options
     *                          (see $options var above for more details)
     */
    public function __construct($stream, array $options = [])
    {
        $this->options = array_merge($this->options, $options);
        if (is_string($stream)) {
            if (false === ($stream = @fopen($stream, $this->options['open_mode']))) {
                throw new InvalidStreamException("Invalid stream URI: " . $stream, InvalidStreamException::ERR_INVALID_URI);
            }
        }
        if (is_resource($stream) && get_resource_type($stream) == 'stream') {
            $this->stream = $stream;
        }
        $this->meta = stream_get_meta_data($this->stream);
    }

    /**1`
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

    /**1`
     * Accessor for stream URI.
     *
     * Returns the stream URI
     *
     * @return string The URI for the stream
     */
    public function getUri()
    {
        return $this->meta['uri'];
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
