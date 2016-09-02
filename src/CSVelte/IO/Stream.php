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
use CSVelte\Contract\Writable;
//use CSVelte\Contract\Seekable;

use \InvalidArgumentException;
use CSVelte\Exception\InvalidStreamException;

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
class Stream implements Readable, Writable/*, Seekable*/
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
     * Meta data about stream resource.
     * Just contains the return value of stream_get_meta_data.
     * @var array The return value of stream_get_meta_data
     */
    protected $meta;

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
        if (array_key_exists('context', $options) && !is_null($options['context'])) {
            if (!is_array($options['context'])) {
                throw new InvalidArgumentException("\"context\" option must me an array, got: " . gettype($options['context']));
            }
            $options['context'] = stream_context_create($options['context']);
        }
        $this->options = array_merge($this->options, $options);
        if (is_string($uri = $stream)) {
            if (is_null($this->options['context'])) {
                $stream = @fopen($stream, $this->options['open_mode']);
            } else {
                $stream = @fopen($stream, $this->options['open_mode'], false, $this->options['context']);
            }
            if (false === $stream) {
                throw new InvalidStreamException("Invalid stream URI: " . $uri, InvalidStreamException::ERR_INVALID_URI);
            }
        }
        if (is_resource($stream) && get_resource_type($stream) == 'stream') {
            $this->stream = $stream;
        } else {
            throw new InvalidStreamException("Expected stream resource, got: " . gettype($stream), InvalidStreamException::ERR_INVALID_RESOURCE);
        }
        $this->meta = stream_get_meta_data($this->stream);
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
        return fread($this->stream, $length);
    }

    /**
     * Read single line.
     * Read the next line from the file (moving the internal pointer down a line).
     * Returns multiple lines if newline character(s) fall within a quoted string.
     *
     * @return string A single line read from the file.
     * @todo Should this accept line terminator? I think it should...
     */
    public function fgets($eol = PHP_EOL)
    {
        return stream_get_line($this->stream, 0, $eol);
    }

    public function eof()
    {
        return feof($this->stream);
    }

    public function rewind()
    {
        return rewind($this->stream);
    }

    public function fwrite($str)
    {
        return fwrite($this->stream, $str);
    }

}
