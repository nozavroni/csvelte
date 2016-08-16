<?php
/**
 * CSVelte: Slender, elegant CSV for PHP
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV
 * standardization efforts, CSVelte was written in an effort to take all the
 * suck out of working with CSV.
 *
 * @version   v0.1
 *
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace CSVelte\Table;

use ArrayAccess;
use Countable;
use CSVelte\Exception\ImmutableException;
use CSVelte\Flavor;
use CSVelte\Utils;
use Iterator;

/**
 * Table row abstract base class
 * Represents a row of tabular data (represented by CSVelte\Table\Data objects).
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
     * @var array The columns within the row
     *
     * @todo Technically a row doesn't contain "columns". It contains "data" or
     *     "datum" if we want to really get down to semantics. But I'm not sure
     *     whether that would be a better name for this or not...
     */
    protected $columns;

    /**
     * @var int The current position within
     */
    protected $position;

    /**
     * @var HeaderRow The header row
     */
    protected $headers;

    /**
     * @var CSVelte\Flavor The flavor for this row
     *
     * @todo I really don't want to have every object in the libary carry around
     *     a Flavor object. In fact, I would prefer it if only one did. The Reader
     *     or the Writer or whatever the action I'm performing's main class is.
     *     I need to find a solution that allows me to keep the Flavor in one
     *     place and have anything else that needs to know about it, either NOT
     *     need to know about it, or get it from somewhere else (rather than )
     *     storing it, itself).
     */
    protected $flavor;

    /**
     * Class constructor.
     *
     * @param array An array (or anything that looks like one) of data (columns)
     * @param CSVelte\Flavor
     *
     * @todo This doesn't really need to be passed $flavor
     * @todo Look into SplFixedArray for csv sources w/out a header row.
     */
    public function __construct(array $columns, Flavor $flavor = null)
    {
        if (is_null($flavor)) {
            $flavor = new Flavor();
        }
        $this->flavor = $flavor;
        $this->columns = array_values($columns);
        $this->rewind();
    }

    /**
     * String overloading.
     *
     * @return string representation of this object
     *
     * @todo Should this end with a lineTerminator? I think it probably should...
     */
    public function __toString()
    {
        return $this->join();
    }

    /**
     * Join columns together using specified delimiter.
     *
     * @param char The delimiter character
     *
     * @return string
     */
    public function join($delimiter = null)
    {
        if (is_null($delimiter)) {
            $delimiter = $this->flavor->delimiter;
        }

        return implode($delimiter, $this->columns);
    }

    /**
     * Convert object to an array.
     *
     * @return array representation of the object
     */
    public function toArray()
    {
        return iterator_to_array($this);
    }

    /** Begin SPL Countable Interface Method **/

    /**
     * Count columns within the row.
     *
     * @return int The amount of columns
     */
    public function count()
    {
        return count($this->columns);
    }

    /** Begin SPL Iterator Interface Methods **/

    /**
     * Get the current column's data object.
     *
     * @return CSVelte\Table\Data
     */
    public function current()
    {
        if (!array_key_exists($this->position, $this->columns)) {
            throw new \OutOfBoundsException('Undefined index: '.$this->position);
        }

        return $this->columns[$this->position];
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
        return isset($this->headers[$this->position]) ? $this->headers[$this->position] : $this->position;
    }

    /**
     * Advance the internal pointer to the next column's data object
     * Also returns the next column's data object if there is one.
     *
     * @return CSVelte\Table\Data The "next" column's data
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
     * @return void
     */
    public function rewind()
    {
        $this->position = 0;
        if ($this->valid()) {
            return $this->current();
        }
    }

    /**
     * Is the current position within the row's data columns valid?
     *
     * @return bool
     */
    public function valid()
    {
        return array_key_exists($this->position, $this->columns);
    }

    /** Begin SPL ArrayAccess Methods **/

    /**
     * Is there an offset at specified position.
     *
     * @param int Offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        try {
            Utils::array_get($this->columns, $offset, null, true);
        } catch (\OutOfBoundsException $e) {
            // now check $this->properties?
            return false;
        }

        return true;
    }

    /**
     * Retrieve offset at specified position or by header name.
     *
     * @param int|string Offset/index
     *
     * @return CSVelte\Table\Data
     */
    public function offsetGet($offset)
    {
        $this->assertOffsetExists($offset);

        return $this->columns[$offset];
    }

    /**
     * Set offset at specified position or by header name.
     *
     * @param int|string Offset/index
     * @param CSVelte\Table\Data
     *
     * @throws CSVelte\Exception\ImmutableException
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        // columns are immutable, cannot be set
        $this->raiseImmutableException();
    }

    /**
     * Unset offset at specified position/index.
     *
     * @param int|string Offset/index
     *
     * @throws CSVelte\Exception\ImmutableException
     *
     * @return void
     *
     * @todo I'm not sure if these objects will stay immutable or not yet...
     */
    public function offsetUnset($offset)
    {
        $this->raiseImmutableException();
    }

    /**
     * Throw exception unless offset/index exists.
     *
     * @param int|string Offset/index
     *
     * @throws \OutOfBoundsException
     *
     * @return void
     */
    protected function assertOffsetExists($offset)
    {
        if (!$this->offsetExists($offset)) {
            throw new \OutOfBoundsException('Undefined offset: '.$offset);
        }
    }

    /**
     * Raise (throw) immutable exception.
     *
     * @param string Message
     *
     * @throws CSVelte\Exception\ImmutableException
     *
     * @return void
     */
    protected function raiseImmutableException($msg = null)
    {
        // columns are immutable, cannot be set
        throw new ImmutableException($msg ?: 'Cannot change immutable column data');
    }
}
