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
use CSVelte\Collection;

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
 * @todo      Most of this class's methods will return a new Collection class
 *     rather than modify the existing class. There needs to be a clear distinction
 *     as to which ones don't and why. Also, some methods return a single value.
 *     These also need to be clear.
 * @todo      Need to make sure method naming, args, return values, concepts, etc.
 *     are consistent. This is a very large class with a LOT of methods. It will
 *     be very difficult to not let it blow up and get extremely messy. Go through
 *     and refactor each method. Make sure there is nothing superfluous and that
 *     everything makes sense and is intuitive to use. Also, because this class
 *     is so enourmous it is going to be a bitch to test. Good test coverage is
 *     going to require a LOT of tests. So put that on the list as well...
 * @todo      Implement whichever SPL classes/interfaces you can (that make sense).
 *     Probably a good idea to implement/extend some of these:
 *         Interfaces - RecursiveIterator, SeekableIterator, OuterIterator, IteratorAggregate
 *         Classes - FilterIterator, CallbackFilterIterator, CachingIterator, IteratorIterator, etc.
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
     * @param null|array $val If an array, it will be merged into the collection
     *     If both this arg and second arg are null, underlying data array will be returned
     * @param null|any $key If null and first arg is callable, this method will call map with callable
     *     If this value is not null but first arg is, it will call $this->offsetUnset($key)
     *     If this value is not null and first arg is anything other than callable, it will return $this->set($key, $val)
     * @see the description for various possible method signatures
     * @return mixed The return value depends entirely upon the arguments passed
     *     to it. See description for various possible arguments/return value combinations
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
                    } /*else {
                        return $this->set($key, $val);
                    }*/
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
     * @return \CSVelte\Collection The collection's keys (as a collection)
     */
    public function keys()
    {
        return new self(array_keys($this->data));
    }

    /**
     * Merge data (array or iterator)
     *
     * Pass an array to this method to have it merged into the collection. A new
     * collection will be created with the merged data and returned.
     *
     * @param array|iterator $data Data to merge into the collection
     * @param boolean $overwrite Whether existing values should be overwritten
     * @return \CSVelte\Collection A new collection with $data merged into it
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
     * Set value for the given key.
     *
     * Given $key, this will set $this->data[$key] to the value of $val. If that
     * index already has a value, it will be overwritten unless $overwrite is set
     * to false. In that case nothing happens.
     *
     * @param any $key The key you want to set a value for
     * @param any $value The value you want to set key to
     * @param boolean $overwrite Whether to overwrite existing value
     * @return $this
     */
    public function set($key, $value = null, $overwrite = true)
    {
        if (!array_key_exists($key, $this->data) || $overwrite) {
            $this->data[$key] = $value;
        }
        return $this;
    }

    /**
     * Test whether this collection contains the given value, optionally at a
     * specific key.
     *
     * This will return true if the collection contains a value equivalent to $val.
     * If $val is a callable (function/method), than the callable will be called
     * with $val, $key as its arguments (in that order). If the callable returns
     * any truthy value, than this method will return true.
     *
     * @param any|callable $val Either the value to check for or a callable that
     *     accepts $key,$val and returns true if collection contains $val
     * @param any $key If not null, the only the value for this key will be checked
     * @return boolean True if this collection contains $val, $key
     */
    public function contains($val, $key = null)
    {
        if (is_callable($callback = $val)) {
            foreach ($this->data as $key => $val) {
                if ($callback($val, $key)) return true;
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
     * @param string $key The key to compare to $val
     * @param mixed|Callable $val Either a value to test against or a callable to
     *     run your own custom "where comparison logic"
     * @param string $comp The type of comparison operation ot use (such as "=="
     *     or "instanceof"). Must be one of the self::WHERE_* constants' values
     *     listed at the top of this class.
     * @return \CSVelte\Collection A collection of rows that meet the criteria
     *     specified by $key, $val, and $comp
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
     * Get the key at a given numerical position.
     *
     * This method will give you the key at the specified numerical offset,
     * regardless of how it's indexed (associatively, unordered numerical, etc.).
     * This allows you to find out what the first key is. Or the second. etc.
     *
     * @param int $pos Numerical position
     * @return mixed The key at numerical position
     * @throws \OutOfBoundsException If you request a position that doesn't exist
     * @todo Allow negative $pos to start counting from end
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
     * Get the value at a given numerical position.
     *
     * This method will give you the value at the specified numerical offset,
     * regardless of how it's indexed (associatively, unordered numerical, etc.).
     * This allows you to find out what the first value is. Or the second. etc.
     *
     * @param int $pos Numerical position
     * @return mixed The value at numerical position
     * @throws \OutOfBoundsException If you request a position that doesn't exist
     * @todo Allow negative $pos to start counting from end
     */
    public function getValueAtPosition($pos)
    {
        return $this->data[$this->getKeyAtPosition($pos)];
    }

    /**
     * Determine if this collection has a value at the specified numerical position.
     *
     * @param int $pos Numerical position
     * @return boolean Whether there exists a value at specified position
     */
    public function hasPosition($pos)
    {
        try {
            $this->getKeyAtPosition($pos);
        } catch (OutOfBoundsException $e) {
            return false;
        }
        return true;
    }

    /**
     * "Pop" an item from the end of a collection.
     *
     * Removes an item from the bottom of the collection's underlying array and
     * returns it. This will actually remove the item from the collection.
     *
     * @return mixed Whatever the last item in the collection is
     */
    public function pop()
    {
        return array_pop($this->data);
    }

    /**
     * "Shift" an item from the top of a collection.
     *
     * Removes an item from the top of the collection's underlying array and
     * returns it. This will actually remove the item from the collection.
     *
     * @return mixed Whatever the first item in the collection is
     */
    public function shift()
    {
        return array_shift($this->data);
    }

    /**
     * "Push" an item onto the end of the collection.
     *
     * Adds item(s) to the end of the collection's underlying array.
     *
     * @param mixed ... The item(s) to push onto the end of the collection. You may
     *     also add additional arguments to push multiple items onto the end
     * @return $this
     */
    public function push()
    {
        foreach (func_get_args() as $arg) {
            array_push($this->data, $arg);
        }
        return $this;
    }

    /**
     * "Unshift" an item onto the beginning of the collection.
     *
     * Adds item(s) to the beginning of the collection's underlying array.
     *
     * @param mixed ... The item(s) to push onto the top of the collection. You may
     *     also add additional arguments to add multiple items
     * @return $this
     */
    public function unshift()
    {
        foreach (array_reverse(func_get_args()) as $arg) {
            array_unshift($this->data, $arg);
        }
        return $this;
    }

    /**
     * "Insert" an item at a given numerical position.
     *
     * Regardless of how the collection is keyed (numerically or otherwise), this
     * method will insert an item at a given numerical position. If the given
     * position is more than there are items in the collection, the given item
     * will simply be added to the end. Nothing is overwritten with this method.
     * All elements that come after $offset will simply be shifted a space.
     *
     * Note: This method is one of the few that will modify the collection in
     *       place rather than returning a new one.
     *
     * @param mixed ... The item(s) to push onto the top of the collection. You may
     *     also add additional arguments to add multiple items
     * @return $this
     */
    public function insert($offset, $item)
    {
        $top = array_slice($this->data, 0, $offset);
        $bottom = array_slice($this->data, $offset);
        $this->data = array_merge($top, [$item], $bottom);
        return $this;
    }

    /**
     * Pad collection to specified length.
     *
     * Pad the collection to a specific length, filling it with a given value. A
     * new collection with padded values is returned.
     *
     * @param  int $size The number of values you want this collection to have
     * @param  any $with The value you want to pad the collection with
     * @return \CSVelte\Collection A new collection, padded to specified size
     */
    public function pad($size, $with = null)
    {
        return new self(array_pad($this->data, (int) $size, $with));
    }

    /**
     * Check if this collection has a value at the given key.
     *
     * If this is a tabular data collection, this will check if the table has the
     * given key by default. You can change this behavior by passing false as the
     * second argument (this will change the behavior to check for a given key
     * at the row-level so it will likely only ever be numerical).
     *
     * @param any $key The key you want to check
     * @return boolean Whether there's a value at $key
     */
    public function has($key, $column = true)
    {
        // we only need to check one row for the existance of $key because the
        // isTabular() method ensures every row has the same keys
        if ($column && $this->isTabular() && $first = reset($this->data)) {
            return array_key_exists($key, $first);
        }
        // if we don't have tabular data or we don't want to check for a column...
        return array_key_exists($key, $this->data);
    }

    /**
     * Get the value at the given key.
     *
     * If there is a value at the given key, it will be returned. If there isn't,
     * a default may be specified. If you would like for this method to throw an
     * exception when there is no value at $key, pass true as the third argument
     *
     * @param  any  $key      The key you want to test for
     * @param  any  $default  The default to return if there is no value at $key
     * @param  boolean $throwExc Whether to throw an exception on failure to find
     *     a value at the given key.
     * @return mixed            Either the value at $key or the specified default
     *     value
     * @throws \OutOfBoundsException If value can't be found at $key and $throwExc
     *     is set to true
     */
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

    /**
     * Unset value at the given offset.
     *
     * This method is used when the end-user uses a colleciton as an array and
     * calls unset($collection[5]).
     *
     * @param mixed $offset The offset at which to unset
     * @return $this
     * @todo create an alias for this... maybe delete() or remove()
     */
    public function offsetUnset($offset)
    {
        if ($this->has($offset)) {
            unset($this->data[$offset]);
        }
        return $this;
    }

    /**
     * Alias of self::has
     *
     * @param int|mixed The offset to test for
     * @return boolean Whether a value exists at $offset
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * Alias of self::set
     *
     * @param int|mixed The offset to set
     * @param any The value to set it to
     * @return boolean
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
        return $this;
    }

    /**
     * Alias of self::get
     *
     * @param int|mixed The offset to get
     * @return mixed The value at $offset
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Count the items in this collection.
     *
     * Returns either the number of items in the collection or, if this is a
     * collection of tabular data, and you pass true as the first argument, you
     * will get back a collection containing the count of each row (which will
     * always be the same so maybe I should still just return an integer).
     *
     * @param boolean $multi Whether to count just the items in the collection or
     *     to count the items in each tabular data row.
     * @return int|\CSVelte\Collection Either an integer count or a collection of counts
     */
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
     * Collection map.
     *
     * Apply a callback to each element in the collection and return the
     * resulting collection. The resulting collection will contain the return
     * values of each call to $callback.
     *
     * @param Callable $callback A callback to apply to each item in the collection
     * @return \CSVelte\Collection A collection of callback return values
     */
    public function map(Callable $callback)
    {
        return new self(array_map($callback, $this->data));
    }

    /**
     * Walk the collection.
     *
     * Walk through each item in the collection, calling a function for each
     * item in the collection. This is one of the few methods that doesn't return
     * a new collection. All changes will be to the existing collection object.
     *
     * Note: return false from the collback to stop walking.
     *
     * @param Callable $callback A callback function to call for each item in the collection
     * @param any $userdata Any extra data you'd like passed to your callback
     * @return $this
     */
    public function walk(Callable $callback, $userdata = null)
    {
        array_walk($this->data, $callback, $userdata);
        return $this;
    }

    /**
     * Call a user function for each item in the collection. If function returns
     * false, loop is terminated.
     *
     * @return $this
     * @todo I'm not entirely sure what this method should do... return new
     *     collection? modify this one?
     * @todo This method appears to be a duplicate of walk(). Is it even necessary?
     */
    public function each(Callable $callback)
    {
        foreach ($this->data as $key => $val) {
            if (!$ret = $callback($val, $key)) {
                if ($ret === false) break;
            }
        }
        return $this;
    }

    /**
     * Reduce collection to single value.
     *
     * Reduces the collection to a single value by calling a callback for each
     * item in the collection, carrying along an accumulative value as it does so.
     * The final value is then returned.
     *
     * @param Callable $callback The function to reduce the collection
     * @param any $initial The initial value to set the accumulative value to
     * @return mixed Whatever the final value from the callback is
     */
    public function reduce(Callable $callback, $initial = null)
    {
        return array_reduce($this->data, $callback, $initial);
    }

    /**
     * Filter out unwanted items using a callback function.
     *
     * @param Callable $callback
     * @return CSVelte\Collection A new collection with filtered items removed
     */
    public function filter(Callable $callback)
    {
        $keys = [];
        foreach ($this->data as $key => $val) {
            if (false === $callback($val, $key)) $keys[$key] = true;
        }
        return new self(array_diff_key($this->data, $keys));
    }

    /**
     * Get first match.
     *
     * Get first value that meets criteria specified with $callback function.
     *
     * @param Callable $callback A callback with arguments ($val, $key). If it
     *     returns true, that $val will be returned.
     * @return mixed The first $val that meets criteria specified with $callback
     */
    public function first(Callable $callback)
    {
        foreach ($this->data as $key => $val) {
            if ($callback($val, $key)) return $val;
        }
        return null;
    }

    /**
     * Get last match.
     *
     * Get last value that meets criteria specified with $callback function.
     *
     * @param Callable $callback A callback with arguments ($val, $key). If it
     *     returns true, that $val will be returned.
     * @return mixed The last $val that meets criteria specified with $callback
     */
    public function last(Callable $callback)
    {
        $elem = null;
        foreach ($this->data as $key => $val) {
            if ($callback($val, $key)) $elem = $val;
        }
        return $elem;
    }

    /**
     * Collection value frequency.
     *
     * Returns an array where the key is a value in the collection and the value
     * is the number of times that value appears in the collection.
     *
     * @return CSVelte\Collection A collection of value frequencies (see description)
     */
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

    /**
     * Unique collection.
     *
     * Returns a collection with duplicate values removed. If two-dimensional,
     * then each array within the collection will have its duplicates removed.
     *
     * @return CSVelte\Collection A new collection with duplicate values removed.
     */
    public function unique()
    {
        if (false !== ($condRet = $this->if2DMapInternalMethod(__METHOD__))) {
            return $condRet;
        }
        return new self(array_unique($this->data));
    }

    /**
     * Reverse keys/values.
     *
     * Get a new collection where the keys and values have been swapped.
     *
     * @return CSVelte\Collection A new collection where keys/values have been swapped
     */
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
     * @return CSVelte\Collection A collection of key/value pairs
     */
    public function pairs($alt = false)
    {
        return new self(array_map(
            function ($key, $val) use ($alt) {
                if ($alt) {
                    return [$key => $val];
                } else {
                    return [$key, $val];
                }
            },
            array_keys($this->data),
            array_values($this->data)
        ));
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
     * If two-dimensional it will return a collection of averages.
     *
     * @return mixed|CSVelte\Collection The average of all items in collection
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
     * Get largest item in the collection
     *
     * @return mixed The largest item in the collection
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
     * Get smallest item in the collection
     *
     * @return mixed The smallest item in the collection
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

    /**
     * Join items together into a string
     *
     * @param string $glue The string to join items together with
     * @return string A string with all items in the collection strung together
     * @todo Make this work with 2D collection
     */
    public function join($glue)
    {
        return implode($glue, $this->data);
    }

    /**
     * Is the collection empty?
     *
     * @return boolean Whether the collection is empty
     */
    public function isEmpty()
    {
        return empty($this->data);
    }

    /**
     * Immediately invoke a callback.
     *
     * @param Callable $callback A callback to invoke with ($this)
     * @return mixed Whatever the callback returns
     */
    public function value(Callable $callback)
    {
        return $callback($this);
    }

    /**
     * Sort the collection.
     *
     * This method can sort your collection in any which way you please. By
     * default it uses a case-insensitive natural order algorithm, but you can
     * pass it any sorting algorithm you like.
     *
     * @param Callable $sort_func The sorting function you want to use
     * @param boolean $preserve_keys Whether you want to preserve keys
     * @return CSVelte\Collection A new collection sorted by $callback
     */
    public function sort(Callable $callback = null, $preserve_keys = true)
    {
        if (is_null($callback)) $callback = 'strcasecmp';
        if (!is_callable($callback)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid argument supplied for %s. Expected %s, got: "%s".',
                __METHOD__,
                'Callable',
                gettype($callback)
            ));
        }
        $data = $this->data;
        if ($preserve_keys) {
            uasort($data, $callback);
        } else {
            usort($data, $callback);
        }
        return new self($data);
    }

    /**
     * Order tabular data.
     *
     * Order a tabular dataset by a given key/comparison algorithm
     *
     * @param string $key The key you want to order by
     * @param Callable $cmp The sorting comparison algorithm to use
     * @param boolean $preserve_keys Whether keys should be preserved
     * @return CSVelte\Collection A new collection sorted by $cmp and $key
     */
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

    /**
     * Reverse collection order.
     *
     * Reverse the order of items in a collection. Sometimes it's easier than
     * trying to write a particular sorting algurithm that sorts forwards and back.
     *
     * @param boolean $preserve_keys Whether keys should be preserved
     * @return CSVelte\Collection A new collection in reverse order
     */
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

    /**
     * Is this collection two-dimensional
     *
     * If all items of the collection are arrays this will return true.
     *
     * @return boolean whether this is two-dimensional
     */
    public function is2D()
    {
        return !$this->contains(function($val){
            return !is_array($val);
        });
        return false;
    }

    /**
     * Is this a tabular collection?
     *
     * If this is a two-dimensional collection with the same keys in every array,
     * this method will return true.
     *
     * @return boolean Whether this is a tabular collection
     */
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
