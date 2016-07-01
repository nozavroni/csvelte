<?php namespace CSVelte\Input;
/**
 * CSVelte\Input\Stream
 * Represents a stream source for CSV data
 *
 * @package   CSVelte
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class Stream implements InputInterface
{
    /**
     * @var resource The stream resource to input file
     */
    protected $source;

    /**
     * @var array An array of meta data about the source stream
     */
    protected $info;

    /**
     * Class constructor
     *
     * @param string The path and filename of the input file to read from
     * @return void
     * @access public
     */
    public function __construct($stream)
    {
        list($scheme, $path, $name) = $this->parseStreamName($stream);
        if (false === ($this->source = @fopen($path . DIRECTORY_SEPARATOR . $name, 'r'))) {
            // @todo custom exception
            throw new \Exception('Cannot open ' . $this->name());
        }
        $this->updateInfo();
    }

    /**
     * Parse stream name and extract pertinant information
     *
     * @param string The name of the stream to parse
     * @return array(scheme, dir, name)
     * @todo Streams can contain multiple instances of :// so make sure you
     *     account for that when writing this method (and for the whole class)
     */
    protected function parseStreamName($stream)
    {
        $uri = explode('://', $stream);
        if (count($uri) < 2) {
            // @todo custom exception
            throw new \Exception('Invalid stream name: ' . $stream);
        }
        if (!$path = realpath($dirname = dirname($uri[1]))) {
            // @todo custom exception
            throw new \Exception('Unable to resolve specified path: ' . $dir);
        }
        return array($uri[0], $path, basename($uri[1]));

    }

    protected function updateInfo()
    {
        $this->info = stream_get_meta_data($this->source);
        return $this->position = ftell($this->source);
    }

    /**
     * @inheritDoc
     */
    public function name()
    {
        return basename($this->info['uri']);
    }

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
            // @todo custom exception
            throw new \Exception('Cannot read from ' . $this->name());
        }
        $this->updateInfo();
        return $data;
    }

    /**
     * @inheritDoc
     */
    public function readLine()
    {

    }
}
