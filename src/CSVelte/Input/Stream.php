<?php namespace CSVelte\Input;

use CSVelte\Contract\Readable;
use CSVelte\Exception\EndOfFileException;
use CSVelte\Exception\InvalidStreamUriException;

/**
 * CSVelte\Input\Stream
 * Represents a stream source for CSV data
 *
 * @package   CSVelte
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @todo      Look at the ArrayObject class and see if it can be used
 */
class Stream implements Readable
{
    /**
     * @const integer
     */
    const MAX_LINE_LENGTH = 4096;

    /**
     * @const string
     */
    const RESOURCE_TYPE = 'stream';

    /**
     * @var resource The stream resource to input file
     */
    protected $source;

    /**
     * @var array An array of meta data about the source stream
     */
    protected $info;

    /**
     * @var integer The position of the pointer within the stream resource
     */
    protected $position;

    /**
     * Class constructor
     *
     * @param stream|string Either a valid stream handle (opened with fopen or similar function) OR a valid stream URI
     * @return void
     * @access public
     */
    public function __construct($stream)
    {
        if (is_resource($stream)) {
            if (self::RESOURCE_TYPE !== ($type = get_resource_type($stream))) {
                // throw exception
                return;
            }
        } else {
            if (false === ($stream = @fopen($stream, 'r'))) {
                // @todo custom exception
                throw new InvalidStreamUriException('Cannot open stream: ' . $stream);
            }
        }
        $this->source = $stream;
        $this->updateInfo();
    }

    /**
     * Close the stream
     * Close the stream resource and release any other resources opened by this
     * stream object.
     *
     * @return bool
     * @access public
     * @todo Should this throw an exception if user tries to close a stream that
     *     isn't open? I don't think it should because I can't think of a way it
     *     would be useful or intuitive. In fact it'd probably cause confusion
     */
    public function close()
    {
        if (is_resource($this->source)) return fclose($this->source);
        return false;
    }

    public function getStreamResource()
    {
        return $this->source;
    }

    /**
     * Get the current position of the pointer
     *
     * @return integer Position of pointer within source
     * @access public
     */
    public function position()
    {
        return $this->position;
    }

    /**
     * Get the current position of the pointer
     *
     * @return integer|false Position of pointer within source or false on failure
     * @access protected
     * @todo Look through all the parameters returned by fstat() and see if any
     *     of it might be useful for this class or for File class.
     */
    protected function updateInfo()
    {
        $this->info = stream_get_meta_data($this->source);
        return $this->position = ftell($this->source);
    }

    /**
     * Retrieve the name of this stream. If stream is a file, it will return the
     * file's name. If it's some other type of stream, it's hard to say what,
     * exactly, the name will be.
     *
     * @return string The name of the stream resource
     * @access public
     */
    public function name()
    {
        return basename($this->info['uri']);
    }

    /**
     * Retrieve the dirname part of the stream name
     *
     * @return string The dirname of this stream's path
     * @access public
     * @todo I'm not sure this method is actually relevant when dealing with
     *     streams such as php://filter/read=string.toupper/resource=file:///var/www/foo.csv
     *     I'm not sure whether I should parse the stream name and return the
     *     dirname(realpath()) of /var/www/foo.csv or if the rest of it actually
     *     is techinally part of the dirname... I'm going to leave it as is for
     *     now because I'm leaning towards "It doesn't matter"
     */
    public function path()
    {
        return dirname($this->info['uri']);
    }

    /**
     * @inheritDoc
     */
    public function read($length)
    {
        if (false === ($data = fread($this->source, $length))) {
            if ($this->isEof()) {
                throw new EndOfFileException('Cannot read from ' . $this->name() . '. End of file has been reached.');
            } else {
                // @todo not sure if this is necessary... may cause bugs/unpredictable behavior even...
                throw new \OutOfBoundsException('Cannot read from ' . $this->name());
            }
        }
        $this->updateInfo();
        return $data;
    }

    /**
     * @inheritDoc
     */
    public function readLine($max = null, $eol = "\n")
    {
        if (false === ($line = stream_get_line($this->source, $max ?: self::MAX_LINE_LENGTH, $eol))) {
            if ($this->isEof()) {
                throw new EndOfFileException('Cannot read line from ' . $this->name() . '. End of file has been reached.');
            } else {
                // @todo not sure if this is necessary... may cause bugs/unpredictable behavior even...
                throw new \OutOfBoundsException('Cannot read line from ' . $this->name());
            }
        }
        $this->updateInfo();
        return $line;
    }

    /**
     * Have we reached the EOF (end of file/stream)?
     *
     * @return boolean
     * @access public
     */
    public function isEof()
    {
        return feof($this->source);
    }

    /**
     * File must be able to be rewound when the end is reached
     *
     * @return void
     * @access public
     */
    public function rewind()
    {
        rewind($this->source);
        $this->updateInfo();
    }
}
