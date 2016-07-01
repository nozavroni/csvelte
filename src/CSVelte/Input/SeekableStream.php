<?php namespace CSVelte\Input;

use CSVelte\Contract\Readable;
use CSVelte\Contract\Seekable;

/**
 * CSVelte\Input\SeekableStream
 * Represents a stream source for CSV data
 *
 * @package   CSVelte
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @todo      Should I imlpement this as a "trait"?
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
