<?php
/**
 * CSVelte: Slender, elegant CSV for PHP
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV
 * standardization efforts, CSVelte was written in an effort to take all the
 * suck out of working with CSV.
 *
 * @version   v0.1
 *
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace CSVelte\Input;

/**
 * CSVelte\Input\string
 * Allows Reader to read from any arbitrary string of CSV data by temporarily
 * placing the string into memory and reading from there.g.
 *
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
            throw new InvalidStreamUriException('Cannot open stream: '.$name);
        }
        if (false === fwrite($this->source, $str)) {
            // @todo throw custom exception
            throw new \Exception('Cannot write to '.$name);
        }
        $this->rewind();
        $this->updateInfo();
    }
}
