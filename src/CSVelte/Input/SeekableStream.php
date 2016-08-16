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

use CSVelte\Contract\Seekable;

/**
 * CSVelte\Input\SeekableStream
 * Represents a stream source for CSV data.
 */
class SeekableStream extends Stream implements Seekable
{
    public function seek($pos)
    {
        fseek($this->source, $pos);
        $this->updateInfo();

        return $this;
    }
}
