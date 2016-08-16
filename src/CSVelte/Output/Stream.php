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
namespace CSVelte\Output;

use CSVelte\Contract\Writable;
use CSVelte\Traits\StreamIO;

/**
 * CSVelte Stream Writer.
 *
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class Stream implements Writable
{
    use StreamIO;

    /**
     * Get the "mode" used to open stream resource handle.
     *
     * @return string
     *
     * @see fopen function
     *
     * @todo I'm definitely not in love with this design but I'll refactor later
     */
    protected function getMode()
    {
        return 'w';
    }

    public function write($data)
    {
        return fwrite($this->source, $data);
    }
}
