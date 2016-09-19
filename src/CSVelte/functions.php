<?php
/**
 * CSVelte: Slender, elegant CSV for PHP
 *
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV
 * standardization efforts, CSVelte was written in an effort to take all the
 * suck out of working with CSV.
 *
 * @version   v0.2.1
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace CSVelte;
/**
 * Library Functions
 *
 * @package CSVelte
 * @subpackage functions
 * @since v0.2.1
 */

use \Iterator;
use CSVelte\Taster;
use CSVelte\Flavor;
use CSVelte\IO\Stream;
use CSVelte\IO\Resource;
use CSVelte\IO\IteratorStream;
use CSVelte\Contract\Streamable;

/**
 * Stream - streams various types of values and objects.
 *
 * You can pass a string, or an iterator, or an object with a __toString()
 * method to this function and it will find the best possible way to stream the
 * data from that object.
 *
 * @param mixed The item you want to stream
 * @return \CSVelte\IO\Stream A stream object
 */
function streamize($obj)
{
    if (is_resource($obj) && get_resource_type($obj) == 'stream') {
        return new Stream(new Resource($obj));
    }

    if ($obj instanceof Iterator) {
        return new IteratorStream($obj);
    }

    if (is_object($obj) && method_exists($obj, '__toString')) {
        $obj = (string) $obj;
    }
    if (is_string($obj)) {
        $stream = Stream::open('php://temp', 'r+');
        if ($obj !== '') {
            $res = $stream->getResource();
            fwrite($res(), $obj);
            fseek($res(), 0);
        }
        return $stream;
    }
}


/**
 * "Taste" a stream object.
 *
 * Pass any class that implements the "Streamable" interface to this function
 * to auto-detect "flavor" (formatting attributes).
 *
 * @param \CSVelte\Contract\Streamable Any streamable class to analyze
 * @return \CSVelte\Flavor A flavor representing stream's formatting attributes
 */
function taste(Streamable $streamable)
{
    $taster = new Taster($streamable);
    return $taster();
}
