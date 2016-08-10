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
namespace CSVelte\Output;

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
