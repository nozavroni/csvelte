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

    if ($obj instanceof Resource) {
        return $obj();
    }

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

/**
 * Stream resource factory.
 *
 * This method is just a shortcut to create a stream resource object using
 * a stream URI string.
 *
 * @param string $uri A stream URI
 * @param string $mode The access mode string
 * @param array|resource $context An array or resource with stream context options
 * @param bool $lazy Whether to lazy-open
 * @return $this
 * @since v0.2.1
 */
function stream_resource(
    $uri,
    $mode = null,
    $context = null,
    $lazy = true
) {
    $res = (new Resource($uri, $mode))
        ->setContextResource($context);
    if (!$lazy) $res->connect();
    return $res;
}

/**
 * Stream factory.
 *
 * This method is just a shortcut to create a stream object using a URI.
 *
 * @param string $uri A stream URI to open
 * @param string $mode The access mode string
 * @param array|resource $context An array or stream context resource of options
 * @param bool $lazy Whether to lazy-open
 * @return Stream
 * @since v0.2.1
 */
function stream(
    $uri,
    $mode = null,
    $context = null,
    $lazy = true
) {
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
 * @return \CSVelte\Flavor A flavor representing stream's formatting attributes
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
 * @since v0.2.1
 * @see CSVelte\Collection::__construct() (alias)
 */
function collect($in = null)
{
    return new Collection($in);
}

/**
 * Invoke a callable and return result.
 *
 * Pass in a callable followed by whatever arguments you want passed to
 * it and this function will invoke it with your arguments and return
 * the result.
 *
 * @param callable $callback The callback function to invoke
 * @param array ...$args The args to pass to your callable
 * @return mixed The result of your invoked callable
 * @since v0.2.1
 */
function invoke(Callable $callback, ...$args)
{
    return $callback(...$args);
}