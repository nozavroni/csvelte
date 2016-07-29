<?php namespace CSVelte\Output;

use CSVelte\Contract\Writable as Writable;

/**
 * CSVelte Stream Writer
 *
 * @package   CSVelte
 * @subpackage CSVelte\Writable
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class File extends Stream
{
    public function __construct($stream)
    {
        if (false === ($stream = fopen($stream, 'w'))) {
            throw new \Exception('Cannot write to ' . $stream);
        }
        $this->source = $stream;
    }
}
