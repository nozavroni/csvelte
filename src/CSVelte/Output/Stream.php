<?php namespace CSVelte\Output;

use CSVelte\Contract\Writable;
use CSVelte\Traits\StreamIO;

/**
 * CSVelte Stream Writer
 *
 * @package   CSVelte
 * @subpackage CSVelte\Writable
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class Stream implements Writable
{
    use StreamIO;

    const FOPEN_MODE = 'w';

    public function write($data)
    {
        return fwrite($this->source, $data);
    }
}
