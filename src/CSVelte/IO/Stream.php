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
        $this->setOptions($options);
        $this->stream = $this->open($stream, $this->options['open_mode'], $this->options['context']);
        $this->meta = stream_get_meta_data($this->stream);
    }

    /**
     * Stream Object Destructor.
     *
     * Closes stream connection.
     */
    public function __destruct()
    {
        if (is_resource($this->stream)) {
            $this->close();
        }
    }

    protected function open($stream, $mode = null, $context = null)
    {
        if (is_string($uri = $stream)) {
            if (is_null($context)) {
                $stream = @fopen($stream, $mode);
            } else {
                $stream = @fopen($stream, $mode, false, $context);
            }
            if (false === $stream) {
                throw new InvalidStreamException("Invalid stream URI: " . $uri, InvalidStreamException::ERR_INVALID_URI);
            }
        }
        if (!is_resource($stream) || get_resource_type($stream) != 'stream') {
            throw new InvalidStreamException("Expected stream resource, got: " . gettype($stream), InvalidStreamException::ERR_INVALID_RESOURCE);
        }
        return $stream;
    }

    /**
     * Close stream resource.
     *
     * @return boolean True on success or false on failure
     */
    protected function close()
    {
        return fclose($this->stream);
    }

    protected function setOptions(array $options)
    {
        if (array_key_exists('context', $options) && !is_null($options['context'])) {
            if (!is_array($options['context'])) {
                throw new InvalidArgumentException("\"context\" option must me an array, got: " . gettype($options['context']));
            }
            $options['context'] = stream_context_create($options['context']);
        }
        $this->options = array_merge($this->options, $options);
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

    /**
     * Read $length bytes from stream.
     *
     * Reads $length bytes (number of characters) from the stream
     *
     * @param int $length Number of bytes to read from stream
     * @return string The data read from stream
     */
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

    /**
     * Is file pointer at the end of the stream?
     *
     * Returns true if internal pointer has reached the end of the stream.
     *
     * @return boolean True if end of stream has been reached
     */
    public function eof()
    {
        return feof($this->stream);
    }

    /**
     * Rewind pointer to beginning of stream.
     *
     * Rewinds the stream, meaning it returns the pointer to the beginning of the
     * stream as if it had just been initialized.
     *
     * @return boolean True on success
     */
    public function rewind()
    {
        return rewind($this->stream);
    }

    /**
     * Write to stream
     *
     * Writes a string to the stream (if it is writable)
     *
     * @param string The data to be written to the stream
     * @return int The number of bytes written to the stream
     */
    public function fwrite($str)
    {
        return fwrite($this->stream, $str);
    }

    /**
     * Seek to position.
     *
     * Seek to a specific position within the stream (if seekable).
     *
     * @param int The position to seek to
     * @param int fseek flags (see http://php.net/manual/en/function.fseek.php)
     * @return int Returns 0 on success, and -1 on failure
     */
    public function fseek($pos, $flags = null)
    {
        return fseek($this->stream, $pos, $flags);
    }

}
