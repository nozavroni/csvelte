<?php
/**
 * CSVelte: Slender, elegant CSV for PHP
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV
 * standardization efforts, CSVelte was written in an effort to take all the
 * suck out of working with CSV.
 *
 * @version   v0.1
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace CSVelte\Traits;

use CSVelte\Exception\InvalidStreamUriException;
use CSVelte\Exception\InvalidStreamResourceException;

trait StreamIO {

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
     * @access public
     */
    public function __construct($stream)
    {
        if (is_resource($stream)) {
            if ('stream' !== ($type = get_resource_type($stream))) {
                throw new InvalidStreamResourceException('Invalid resource type provided: ' . $type);
            }
        } else {
            if (false === ($stream = @fopen($stream, $this->getMode()))) {
                // @todo custom exception
                throw new InvalidStreamUriException('Cannot open stream: ' . $stream);
            }
        }
        $this->source = $stream;
        $this->updateInfo();
    }

    /**
     * Class destructor
     *
     * @return void
     * @access public
     */
    public function __destruct()
    {
        $this->close();
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

    /**
     * Retrieve underlying stream resource
     *
     * @return resource
     * @access public
     */
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

    abstract protected function getMode();
}
