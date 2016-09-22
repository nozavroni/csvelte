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
namespace CSVelte\Table;

use \Iterator;
use \Countable;
use \ArrayAccess;
use CSVelte\Flavor;

use \OutOfBoundsException;
use \InvalidArgumentException;
use CSVelte\Exception\ImmutableException;

use function CSVelte\collect;

/**
 * Table row abstract base class
 * Represents a row of tabular data (represented by CSVelte\Table\Data objects)
 *
 * @package CSVelte
 * @subpackage CSVelte\Table
 * @since v0.1
 * @todo On all of the ArrayAccess methods, the docblocks say that $offset can be
 *     either an integer offset or a string index, but that isn't true, they must
 *     be an integer offset. Fix docblocks.
 */
abstract class AbstractRow implements Iterator, Countable, ArrayAccess
{
    /**
     * An array of fields for this row
     * @var array
     */
    protected $fields;

    /**
     * Iterator position
     * @var int
     */
    protected $position;

    /**
     * Class constructor
     *
     * @param array|Iterator An array (or anything that looks like one) of data (fields)
     * @access public
     */
    public function __construct($fields)
    {
        $this->setFields($fields)
             ->rewind();
    }

    protected function setFields($fields)
    {
        if (!is_array($fields)) {
            if (is_object($fields) && method_exists($fields, 'toArray')) {
                $fields = $fields->toArray();
            } elseif ($fields instanceof Iterator) {
                $fields = iterator_to_array($fields);
            } else {
                throw new InvalidArgumentException(__CLASS__ . " requires an array, got: " . gettype($fields));
            }
        }
        $this->fields = collect(array_values($fields));
        return $this;
    }

    public function __toString()
    {
        return $this->join();
    }

    /**
     * Join fields together using specified delimiter
     *
     * @param char The delimiter character
     * @return string
     * @access public
     */
    public function join($delimiter = ',')
    {
        return $this->fields->join($delimiter);
    }

    /**
     * Convert object to an array
     *
     * @return array representation of the object
     * @access public
     */
    public function toArray()
    {
        return $this->fields->toArray();
    }

    /** Begin SPL Countable Interface Method **/

    /**
     * Count fields within the row
     *
     * @return integer The amount of fields
     * @access public
     */
    public function count()
    {
        return count($this->fields);
    }

    /** Begin SPL Iterator Interface Methods **/

    /**
     * Get the current column's data object
     *
     * @return string
     * @access public
     */
    public function current()
    {
        return $this->fields->getValueAtPosition($this->position);
    }

    /**
     * Get the current key (column number or header, if available)
     *
     * @return string The "current" key
     * @access public
     * @todo Figure out if this can return a CSVelte\Table\HeaderData object so long as it
     *     has a __toString() method that generated the right key...
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Advance the internal pointer to the next column's data object
     * Also returns the next column's data object if there is one
     *
     * @return CSVelte\Table\Data The "next" column's data
     * @access public
     */
    public function next()
    {
        $this->position++;
        if ($this->valid()) return $this->current();
    }

    /**
     * Return the internal pointer to the first column and return that object
     *
     * @return void
     * @access public
     */
    public function rewind()
    {
        $this->position = 0;
        if ($this->valid()) return $this->current();
    }

    /**
     * Is the current position within the row's data fields valid?
     *
     * @return boolean
     * @access public
     */
    public function valid()
    {
        return $this->fields->hasPosition($this->position);
    }

    /** Begin SPL ArrayAccess Methods **/

    /**
     * Is there an offset at specified position
     *
     * @param integer Offset
     * @return boolean
     * @access public
     */
    public function offsetExists($offset)
    {
        return $this->fields->hasPosition($offset);
    }

    /**
     * Retrieve offset at specified position or by header name
     *
     * @param integer|string Offset/index
     * @return CSVelte\Table\Data
     * @access public
     */
    public function offsetGet($offset)
    {
        return $this->fields->getValueAtPosition($offset);
    }

    /**
     * Set offset at specified position
     *
     * @param integer|string Offset/index
     * @param CSVelte\Table\Data
     * @return void
     * @access public
     * @throws CSVelte\Exception\ImmutableException
     */
    public function offsetSet($offset, $value)
    {
        // fields are immutable, cannot be set
        $this->raiseImmutableException();
    }

    /**
     * Unset offset at specified position/index
     *
     * @param integer|string Offset/index
     * @return void
     * @access public
     * @throws CSVelte\Exception\ImmutableException
     * @todo I'm not sure if these objects will stay immutable or not yet...
     */
    public function offsetUnset($offset)
    {
        $this->raiseImmutableException();
    }

    /**
     * Raise (throw) immutable exception
     *
     * @param string Message
     * @return void
     * @access protected
     * @throws CSVelte\Exception\ImmutableException
     */
    protected function raiseImmutableException($msg = null)
    {
        // fields are immutable, cannot be set
        throw new ImmutableException($msg ?: 'Cannot change immutable column data');
    }
}
