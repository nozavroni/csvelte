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
     * Class constructor
     *
     * @param string The path and filename of the input file to read from
     * @return void
     * @access public
     */
    public function __construct($stream)
    {
        if (false === ($this->source = @fopen($stream, 'r'))) {
            // throw exception
            throw new \Exception('Cannot open ' . $this->name());
        }
        $this->updateInfo();
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

    /**
     * @inheritDoc
     */
    public function read($length)
    {
        if (false === ($data = fread($this->source, $length))) {
            // throw exception
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
