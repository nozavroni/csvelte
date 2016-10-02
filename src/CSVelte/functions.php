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

use \InvalidArgumentException;

/**
 * Stream - streams various types of values and objects.
 *
 * You can pass a string, or an iterator, or an object with a __toString()
 * method to this function and it will find the best possible way to stream the
 * data from that object.
 *
 * @param mixed The item you want to stream
 * @return \CSVelte\IO\Stream A stream object
 * @since v0.2.1
 */
function streamize($obj = '')
{
    if ($obj instanceof Streamable) {
        return $obj;
    }

    // @todo add this
    // if (($resource = $obj) instanceof Resource) {
    //     return $resource() or $resource->stream();
    // }

    // @todo there needs to be a way to create a stream object from a resource
    //     object (other than streamize($resource)). I'm thinking something like
    //     $resource = new Resource(); $stream = $resource->toStream(); or $resource->stream();
    //     also possibly $resource = new Resource(); $stream = $resource();
    //     I kinda like the idea of passing around a $resource and then just invoking
    //     it to get a stream from it...
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
            fwrite($res->getHandle(), $obj);
            fseek($res->getHandle(), 0);
        }
        return $stream;
    }

    throw new InvalidArgumentException(sprintf(
        "Invalid argument type for %s: %s",
        __FUNCTION__,
        gettype($obj)
    ));
}

function stream_resource($uri, $mode = null, $context = null, $lazy = true)
{
    $res = (new Resource($uri, $mode))
        ->setContextResource($context);
    if (!$lazy) $res->connect();
    return $res;
}

function stream($uri, $mode = null, $context = null, $lazy = true)
{
    $res = stream_resource($uri, $mode, $context, $lazy);
    return new Stream($res);
}

/**
 * "Taste" a stream object.
 *
 * Pass any class that implements the "Streamable" interface to this function
 * to auto-detect "flavor" (formatting attributes).
 *
 * @param \CSVelte\Contract\Streamable Any streamable class to analyze
 * @return \CSVelte\Flavor A flavor representing str                                                                                                                                                                                                                                                                                                                                                           eam's formatting attributes
 * @since v0.2.1
 */
function taste(Streamable $str)
{
    $taster = new Taster($str);
    return $taster();
}

/**
 * Does dataset being streamed by $str have a header row?
 *
 * @param \CSVelte\Contract\Streamable $str Stream object
 * @return boolean Whether stream dataset has header
 * @since v0.2.1
 */
function taste_has_header(Streamable $str)
{
    $taster = new Taster($str);
    $flv = $taster();
    return $taster->lickHeader(
        $flv->delimiter,
        $flv->lineTerminator
    );
}

/**
 * Collection factory.
 *
 * Simply an alias to (new Collection($in)). Allows for a little more concise and
 * simpler instantiation of a collection. Also I plan to eventually support
 * additional input types that will make this function more flexible and forgiving
 * than simply instantiating a Collection object, but for now the two are identical.
 *
 * @param array|Iterator $in Either an array or an iterator of data
 * @return \CSVelte\Collection A collection object containing data from $in
 * @see CSVelte\Collection::__construct() (alias)
 * )
 */
function collect($in = null)
{
    return new Collection($in);
}
