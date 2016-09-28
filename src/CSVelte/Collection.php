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
use \RuntimeException;

use function CSVelte\collect;

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
 * @todo      Maybe methods should return a new collection rather than modifying
 *            this collection in place.
 * @replaces  \CSVelte\Utils
 */
class Collection implements Countable, ArrayAccess
{
    /**
     * Constants used as comparison operators in where() method
     */

    /** @var const Use this operator constant to test for identity (exact same) **/
    const WHERE_ID = '===';

    /** @var const Use this operator constant to test for non-identity **/
    const WHERE_NID = '!==';

    /** @var const Use this operator constant to test for equality **/
    const WHERE_EQ = '==';

    /** @var const Use this operator constant to test for non-equality **/
    const WHERE_NEQ = '!=';

    /** @var const Use this operator constant to test for less-than **/
    const WHERE_LT = '<';

    /** @var const Use this operator constant to test for greater-than or equal-to **/
    const WHERE_LTE = '<=';

    /** @var const Use this operator constant to test for greater-than **/
    const WHERE_GT = '>';

    /** @var const Use this operator constant to test for greater-than or equal-to **/
    const WHERE_GTE = '>=';

    /** @var const Use this operator constant to test for case insensitive equality **/
    const WHERE_LIKE = 'like';

    /** @var const Use this operator constant to test for case instensitiv inequality **/
    const WHERE_NLIKE = '!like';

    /** @var const Use this operator constant to test for descendants or instances of a class **/
    const WHERE_ISA = 'instanceof';

    /** @var const Use this operator constant to test for values that aren't descendants or instances of a class  **/
    const WHERE_NISA = '!instanceof';

    /** @var const Use this operator constant to test for internal PHP types **/
    const WHERE_TOF = 'typeof';

    /** @var const Use this operator constant to test for internal PHP type (negated) **/
    const WHERE_NTOF = '!typeof';

    /** @var const Use this operator constant to test against a regex pattern **/
    const WHERE_MATCH = 'match';

    /** @var const Use this operator constant to test against a regex pattern (negated) **/
    const WHERE_NMATCH = '!match';

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
     * Invokes the object as a function.
     *
     * If called with no arguments, it will return underlying data array
     * If called with array as first argument, array will be merged into data array
     * If called with second param, it will call $this->set($key, $val)
     * If called with null as first param and key as second param, it will call $this->offsetUnset($key)
     *
     * @return array The underlying data array
     */
    public function __invoke($val = null, $key = null)
    {
        if (is_null($val)) {
            if (is_null($key)) {
                return $this->data;
            } else {
                return $this->offsetUnset($key);
            }
        } else {
            if (is_null($key)) {
                if (is_array($val)) return $this->merge($val);
                else {
                    if (is_callable($val)) {
                        return $this->map($val);
                    }
                }
            } else {
                $this->offsetSet($key, $val);
            }
        }
        return $this;
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
     * @todo Recursively call toArray on anything inside this collection
     */
    public function toArray()
    {
        $data = [];
        foreach($this->data as $key => $val) {
            $data[$key] = (is_object($val) && method_exists($val, 'toArray')) ? $val->toArray() : $val;
        }
        return $data;
    }

    /**
     * Get array keys
     *
     * @return array An array of keys
     */
    public function keys()
    {
        return new self(array_keys($this->data));
    }

    /**
     * Merge data
     *
     * Pass an array to this method to have it merged into this collection.
     *
     * @param array $data Data to merge into the collection
     * @param boolean Should existing values be overwritten?
     * @return $this;
     */
    public function merge($data = null, $overwrite = true)
    {
        $this->assertArrayOrIterator($data);
        $coll = new self($this->data);
        foreach ($data as $key => $val) {
            $coll->set($key, $val, $overwrite);
        }
        return $coll;
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

    /**
     * Tabular Where Search.
     *
     * Search for values of a certain key that meet a particular search criteria
     * using either one of the "Collection::WHERE_" class constants, or its string
     * counterpart.
     *
     * Warning: Only works for tabular collections (2-dimensional data array)
     *
     * @param  [type] $key  [description]
     * @param  [type] $val  [description]
     * @param  [type] $comp [description]
     * @return [type]       [description]
     */
    public function where($key, $val, $comp = null)
    {
        $this->assertIsTabular();
        $data = [];
        if ($this->has($key, true)) {
            if (is_callable($val)) {
                foreach ($this->data as $ln => $row) {
                    if ($val($row[$key], $key)) {
                        $data[$ln] = $row;
                    }
                }
            } else {
                foreach ($this->data as $ln => $row) {
                    $fieldval = $row[$key];
                    switch (strtolower($comp)) {
                        case self::WHERE_ID:
                            $comparison = $fieldval === $val;
                            break;
                        case self::WHERE_NID:
                            $comparison = $fieldval !== $val;
                            break;
                        case self::WHERE_LT:
                            $comparison = $fieldval < $val;
                            break;
                        case self::WHERE_LTE:
                            $comparison = $fieldval <= $val;
                            break;
                        case self::WHERE_GT:
                            $comparison = $fieldval > $val;
                            break;
                        case self::WHERE_GTE:
                            $comparison = $fieldval >= $val;
                            break;
                        case self::WHERE_LIKE:
                            $comparison = strtolower($fieldval) == strtolower($val);
                            break;
                        case self::WHERE_NLIKE:
                            $comparison = strtolower($fieldval) != strtolower($val);
                            break;
                        case self::WHERE_ISA:
                            $comparison = (is_object($fieldval) && $fieldval instanceof $val);
                            break;
                        case self::WHERE_NISA:
                            $comparison = (!is_object($fieldval) || !($fieldval instanceof $val));
                            break;
                        case self::WHERE_TOF:
                            $comparison = (strtolower(gettype($fieldval)) == strtolower($val));
                            break;
                        case self::WHERE_NTOF:
                            $comparison = (strtolower(gettype($fieldval)) != strtolower($val));
                            break;
                        case self::WHERE_NEQ:
                            $comparison = $fieldval != $val;
                            break;
                        case self::WHERE_MATCH:
                            $match = preg_match($val, $fieldval);
                            $comparison = $match === 1;
                            break;
                        case self::WHERE_NMATCH:
                            $match = preg_match($val, $fieldval);
                            $comparison = $match === 0;
                            break;
                        case self::WHERE_EQ:
                        default:
                            $comparison = $fieldval == $val;
                            break;
                    }
                    if ($comparison) {
                        $data[$ln] = $row;
                    }
                }
            }
        }
        return new self($data);
    }

    /**
     * Get the key at a given numerical position
     *
     * @param int $pos Numerical position
     * @return mixed The key at numerical position
     */
    public function getKeyAtPosition($pos)
    {
        $i = 0;
        foreach ($this->data as $key => $val) {
            if ($i === $pos) return $key;
            $i++;
        }
        throw new OutOfBoundsException("Collection data does not contain a key at given position: " . $pos);
    }

    /**
     * Get the value at a given numerical position
     *
     * @param int $pos Numerical position
     * @return mixed The value at numerical position
     */
    public function getValueAtPosition($pos)
    {
        return $this->data[$this->getKeyAtPosition($pos)];
    }

    public function hasPosition($pos)
    {
        try {
            $this->getKeyAtPosition($pos);
        } catch (OutOfBoundsException $e) {
            return false;
        }
        return true;
    }

    public function pad($size, $with = null)
    {
        return new self(array_pad($this->data, (int) $size, $with));
    }

    public function has($key, $column = false)
    {
        if ($column && $this->isTabular() && $first = reset($this->data)) {
            return array_key_exists($key, $first);
        }
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
        return $this;
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
        return $this;
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function count($multi = false)
    {
        if ($multi) {
            // if every value is an array...
            if (false !== ($condRet = $this->if2DMapInternalMethod(__METHOD__))) {
                return $condRet;
            }
        }
        // just count main array
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

    public function reduce(Callable $func, $initial = null)
    {
        return array_reduce($this->data, $func, $initial);
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
        return null;
    }

    public function last(Callable $func)
    {
        $elem = null;
        foreach ($this->data as $key => $val) {
            if ($func($val, $key)) $elem = $val;
        }
        return $elem;
    }

    public function frequency()
    {
        if (false !== ($condRet = $this->if2DMapInternalMethod(__METHOD__))) {
            return $condRet;
        }
        $freq = [];
        foreach ($this->data as $val) {
            $key = is_numeric($val) ? $val : (string) $val;
            if (!isset($freq[$key])) {
                $freq[$key] = 0;
            }
            $freq[$key]++;
        }
        return new self($freq);
    }

    public function unique()
    {
        if (false !== ($condRet = $this->if2DMapInternalMethod(__METHOD__))) {
            return $condRet;
        }
        return new self(array_unique($this->data));
    }

    public function flip()
    {
        return new self(array_flip($this->data));
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
    public function sum()
    {
        if (false !== ($condRet = $this->if2DMapInternalMethod(__METHOD__))) {
            return $condRet;
        }
        $this->assertNumericValues();
        return array_sum($this->data);
    }

    /**
     * Get average of data items.
     *
     * @return mixed The average of all items in collection
     */
    public function average()
    {
        if (false !== ($condRet = $this->if2DMapInternalMethod(__METHOD__))) {
            return $condRet;
        }
        $this->assertNumericValues();
        $total = array_sum($this->data);
        $count = count($this->data);
        return $total / $count;
    }

    /**
     * Get mode of data items.
     *
     * @return mixed The mode of all items in collection
     */
    public function max()
    {
        if (false !== ($condRet = $this->if2DMapInternalMethod(__METHOD__))) {
            return $condRet;
        }
        $this->assertNumericValues();
        return max($this->data);
    }

    /**
     * Get mode of data items.
     *
     * @return mixed The mode of all items in collection
     */
    public function min()
    {
        if (false !== ($condRet = $this->if2DMapInternalMethod(__METHOD__))) {
            return $condRet;
        }
        $this->assertNumericValues();
        return min($this->data);
    }

    /**
     * Get mode of data items.
     *
     * @return mixed The mode of all items in collection
     */
    public function mode()
    {
        if (false !== ($condRet = $this->if2DMapInternalMethod(__METHOD__))) {
            return $condRet;
        }
        $strvals = $this->map(function($val){
            return (string) $val;
        });
        $this->assertNumericValues();
        $counts = array_count_values($strvals->toArray());
        arsort($counts);
        $mode = key($counts);
        return (strpos($mode, '.')) ? floatval($mode) : intval($mode);
    }

    /**
     * Get median of data items.
     *
     * @return mixed The median of all items in collection
     */
    public function median()
    {
        if (false !== ($condRet = $this->if2DMapInternalMethod(__METHOD__))) {
            return $condRet;
        }
        $this->assertNumericValues();
        $count = count($this->data);
        natcasesort($this->data);
        $middle = ($count / 2);
        $values = array_values($this->data);
        if ($count % 2 == 0) {
            // even number, use middle
            $low = $values[$middle - 1];
            $high = $values[$middle];
            return ($low + $high) / 2;
        }
        // odd number return median
        return $values[$middle];
    }

    public function join($glue)
    {
        return implode($glue, $this->data);
    }

    public function isEmpty()
    {
        return empty($this->data);
    }

    public function value(Callable $func)
    {
        return $func($this);
    }

    public function sort($sort_func = 'strcasecmp', $preserve_keys = true)
    {
        if (!is_callable($sort_func)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid argument supplied for %s. Expected %s, got: "%s".',
                __METHOD__,
                'Callable',
                gettype($sort_func)
            ));
        }
        $data = $this->data;
        if ($preserve_keys) {
            uasort($data, $sort_func);
        } else {
            usort($data, $sort_func);
        }
        return new self($data);
    }

    public function orderBy($key, Callable $cmp = null, $preserve_keys = true)
    {
        $this->assertIsTabular();
        return $this->sort(function($a, $b) use ($key, $cmp) {
            if (!isset($a[$key]) || !isset($b[$key])) {
                throw new RuntimeException('Cannot order collection by non-existant key: ' . $key);
            }
            if (is_null($cmp)) {
                return strcasecmp($a[$key], $b[$key]);
            } else {
                return $cmp($a[$key], $b[$key]);
            }
        }, $preserve_keys);
    }

    public function reverse($preserve_keys = true)
    {
        return new self(array_reverse($this->data, $preserve_keys));
    }

    protected function if2DMapInternalMethod($method)
    {
        if ($this->is2D()) {
            $method = explode('::', $method, 2);
            if (count($method) == 2) {
                $method = $method[1];
                return $this->map(function($val) use ($method) {
                    return (new self($val))->$method();
                });
            }
        }
        return false;
    }

    public function is2D()
    {
        return !$this->contains(function($val){
            return !is_array($val);
        });
    }

    public function isTabular()
    {
        if ($this->is2D()) {
            // look through each item in the collection and if an array, grab its keys
            // and throw them in an array to be analyzed later...
            $test = [];
            $this->walk(function($val, $key) use (&$test) {
                if (is_array($val)) {
                    $test[$key] = array_keys($val);
                    return true;
                }
                return false;
            });

            // if the list of array keys is shorter than the total amount of items in
            // the collection, than this is not tabular data
            if (count($test) != count($this)) return false;

            // loop through the array of each item's array keys that we just created
            // and compare it to the FIRST item. If any array contains different keys
            // than this is not tabular data.
            $first = array_shift($test);
            foreach ($test as $key => $keys) {
                $diff = array_diff($first, $keys);
                if (!empty($diff)) return false;
            }
            return true;
        }
        return false;
    }

    /**
     * Assert this collection is two-dimensional.
     *
     * Although a collection must be two-dimensional to be tabular, the opposite
     * is not necessarily true. This will throw an exception if this collection
     * contains anything but arrays.
     *
     * @throws
     */
    protected function assertIs2D()
    {
        if (!$this->is2D()) {
            throw new RuntimeException('Invalid data type, requires two-dimensional array.');
        }
    }

    protected function assertIsTabular()
    {
        if (!$this->isTabular()) {
            throw new RuntimeException('Invalid data type, requires tabular data (two-dimensional array where each sub-array has the same keys).');
        }
    }

    protected function assertNumericValues()
    {
        if ($this->contains(function($val){
            return !is_numeric($val);
        })) {
            // can't average non-numeric data
            throw new InvalidArgumentException(sprintf(
                "%s expects collection of integers or collection of arrays of integers",
                __METHOD__
            ));
        }
    }

    protected function assertArrayOrIterator($data)
    {
        if (is_null($data) || is_array($data) || $data instanceof Iterator) {
            return;
        }
        throw new InvalidArgumentException("Invalid type for collection data: " . gettype($data));
    }
}
