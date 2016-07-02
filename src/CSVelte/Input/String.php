<?php namespace CSVelte\Input;

use CSVelte\Contract\Readable;
use CSVelte\Exception\ImmutableException;

/**
 * CSVelte\Input\string
 * Allows Reader to read from any arbitrary string of CSV data by temporarily
 * placing the string into memory and reading from there.g
 *
 * @package   CSVelte\Reader
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class String extends SeekableStream
{
    const STREAM_MEMORY = 'php://memory';

    public function __construct($string)
    {
        $this->remember($string);
    }

    protected function remember($str)
    {
        $name = self::STREAM_MEMORY;
        if (false === ($this->source = @fopen($name, 'w+'))) {
            // @todo custom exception
            throw new InvalidStreamUriException('Cannot open stream: ' . $name);
        }
        if (false === fwrite($this->source, $str)) {
            // @todo throw custom exception
            throw new \Exception('Cannot write to ' . $name);
        }
        $this->rewind();
        $this->updateInfo();
    }
}
