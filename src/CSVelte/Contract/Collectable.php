<?php

/*
 * CSVelte: Slender, elegant CSV for PHP
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV
 * standardization efforts, CSVelte was written in an effort to take all the
 * suck out of working with CSV.
 *
 * @version   {version}
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace CSVelte\Contract;

interface Collectable
{
    public function __construct();

    public function __invoke($val = null, $key = null);

    public function toArray();

    public function keys();

    public function values();

    public function merge($data);

    public function contains($val, $index = null);

    public function pop();

    public function shift();

    public function push(...$items);

    public function unshift(...$items);

    public function pad($size, $with = null);

    public function has($index);

    public function get($index, $default = null);

    public function set($index, $value = null);

    public function map(callable $callback);

    public function walk(callable $callback, $extraContext = null);

    public function reduce(callable $callback, $initial = null);

    public function filter(callable $callback);

    public function first(callable $callback);

    public function last(callable $callback);

    public function frequency();

    public function unique();

    public function duplicates();

    public function flip();

    public function reverse();

    public function pairs();

    public function join($glue = '');

    public function isEmpty();

    public function value(callable $callback);

    public function sort(callable $callback, $preserveKeys = true);

    public function reverse($preserveKeys = true);
}
