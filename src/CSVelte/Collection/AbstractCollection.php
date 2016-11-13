<?php
/**
 * CSVelte: Slender, elegant CSV for PHP
 *
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV
 * standardization efforts, CSVelte was written in an effort to take all the
 * suck out of working with CSV.
 *
 * @version   v${CSVELTE_DEV_VERSION}
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace CSVelte\Collection;

use ArrayAccess;
use Countable;
use Iterator;
use CSVelte\Contract\Collectable;
use CSVelte\Collection\Collection as BaseCollection;
use function CSVelte\is_traversable;

use OutOfBoundsException;

/**
 * Class AbstractCollection.
 *
 * This is the abstract class that all other collection classes are based on.
 * Although it's possible to use a completely custom Collection class by simply
 * implementing the "Collectable" interface, extending this class gives you a
 * whole slew of convenient methods for free.
 *
 * @package CSVelte\Collection
 * @since v0.2.2
 * @author Luke Visinoni <luke.visinoni@gmail.com>
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @todo Implement Serializable, Countable, other Interfaces
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
    protected $data;

    /**
     * @var boolean True unless we have advanced past the end of the data array
     */
    protected $isValid = true;

    /**
     * AbstractCollection constructor.
     *
     * @param mixed $data The data to wrap
     */
    public function __construct($data)
    {
        $this->setData($data);
    }

    /**
     * Invoke object.
     *
     * Magic "invoke" method. Called when object is invoked as if it were a function.
     *
     * @param mixed $val The value (depends on other param value)
     * @param mixed $index The index (depends on other param value)
     * @return mixed (Depends on parameter values)
     */
    public function __invoke($val = null, $index = null)
    {
        if (is_null($val)) {
            if (is_null($index)) {
                return $this->toArray();
            } else {
                return $this->delete($index);
            }
        } else {
            if (is_null($index)) {
                // @todo cast $val to array?
                return $this->merge($val);
            } else {
                return $this->set($val, $index);
            }
        }
    }

    /** BEGIN ArrayAccess methods */

    /**
     * Whether a offset exists.
     *
     * @param mixed $offset An offset to check for.
     * @return boolean true on success or false on failure.
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * Offset to retrieve.
     *
     * @param mixed $offset The offset to retrieve.
     * @return mixed Can return all value types.
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Offset to set.
     *
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value The value to set.
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * Offset to unset.
     *
     * @param mixed $offset The offset to unset.
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
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
    public function current ()
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
    public function key ()
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
    public function next ()
    {
        $next = next($this->data);
        $key = key($this->data);
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
    public function rewind ()
    {
        $this->isValid = true;
        return reset($this->data);
    }

    /**
     * Is internal pointer in a valid position?
     *
     * If the internal pointer is advanced beyond the end of the collection, this method will return false.
     *
     * @return bool True if internal pointer isn't past the end
     */
    public function valid ()
    {
        return $this->isValid;
    }

    /** END Iterator methods */

    /**
     * Set collection data.
     *
     * Sets the collection data.
     *
     * @param array $data The data to wrap
     * @return $this
     */
    protected function setData($data)
    {
        if (is_null($data)) {
            $data = [];
        }
        $this->assertCorrectInputDataType($data);
        foreach ($data as $index => $value) {
            $this->set($index, $value);
        }

        return $this;
    }

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
     * @param mixed $index The index of the data you want to get
     * @param mixed $default The default value to return if none available
     * @param bool $throw True if you want an exception to be thrown if no data found at $index
     * @throws OutOfBoundsException If $throw is true and $index isn't found
     * @return mixed The data found at $index or failing that, the $default
     */
    public function get($index, $default = null, $throw = false)
    {
        if (isset($this->data[$index])) {
            return $this->data[$index];
        }
        if ($throw) {
            throw new OutOfBoundsException(__CLASS__ . " could not find value at index " . $index);
        }
        return $default;
    }

    /**
     * Set a value at a given index.
     *
     * Setter for this collection. Allows setting a value at a given index.
     *
     * @param mixed $index The index to set a value at
     * @param mixed $val The value to set $index to
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
     * @param bool $throw True if you want an exception to be thrown if no data found at $index
     * @throws OutOfBoundsException If $throw is true and $index isn't found
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
     * @return Collection Containing this collection's keys
     */
    public function keys()
    {
        return self::factory(array_keys($this->data));
    }

    /**
     * Get this collection's values as a collection.
     *
     * This method returns this collection's values but completely re-indexed (numerically).
     *
     * @return Collection Containing this collection's values
     */
    public function values()
    {
        return self::factory(array_values($this->data));
    }

    /**
     * Merge data into collection.
     *
     * Merges input data into this collection. Input can be an array or another collection. Returns a NEW collection object.
     *
     * @param Traversable|array $data The data to merge with this collection
     * @return Collection A new collection with $data merged in
     */
    public function merge($data)
    {
        $this->assertCorrectInputDataType($data);
        $coll = self::factory($this->data);
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
     * @param mixed $index The (optional) index to look under
     * @return boolean True if this collection contains $value
     * @todo Maybe add $identical param for identical comparison (===)
     */
    public function contains($value, $index = null)
    {
        foreach ($this->data as $key => $val) {
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
        }
        return false;
    }

    /**
     * Pop an element off the end of this collection.
     *
     * @return mixed The last item in this collection
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
     * @return mixed The first item in this collection
     */
    public function push(...$items)
    {
        array_push($this->data, ...$items);
        return self::factory($this->data);
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
        return self::factory($this->data);
    }

    /**
     * Pad this collection to a certain size.
     *
     * Returns a new collection, padded to the given size, with the given value.
     *
     * @param int $size The number of items that should be in the collection
     * @param null $with The value to pad the collection with
     * @return Collection A new collection padded to specified length
     */
    public function pad($size, $with = null)
    {
        return self::factory(array_pad($this->data, $size, $with));
    }

    /**
     * Apply a callback to each item in collection.
     *
     * Applies a callback to each item in collection and returns a new collection
     * containing each iteration's return value.
     *
     * @param callable $callback The callback to apply
     * @return Collection A new collection with callback return values
     */
    public function map(callable $callback)
    {
        return self::factory(array_map($callback, $this->data));
    }

    /**
     * Apply a callback to each item in collection.
     *
     * Applies a callback to each item in collection. The callback should return
     * false to filter any item from the collection.
     *
     * @param callable $callback The callback function
     * @param null $extraContext Extra context to pass as third param in callback
     * @return $this
     * @see php.net array_walk
     */
    public function walk(callable $callback, $extraContext = null)
    {
        array_walk($this->data, $callback, $extraContext);
        return $this;
    }

    /**
     * Reduce the collection to a single value.
     *
     * Using a callback function, this method will reduce this collection to a
     * single value.
     *
     * @param callable $callback The callback function used to reduce
     * @param null $initial The initial carry value
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
     * @param int $flag array_filter flag(s) (ARRAY_FILTER_USE_KEY or ARRAY_FILTER_USE_BOTH)
     * @return Collection A new collection with only values that weren't filtered
     * @see php.net array_filter
     */
    public function filter(callable $callback, $flag = ARRAY_FILTER_USE_BOTH)
    {
        return self::factory(array_filter($this->data, $callback, $flag));
    }

    /**
     * Return the first item that meets given criteria.
     *
     * Using a callback function, this method will return the first item in the collection
     * that causes the callback function to return true.
     *
     * @param callable $callback The callback function
     * @return null|mixed The first item in the collection that causes callback to return true
     */
    public function first(callable $callback)
    {
        foreach ($this->data as $index => $value) {
            if ($ret = $callback($value, $index)) {
                return $ret;
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
     * @return Collection This collection in reverse order.
     */
    public function reverse($preserveKeys = null)
    {
        return self::factory(array_reverse($this->data, $preserveKeys));
    }

    /**
     * Get unique items.
     *
     * Returns a collection of all the unique items in this collection.
     *
     * @return Collection This collection with duplicate items removed
     */
    public function unique()
    {
        return self::factory(array_unique($this->data));
    }

    /**
     * Convert collection to string
     *
     * @return string A string representation of this collection
     */
    public function __toString()
    {
        return (string) $this->data;
    }

    /**
     * Collection factory method.
     *
     * This method will analyze input data and determine the most appropriate Collection
     * class to use. It will then instantiate said Collection class with the given
     * data and return it.
     *
     * @param mixed $data The data to wrap
     * @return Collection A collection containing $data
     */
    public static function factory($data = null)
    {
        switch (true) {
            case false:
                $class = 'CharCollection';
                break;
            case false:
                $class = 'NumericCollection';
                break;
            case false:
                $class = 'TabularCollection';
                break;
            case false:
                $class = 'MultiCollection';
                break;
            default:
                $class = BaseCollection::class;
                break;
        }
        return new $class($data);
    }

    abstract protected function assertCorrectInputDataType($data);
}