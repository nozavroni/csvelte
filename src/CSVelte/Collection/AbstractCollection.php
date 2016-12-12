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
namespace CSVelte\Collection;

use ArrayAccess;
use ArrayIterator;
use Closure;
use Countable;
use CSVelte\Contract\Collectable;
use InvalidArgumentException;
use Iterator;
use OutOfBoundsException;

use function CSVelte\is_traversable;

/**
 * Class AbstractCollection.
 *
 * This is the abstract class that all other collection classes are based on.
 * Although it's possible to use a completely custom Collection class by simply
 * implementing the "Collectable" interface, extending this class gives you a
 * whole slew of convenient methods for free.
 *
 * @package CSVelte\Collection
 *
 * @since v0.2.2
 *
 * @author Luke Visinoni <luke.visinoni@gmail.com>
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 *
 * @todo Implement Serializable, other Interfaces
 * @todo Implement __toString() in such a way that by deault it
 *     will return a CSV-formatted string but you can configure
 *     it to return other formats if you want
 */
abstract class AbstractCollection implements
    ArrayAccess,
    Countable,
    Iterator
    /*Collectable*/
{
    /**
     * @var array The collection of data this object represents
     */
    protected $data = [];

    /**
     * @var bool True unless we have advanced past the end of the data array
     */
    protected $isValid = true;

    /**
     * AbstractCollection constructor.
     *
     * @param mixed $data The data to wrap
     */
    public function __construct($data = [])
    {
        $this->setData($data);
    }

    /**
     * Invoke object.
     *
     * Magic "invoke" method. Called when object is invoked as if it were a function.
     *
     * @param mixed $val   The value (depends on other param value)
     * @param mixed $index The index (depends on other param value)
     *
     * @return mixed (Depends on parameter values)
     */
    public function __invoke($val = null, $index = null)
    {
        if (is_null($val)) {
            if (is_null($index)) {
                return $this->toArray();
            }

            return $this->delete($index);
        }
        if (is_null($index)) {
            // @todo cast $val to array?
                return $this->merge($val);
        }

        return $this->set($val, $index);
    }

    /**
     * Convert collection to string.
     *
     * @return string A string representation of this collection
     *
     * @todo Eventually I would like to add a $delim property so that
     *     I can easily join collection items together with a particular
     * character (or set of characters). I would then add a few methods
     * to change the delim property. It would default to a comma.
     */
    public function __toString()
    {
        return $this->join();
    }

    /** BEGIN ArrayAccess methods */

    /**
     * Whether a offset exists.
     *
     * @param mixed $offset An offset to check for.
     *
     * @return bool true on success or false on failure.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetexists.php
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * Offset to retrieve.
     *
     * @param mixed $offset The offset to retrieve.
     *
     * @return mixed Can return all value types.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetget.php
     */
    public function offsetGet($offset)
    {
        return $this->get($offset, null, true);
    }

    /**
     * Offset to set.
     *
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value  The value to set.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetset.php
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * Offset to unset.
     *
     * @param mixed $offset The offset to unset.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetunset.php
     */
    public function offsetUnset($offset)
    {
        $this->delete($offset);
    }

    /** END ArrayAccess methods */

    /** BEGIN Countable methods */
    public function count()
    {
        return count($this->data);
    }

    /** END Countable methods */

    /** BEGIN Iterator methods */

    /**
     * Return the current element.
     *
     * Returns the current element in the collection. The internal array pointer
     * of the data array wrapped by the collection should not be advanced by this
     * method. No side effects. Return current element only.
     *
     * @return mixed
     */
    public function current()
    {
        return current($this->data);
    }

    /**
     * Return the current key.
     *
     * Returns the current key in the collection. No side effects.
     *
     * @return mixed
     */
    public function key()
    {
        return key($this->data);
    }

    /**
     * Advance the internal pointer forward.
     *
     * Although this method will return the current value after advancing the
     * pointer, you should not expect it to. The interface does not require it
     * to return any value at all.
     *
     * @return mixed
     */
    public function next()
    {
        $next = next($this->data);
        $key  = key($this->data);
        if (isset($key)) {
            return $next;
        }
        $this->isValid = false;
    }

    /**
     * Rewind the internal pointer.
     *
     * Return the internal pointer to the first element in the collection. Again,
     * this method is not required to return anything by its interface, so you
     * should not count on a return value.
     *
     * @return mixed
     */
    public function rewind()
    {
        $this->isValid = !empty($this->data);

        return reset($this->data);
    }

    /**
     * Is internal pointer in a valid position?
     *
     * If the internal pointer is advanced beyond the end of the collection, this method will return false.
     *
     * @return bool True if internal pointer isn't past the end
     */
    public function valid()
    {
        return $this->isValid;
    }

    public function sort($alg = null)
    {
        if (is_null($alg)) {
            $alg = 'natcasesort';
        }
        $alg($this->data);

        return static::factory($this->data);
    }

    /**
     * Does this collection have a value at given index?
     *
     * @param mixed $index The index to check
     *
     * @return bool
     */
    public function has($index)
    {
        return array_key_exists($index, $this->data);
    }

    /**
     * Get value at a given index.
     *
     * Accessor for this collection of data. You can optionally provide a default
     * value for when the collection doesn't find a value at the given index. It can
     * also optionally throw an OutOfBoundsException if no value is found.
     *
     * @param mixed $index   The index of the data you want to get
     * @param mixed $default The default value to return if none available
     * @param bool  $throw   True if you want an exception to be thrown if no data found at $index
     *
     * @throws OutOfBoundsException If $throw is true and $index isn't found
     *
     * @return mixed The data found at $index or failing that, the $default
     *
     * @todo Use OffsetGet, OffsetSet, etc. internally here and on set, has, delete, etc.
     */
    public function get($index, $default = null, $throw = false)
    {
        if (isset($this->data[$index])) {
            return $this->data[$index];
        }
        if ($throw) {
            throw new OutOfBoundsException(__CLASS__ . ' could not find value at index ' . $index);
        }

        return $default;
    }

    /**
     * Set a value at a given index.
     *
     * Setter for this collection. Allows setting a value at a given index.
     *
     * @param mixed $index The index to set a value at
     * @param mixed $val   The value to set $index to
     *
     * @return $this
     */
    public function set($index, $val)
    {
        $this->data[$index] = $val;

        return $this;
    }

    /**
     * Unset a value at a given index.
     *
     * Unset (delete) value at the given index.
     *
     * @param mixed $index The index to unset
     * @param bool  $throw True if you want an exception to be thrown if no data found at $index
     *
     * @throws OutOfBoundsException If $throw is true and $index isn't found
     *
     * @return $this
     */
    public function delete($index, $throw = false)
    {
        if (isset($this->data[$index])) {
            unset($this->data[$index]);
        } else {
            if ($throw) {
                throw new OutOfBoundsException('No value found at given index: ' . $index);
            }
        }

        return $this;
    }

    /**
     * Does this collection have a value at specified numerical position?
     *
     * Returns true if collection contains a value (any value including null)
     * at specified numerical position.
     *
     * @param int $pos The position
     *
     * @return bool
     *
     * @todo I feel like it would make more sense  to have this start at position 1 rather than 0
     */
    public function hasPosition($pos)
    {
        try {
            $this->getKeyAtPosition($pos);

            return true;
        } catch (OutOfBoundsException $e) {
            return false;
        }
    }

    /**
     * Return value at specified numerical position.
     *
     * @param int $pos The numerical position
     *
     * @throws OutOfBoundsException if no pair at position
     *
     * @return mixed
     */
    public function getValueAtPosition($pos)
    {
        return $this->data[$this->getKeyAtPosition($pos)];
    }

    /**
     * Return key at specified numerical position.
     *
     * @param int $pos The numerical position
     *
     * @throws OutOfBoundsException if no pair at position
     *
     * @return mixed
     */
    public function getKeyAtPosition($pos)
    {
        $i = 0;
        foreach ($this as $key => $val) {
            if ($i === $pos) {
                return $key;
            }
            $i++;
        }
        throw new OutOfBoundsException("No element at expected position: $pos");
    }

    /**
     * @param int $pos The numerical position
     *
     * @throws OutOfBoundsException if no pair at position
     *
     * @return array
     */
    public function getPairAtPosition($pos)
    {
        $pairs = $this->pairs();

        return $pairs[$this->getKeyAtPosition($pos)];
    }

    /**
     * Get collection as array.
     *
     * @return array This collection as an array
     */
    public function toArray()
    {
        $arr = [];
        foreach ($this as $index => $value) {
            if (is_object($value) && method_exists($value, 'toArray')) {
                $value = $value->toArray();
            }
            $arr[$index] = $value;
        }

        return $arr;
    }

    /**
     * Get this collection's keys as a collection.
     *
     * @return AbstractCollection Containing this collection's keys
     */
    public function keys()
    {
        return static::factory(array_keys($this->data));
    }

    /**
     * Get this collection's values as a collection.
     *
     * This method returns this collection's values but completely re-indexed (numerically).
     *
     * @return AbstractCollection Containing this collection's values
     */
    public function values()
    {
        return static::factory(array_values($this->data));
    }

    /**
     * Merge data into collection.
     *
     * Merges input data into this collection. Input can be an array or another collection. Returns a NEW collection object.
     *
     * @param Traversable|array $data The data to merge with this collection
     *
     * @return AbstractCollection A new collection with $data merged in
     */
    public function merge($data)
    {
        $this->assertCorrectInputDataType($data);
        $coll = static::factory($this->data);
        foreach ($data as $index => $value) {
            $coll->set($index, $value);
        }

        return $coll;
    }

    /**
     * Determine if this collection contains a value.
     *
     * Allows you to pass in a value or a callback function and optionally an index,
     * and tells you whether or not this collection contains that value. If the $index param is specified, only that index will be looked under.
     *
     * @param mixed|callable $value The value to check for
     * @param mixed          $index The (optional) index to look under
     *
     * @return bool True if this collection contains $value
     *
     * @todo Maybe add $identical param for identical comparison (===)
     * @todo Allow negative offset for second param
     */
    public function contains($value, $index = null)
    {
        return (bool) $this->first(function ($val, $key) use ($value, $index) {
            if (is_callable($value)) {
                $found = $value($val, $key);
            } else {
                $found = ($value == $val);
            }
            if ($found) {
                if (is_null($index)) {
                    return true;
                }
                if (is_array($index)) {
                    return in_array($key, $index);
                }

                return $key == $index;
            }

            return false;
        });
    }

    /**
     * Get duplicate values.
     *
     * Returns a collection of arrays where the key is the duplicate value
     * and the value is an array of keys from the original collection.
     *
     * @return AbstractCollection A new collection with duplicate values.
     */
    public function duplicates()
    {
        $dups = [];
        $this->walk(function ($val, $key) use (&$dups) {
            $dups[$val][] = $key;
        });

        return static::factory($dups)->filter(function ($val) {
            return count($val) > 1;
        });
    }

    /**
     * Pop an element off the end of this collection.
     *
     * @return mixed The last item in this collectio n
     */
    public function pop()
    {
        return array_pop($this->data);
    }

    /**
     * Shift an element off the beginning of this collection.
     *
     * @return mixed The first item in this collection
     */
    public function shift()
    {
        return array_shift($this->data);
    }

    /**
     * Push a item(s) onto the end of this collection.
     *
     * Returns a new collection with $items added.
     *
     * @param array $items Any number of arguments will be pushed onto the
     *
     * @return mixed The first item in this collection
     */
    public function push(...$items)
    {
        array_push($this->data, ...$items);

        return static::factory($this->data);
    }

    /**
     * Unshift item(s) onto the beginning of this collection.
     *
     * Returns a new collection with $items added.
     *
     * @return mixed The first item in this collection
     */
    public function unshift(...$items)
    {
        array_unshift($this->data, ...$items);

        return static::factory($this->data);
    }

    /**
     * Pad this collection to a certain size.
     *
     * Returns a new collection, padded to the given size, with the given value.
     *
     * @param int  $size The number of items that should be in the collection
     * @param null $with The value to pad the collection with
     *
     * @return AbstractCollection A new collection padded to specified length
     */
    public function pad($size, $with = null)
    {
        return static::factory(array_pad($this->data, $size, $with));
    }

    /**
     * Apply a callback to each item in collection.
     *
     * Applies a callback to each item in collection and returns a new collection
     * containing each iteration's return value.
     *
     * @param callable $callback The callback to apply
     *
     * @return AbstractCollection A new collection with callback return values
     */
    public function map(callable $callback)
    {
        return static::factory(array_map($callback, $this->data));
    }

    /**
     * Apply a callback to each item in collection.
     *
     * Applies a callback to each item in collection. The callback should return
     * false to filter any item from the collection.
     *
     * @param callable $callback     The callback function
     * @param null     $extraContext Extra context to pass as third param in callback
     *
     * @return $this
     *
     * @see php.net array_walk
     */
    public function walk(callable $callback, $extraContext = null)
    {
        array_walk($this->data, $callback, $extraContext);

        return $this;
    }

    /**
     * Iterate over each item that matches criteria in callback.
     *
     * @param Closure|callable $callback A callback to use
     * @param object           $bindTo   The object to bind to
     *
     * @return AbstractCollection
     */
    public function each(Closure $callback, $bindTo = null)
    {
        if (is_null($bindTo)) {
            $bindTo = $this;
        }
        if (!is_object($bindTo)) {
            throw new InvalidArgumentException('Second argument must be an object.');
        }
        $cb     = $callback->bindTo($bindTo);
        $return = [];
        foreach ($this as $key => $val) {
            if ($cb($val, $key)) {
                $return[$key] = $val;
            }
        }

        return static::factory($return);
    }

    /**
     * Get each key/value as an array pair.
     *
     * Returns a collection of arrays where each item in the collection is [key,value]
     *
     * @return AbstractCollection
     */
    public function pairs()
    {
        return static::factory(array_map(
            function ($key, $val) {
                return [$key, $val];
            },
            array_keys($this->data),
            array_values($this->data)
        ));
    }

    /**
     * Reduce the collection to a single value.
     *
     * Using a callback function, this method will reduce this collection to a
     * single value.
     *
     * @param callable $callback The callback function used to reduce
     * @param null     $initial  The initial carry value
     *
     * @return mixed The single value produced by reduction algorithm
     */
    public function reduce(callable $callback, $initial = null)
    {
        return array_reduce($this->data, $callback, $initial);
    }

    /**
     * Filter the collection.
     *
     * Using a callback function, this method will filter out unwanted values, returning
     * a new collection containing only the values that weren't filtered.
     *
     * @param callable $callback The callback function used to filter
     * @param int      $flag     array_filter flag(s) (ARRAY_FILTER_USE_KEY or ARRAY_FILTER_USE_BOTH)
     *
     * @return AbstractCollection A new collection with only values that weren't filtered
     *
     * @see php.net array_filter
     */
    public function filter(callable $callback, $flag = ARRAY_FILTER_USE_BOTH)
    {
        return static::factory(array_filter($this->data, $callback, $flag));
    }

    /**
     * Return the first item that meets given criteria.
     *
     * Using a callback function, this method will return the first item in the collection
     * that causes the callback function to return true.
     *
     * @param callable $callback The callback function
     *
     * @return null|mixed The first item in the collection that causes callback to return true
     */
    public function first(callable $callback)
    {
        foreach ($this->data as $index => $value) {
            if ($callback($value, $index)) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Return the last item that meets given criteria.
     *
     * Using a callback function, this method will return the last item in the collection
     * that causes the callback function to return true.
     *
     * @param callable $callback The callback function
     *
     * @return null|mixed The last item in the collection that causes callback to return true
     */
    public function last(callable $callback)
    {
        $reverse = $this->reverse(true);

        return $reverse->first($callback);
    }

    /**
     * Returns collection in reverse order.
     *
     * @param null $preserveKeys True if you want to preserve collection's keys
     *
     * @return AbstractCollection This collection in reverse order.
     */
    public function reverse($preserveKeys = null)
    {
        return static::factory(array_reverse($this->data, $preserveKeys));
    }

    /**
     * Get unique items.
     *
     * Returns a collection of all the unique items in this collection.
     *
     * @return AbstractCollection This collection with duplicate items removed
     */
    public function unique()
    {
        return static::factory(array_unique($this->data));
    }

    /**
     * Join collection together using a delimiter.
     *
     * @param string $delimiter The delimiter string/char
     *
     * @return string
     */
    public function join($delimiter = '')
    {
        return implode($delimiter, $this->data);
    }

    /**
     * Counts how many times each value occurs in a collection.
     *
     * Returns a new collection with values as keys and how many times that
     * value appears in the collection. Works best with scalar values but will
     * attempt to work on collections of objects as well.
     *
     * @return AbstractCollection
     *
     * @todo Right now, collections of arrays or objects are supported via the
     * __toString() or spl_object_hash()
     * @todo NumericCollection::counts() does the same thing...
     */
    public function frequency()
    {
        $frequency = [];
        foreach ($this as $key => $val) {
            if (!is_scalar($val)) {
                if (!is_object($val)) {
                    $val = new ArrayIterator($val);
                }

                if (method_exists($val, '__toString')) {
                    $val = (string) $val;
                } else {
                    $val = spl_object_hash($val);
                }
            }
            if (!isset($frequency[$val])) {
                $frequency[$val] = 0;
            }
            $frequency[$val]++;
        }

        return static::factory($frequency);
    }

    /**
     * Collection factory method.
     *
     * This method will analyze input data and determine the most appropriate Collection
     * class to use. It will then instantiate said Collection class with the given
     * data and return it.
     *
     * @param mixed $data The data to wrap
     *
     * @return AbstractCollection A collection containing $data
     */
    public static function factory($data = null)
    {
        if (static::isTabular($data)) {
            $class = TabularCollection::class;
        } elseif (static::isMultiDimensional($data)) {
            $class = MultiCollection::class;
        } elseif (static::isAllNumeric($data)) {
            $class = NumericCollection::class;
        } elseif (static::isCharacterSet($data)) {
            $class = CharCollection::class;
        } else {
            $class = Collection::class;
        }

        return new $class($data);
    }

    /**
     * Is input data tabular?
     *
     * Returns true if input data is tabular in nature. This means that it is a
     * two-dimensional array with the same keys (columns) for each element (row).
     *
     * @param mixed $data The data structure to check
     *
     * @return bool True if data structure is tabular
     */
    public static function isTabular($data)
    {
        if (!is_traversable($data)) {
            return false;
        }
        foreach ($data as $row) {
            if (!is_traversable($row)) {
                return false;
            }
            $columns = array_keys($row);
            if (!isset($cmp_columns)) {
                $cmp_columns = $columns;
            } else {
                if ($cmp_columns != $columns) {
                    return false;
                }
            }
            // if row contains an array it isn't tabular
            if (array_reduce($row, function ($carry, $item) {
                return is_array($item) && $carry;
            }, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check data for multiple dimensions.
     *
     * This method is to determine whether a data structure is multi-dimensional.
     * That is to say, it is a traversable structure that contains at least one
     * traversable structure.
     *
     * @param mixed $data The input data
     *
     * @return bool
     */
    public static function isMultiDimensional($data)
    {
        if (!is_traversable($data)) {
            return false;
        }
        foreach ($data as $elem) {
            if (is_traversable($elem)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if structure contains all numeric values.
     *
     * @param mixed $data The input data
     *
     * @return bool
     */
    public static function isAllNumeric($data)
    {
        if (!is_traversable($data)) {
            return false;
        }
        foreach ($data as $val) {
            if (!is_numeric($val)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Is data a string of characters?
     *
     * Just checks to see if input is a string of characters or a string
     * of digits.
     *
     * @param mixed $data Data to check
     *
     * @return bool
     */
    public static function isCharacterSet($data)
    {
        return
            is_string($data) ||
            is_numeric($data);
    }

    /** END Iterator methods */

    /**
     * Set collection data.
     *
     * Sets the collection data.
     *
     * @param array $data The data to wrap
     *
     * @return $this
     */
    protected function setData($data)
    {
        if (is_null($data)) {
            $data = [];
        }
        $this->assertCorrectInputDataType($data);
        $data = $this->prepareData($data);
        foreach ($data as $index => $value) {
            $this->set($index, $value);
        }
        reset($this->data);

        return $this;
    }

    /**
     * Assert input data is of the correct structure.
     *
     * @param mixed $data Data to check
     *
     * @throws InvalidArgumentException If invalid data structure
     */
    protected function assertCorrectInputDataType($data)
    {
        if (!$this->isConsistentDataStructure($data)) {
            throw new InvalidArgumentException(__CLASS__ . ' expected traversable data, got: ' . gettype($data));
        }
    }

    /**
     * Convert input data to an array.
     *
     * Convert the input data to an array that can be worked with by a collection.
     *
     * @param mixed $data The input data
     *
     * @return array
     */
    protected function prepareData($data)
    {
        return $data;
    }

    /**
     * Determine whether data is consistent with a given collection type.
     *
     * This method is used to determine whether input data is consistent with a
     * given collection type. For instance, CharCollection requires a string.
     * NumericCollection requires an array or traversable set of numeric data.
     * TabularCollection requires a two-dimensional data structure where all the
     * keys are the same in every row.
     *
     * @param mixed $data Data structure to check for consistency
     *
     * @return bool
     */
    abstract protected function isConsistentDataStructure($data);
}
