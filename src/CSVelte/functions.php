<?php
/**
 * CSVelte: Slender, elegant CSV for PHP
 *
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV
 * standardization efforts, CSVelte was written in an effort to take all the
 * suck out of working with CSV.
 *
 * @copyright Copyright (c) 2018 Luke Visinoni
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   See LICENSE file (MIT license)
 */
namespace CSVelte;

use Iterator;
use InvalidArgumentException;
use CSVelte\Contract\Streamable;
use CSVelte\IO\Stream;
use CSVelte\IO\StreamResource;
use CSVelte\IO\IteratorStream;

/**
 * Stream - streams various types of values and objects.
 *
 * You can pass a string, or an iterator, or an object with a __toString()
 * method to this function and it will find the best possible way to stream the
 * data from that object.
 *
 * @param mixed $obj The item you want to stream
 *
 * @throws InvalidArgumentException
 *
 * @return Streamable
 *
 * @since v0.2.1
 */
function streamize($obj = '')
{
    if ($obj instanceof Streamable) {
        return $obj;
    }

    if ($obj instanceof StreamResource) {
        return $obj();
    }

    if (is_resource($obj) && get_resource_type($obj) == 'stream') {
        return new Stream(new StreamResource($obj));
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
            fwrite($res->getHandle(), $obj);
            fseek($res->getHandle(), 0);
        }

        return $stream;
    }

    throw new InvalidArgumentException(sprintf(
        'Invalid argument type for %s: %s',
        __FUNCTION__,
        gettype($obj)
    ));
}