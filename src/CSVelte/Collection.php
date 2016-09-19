<?php
/**
 * CSVelte: Slender, elegant CSV for PHP
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

use \Iterator;
use \Countable;
use \ArrayAccess;
use \OutOfBoundsException;
use \InvalidArgumentException;

/**
 * Collection class.
 *
 * Represents a collection of data. This is a one-dimensional structure that is
 * represented internally with a simple array. It provides several very
 * convenient operations to be performed on its data.
 *
 * @package   CSVelte
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @since     v0.2.1
 * @replaces  \CSVelte\Utils
 */
class Collection implements Countable, ArrayAccess
{
    /**
     * Underlying array
     * @var array The array of data for this collection
     */
    protected $data = [];

    /**
     * Collection constructor.
     *
     * Set the data for this collection using $data
     *
     * @param array|ArrayAccess|null $data Either an array or an object that can be accessed
     *     as if it were an array.
     */
    public function __construct($data = null)
    {
        $this->assertArrayOrIterator($data);
        if (!is_null($data)) {
            $this->setData($data);
        }
    }

    /**
     * Set internal collection data.
     *
     * Use an array or iterator to set this collection's data.
     *
     * @param array|Iterator $data The data to set for this collection
     * @return $this
     * @throws InvalidArgumentException If invalid data type
     */
    protected function setData($data)
    {
        $this->assertArrayOrIterator($data);
        foreach ($data as $key => $val) {
            $this->data[$key] = $val;
        }
        return $this;
    }

    /**
     * Get data as an array.
     *
     * @return array Collection data as an array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * Merge array into this collection.
     *
     * Pass an array to this method ot have it merged into this collection.
     *
     * @param array $data Data to merge into the collection
     * @param boolean Should existing values be overwritten?
     * @return $this;
     */
    public function merge(array $data, $overwrite = true)
    {
        $this->assertArrayOrIterator($data);
        foreach ($data as $key => $val) {
            $this->set($key, $value, $overwrite);
        }
        return $this;
    }

    /**
     * Set value for given key.
     *
     * @param [type]  $key       [description]
     * @param [type]  $value     [description]
     * @param boolean $overwrite [description]
     */
    public function set($key, $value = null, $overwrite = true)
    {
        $this->assertArrayOrIterator($data);
        if (!array_key_exists($key, $this->data) || $overwrite) {
            $this->data[$key] = $value;
        }
        return $this;
    }

    public function contains($val, $key = null)
    {
        if (is_callable($func = $val)) {
            foreach ($this->data as $key => $val) {
                if ($func($val, $key)) return true;
            }
        } elseif (in_array($val, $this->data)) {
            return (is_null($key) || (isset($this->data[$key]) && $this->data[$key] == $val));
        }
        return false;
    }

    public function has($key)
    {
        return array_key_exists($key, $this->data);
    }

    public function get($key, $default = null, $throwExc = false)
    {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        } else {
            if ($throwExc) {
                throw new OutOfBoundsException("Collection data does not contain value for given key: " . $key);
            }
        }
        return $default;
    }

    // @todo create an alias for this... maybe delete() or remove()
    public function offsetUnset($offset)
    {
        if ($this->has($offset)) {
            unset($this->data[$offset]);
        }
    }

    // @todo create an alias for this... maybe delete() or remove()
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    // @todo create an alias for this... maybe delete() or remove()
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    public function offsetGet($offset)
    {
        $this->get($offset);
    }

    public function count()
    {
        return count($this->data);
    }

    /**
     * Apply a callback to each element in the collection and return the
     * resulting collection
     *
     * @return array An array of key/value pairs
     */
    public function map(Callable $func)
    {
        return new self(array_map($func, $this->data));
    }

    /**
     * Walk through each item in the collection, calling a function for each
     * item in the collection.
     *
     * @return $this
     */
    public function walk(Callable $func, $userdata = null)
    {
        array_walk($this->data, $func, $userdata);
        return $this;
    }

    /**
     * Call a user function for each item in the collection. If function returns
     * false, loop is terminated.
     *
     * @return $this
     * @todo I'm not entirely sure what this method should do... return new
     *     collection? modify this one?
     */
    public function each(Callable $func)
    {
        foreach ($this->data as $key => $val) {
            if (!$ret = $func($val, $key)) {
                if ($ret === false) break;
            }
        }
        return $this;
    }

    /**
     * Filter out unwanted items using a callback function.
     *
     * @param Callable $func
     * @return $this
     */
    public function filter(Callable $func)
    {
        $keys = [];
        foreach ($this->data as $key => $val) {
            if (false === $func($val, $key)) $keys[$key] = true;
        }
        $this->data = array_diff_key($this->data, $keys);
    }

    public function first(Callable $func)
    {
        foreach ($this->data as $key => $val) {
            if ($func($val, $key)) return $val;
        }
    }

    public function flip()
    {
        $this->data = array_flip($this->data);
        return $this;
    }

    /**
     * Return an array of key/value pairs.
     *
     * Return array can either be in [key,value] or [key => value] format. The
     * first is the default.
     *
     * @param boolean Whether you want pairs in [k => v] rather than [k, v] format
     * @return array An array of key/value pairs
     */
    public function pairs($alt = false)
    {
        return array_map(
            function ($key, $val) use ($alt) {
                if ($alt) {
                    return [$key => $val];
                } else {
                    return [$key, $val];
                }
            },
            array_keys($this->data),
            array_values($this->data)
        );
    }

    /**
     * Get average of data items.
     *
     * @return mixed The average of all items in collection
     */
    public function average()
    {
        $total = array_sum($this->data);
        $count = count($this->data);
        return $total / $count;
    }

    /**
     * Get mode of data items.
     *
     * @return mixed The mode of all items in collection
     */
    public function mode()
    {
        $counts = array_count_values($this->data);
        arsort($counts);
        return key($counts);
    }

    /**
     * Get median of data items.
     *
     * @return mixed The median of all items in collection
     */
    public function median()
    {
        $count = count($this->data);
        natcasesort($this->data);
        $middle = ($count / 2) - 1;
        $values = array_values($this->data);
        if ($count % 2 == 0) {
            // even number, use middle
            $low = $values[$middle];
            $high = $values[$middle + 1];
            return ($low + $high) / 2;
        }
        // odd number return median
        return $values[$middle];
    }

    protected function assertArrayOrIterator($data)
    {
        if (is_array($data) || $data instanceof Iterator) {
            return;
        }
        throw new InvalidArgumentException("Invalid type for collection data: " . gettype($data));
    }
}
