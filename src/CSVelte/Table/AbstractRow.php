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
namespace CSVelte\Table;

use ArrayAccess;
use Countable;
use CSVelte\Collection\AbstractCollection;
use CSVelte\Exception\ImmutableException;

use InvalidArgumentException;
use Iterator;

use function CSVelte\collect;

/**
 * Table row abstract base class
 * Represents a row of tabular data (represented by CSVelte\Table\Data objects).
 *
 * @package CSVelte
 * @subpackage CSVelte\Table
 *
 * @since v0.1
 *
 * @todo On all of the ArrayAccess methods, the docblocks say that $offset can be
 *     either an integer offset or a string index, but that isn't true, they must
 *     be an integer offset. Fix docblocks.
 */
abstract class AbstractRow implements Iterator, Countable, ArrayAccess
{
    /**
     * An collection of fields for this row.
     *
     * @var AbstractCollection
     */
    protected $fields;

    /**
     * Iterator position.
     *
     * @var int
     */
    protected $position;

    /**
     * Class constructor.
     *
     * @param array|Iterator An array (or anything that looks like one) of data (fields)
     * @param mixed $fields
     */
    public function __construct($fields)
    {
        $this->setFields($fields)
             ->rewind();
    }

    /**
     * Return a string representation of this object.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->join();
    }

    /**
     * Join fields together using specified delimiter.
     *
     * @param string The delimiter character
     * @param mixed $delimiter
     *
     * @return string
     */
    public function join($delimiter = ',')
    {
        return $this->fields->join($delimiter);
    }

    /**
     * Convert object to an array.
     *
     * @return array representation of the object
     */
    public function toArray()
    {
        return $this->fields->toArray();
    }

    /** Begin SPL Countable Interface Method **/

    /**
     * Count fields within the row.
     *
     * @return int The amount of fields
     */
    public function count()
    {
        return count($this->fields);
    }

    /** Begin SPL Iterator Interface Methods **/

    /**
     * Get the current column's data object.
     *
     * @return string
     */
    public function current()
    {
        return $this->fields->getValueAtPosition($this->position);
    }

    /**
     * Get the current key (column number or header, if available).
     *
     * @return string The "current" key
     *
     * @todo Figure out if this can return a CSVelte\Table\HeaderData object so long as it
     *     has a __toString() method that generated the right key...
     */
    public function key()
    {
        return $this->fields->getKeyAtPosition($this->position);
    }

    /**
     * Advance the internal pointer to the next column's data object
     * Also returns the next column's data object if there is one.
     *
     * @return mixed The "next" column's data
     */
    public function next()
    {
        $this->position++;
        if ($this->valid()) {
            return $this->current();
        }
    }

    /**
     * Return the internal pointer to the first column and return that object.
     *
     * @return null|mixed|AbstractRow
     */
    public function rewind()
    {
        $this->position = 0;
        if ($this->valid()) {
            return $this->current();
        }
    }

    /**
     * Is the current position within the row's data fields valid?
     *
     * @return bool
     */
    public function valid()
    {
        return $this->fields->hasPosition($this->position);
    }

    * @param mixed $offset
/** Begin SPL ArrayAccess Methods **/

    /**
     * Is there an offset at specified position.
     *
     * @param mixed $offset The offset to check existence of
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->fields->offsetExists($offset);
    }

    /**
     * Retrieve offset at specified position or by header name.
     *
     * @param mixed $offset The offset to get
     *
     * @return mixed The data at the specified position
     */
    public function offsetGet($offset)
    {
        return $this->fields->offsetGet($offset);
    }

    /**
     * Set offset at specified position.
     *
     * @param mixed $offset The array offset to set
     * @param mixed $value  The value to set $offset to
     *
     * @throws ImmutableException
     */
    public function offsetSet($offset, $value)
    {
        // fields are immutable, cannot be set
        $this->raiseImmutableException();
    }

    /**
     * Unset offset at specified position/index.
     *
     * @param mixed $offset The offset to unset
     *
     * @throws ImmutableException
     *
     * @todo I'm not sure if these objects will stay immutable or not yet...
     */
    public function offsetUnset($offset)
    {
        $this->raiseImmutableException();
    }

    /**
     * Set the row fields.
     *
     * Using either an array or iterator, set the fields for this row.
     *
     * @param array|Iterator $fields An array or iterator with the row's fields
     *
     * @return $this
     */
    protected function setFields($fields)
    {
        if (!is_array($fields)) {
            if (is_object($fields) && method_exists($fields, 'toArray')) {
                $fields = $fields->toArray();
            } elseif ($fields instanceof Iterator) {
                $fields = iterator_to_array($fields);
            } else {
                throw new InvalidArgumentException(__CLASS__ . ' requires an array, got: ' . gettype($fields));
            }
        }
        $this->fields = collect($fields)->values();

        return $this;
    }

    /**
     * Raise (throw) immutable exception.
     *
     * @param string $msg The message to pass to the exception
     *
     * @throws ImmutableException
     */
    protected function raiseImmutableException($msg = null)
    {
        // fields are immutable, cannot be set
        throw new ImmutableException($msg ?: 'Cannot change immutable column data');
    }
}
