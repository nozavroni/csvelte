<?php namespace CSVelte\Reader;

use CSVelte\Reader;
use CSVelte\Utils;
use CSVelte\Flavor;
use CSVelte\Exception\ImmutableException;

/**
 * Reader row abstract base class
 * The CSVelte\Reader returns Row objects for each row it reads
 *
 * @package   CSVelte\Reader
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @todo      The column values in this class should be immutable. Once you create
 *     the object, there should be no way to then modify its columns. You can't
 *     add new indexes, remove existing ones, none of that. So that would mean
 *     that although it implements ArrayAccess, it needs to be throwing exceptions
 *     on any method that isn't read-only (offsetSet/offsetUnset)
 */
abstract class RowBase implements \Iterator, \Countable, \ArrayAccess
{
    /**
     * @var array The columns within the row
     */
    protected $columns;

    /**
     * @var integer The current position within $columns
     */
    protected $position;

    /**
     * @var HeaderRow The header row
     */
    protected $headers;

    protected $flavor;

    /**
     * Class constructor
     *
     * @param array An array (or anything that looks like one) of columns
     * @return void
     * @access public
     * @todo Look into SplFixedArray for csv sources w/out a header row.
     */
    public function __construct(array $columns, Flavor $flavor = null)
    {
        if (is_null($flavor)) $flavor = new Flavor;
        $this->flavor = $flavor;
        $this->columns = array_values($columns);
        $this->rewind();
    }

    public function __toString()
    {
        return $this->join();
    }

    /**
     * Join columns together using specified delimiter
     *
     * @return boolean
     * @access public
     */
    public function join($delimiter = null)
    {
        if (is_null($delimiter)) $delimiter = $this->flavor->delimiter;
        return implode($delimiter, $this->columns);
    }

    /**
     * Convert object to an array
     *
     * @return array
     * @access public
     */
    public function toArray()
    {
        return iterator_to_array($this);
    }

    /** Countable Method **/

    /**
     * Count columns within the row
     *
     * @return integer The amount of columns
     * @access public
     */
    public function count()
    {
        return count($this->columns);
    }

    /** Iterator Methods **/

    /**
     * Get the current column
     *
     * @return string The "current" column
     * @access public
     */
    public function current()
    {
        if (!array_key_exists($this->position, $this->columns)) {
            throw new \OutOfBoundsException("Undefined index: " . $this->position);
        }
        return $this->columns[$this->position];
    }

    /**
     * Get the current key
     *
     * @return string The "current" key
     * @access public
     */
    public function key()
    {
        return isset($this->headers[$this->position]) ? $this->headers[$this->position] : $this->position;
    }

    /**
     * Get the next column
     *
     * @return string The "next" column
     * @access public
     */
    public function next()
    {
        $this->position++;
        if ($this->valid()) return $this->current();
    }

    /**
     * Rewind back to the beginning...
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
     * Is the current position valid?
     *
     * @return boolean
     * @access public
     */
    public function valid()
    {
        return array_key_exists($this->position, $this->columns);
    }

    /** ArrayAccess Methods **/

    /**
     * Is there an offset at specified position
     *
     * @param integer Offset
     * @return boolean
     * @access public
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
     * Retrieve offset at specified position
     *
     * @param integer Offset
     * @return any
     * @access public
     */
    public function offsetGet($offset)
    {
        $this->assertOffsetExists($offset);
        return $this->columns[$offset];
    }

    /**
     * Set offset at specified position
     *
     * @param integer Offset
     * @param any Value
     * @return void
     * @access public
     * @throws CSVelte\Exception\ImmutableException
     */
    public function offsetSet($offset, $value)
    {
        // $this->columns[$offset] = $value;
        // columns are immutable, cannot be set
        $this->raiseImmutableException();
    }

    /**
     * Unset offset at specified position
     *
     * @param integer Offset
     * @return void
     * @access public
     * @throws CSVelte\Exception\ImmutableException
     */
    public function offsetUnset($offset)
    {
        // $this->assertOffsetExists($offset);
        // unset($this->columns[$offset]);
        $this->raiseImmutableException();
    }

    /**
     * Throw exception unless offset exists
     *
     * @param integer Offset
     * @return void
     * @access protected
     * @throws \OutOfBoundsException
     */
    protected function assertOffsetExists($offset)
    {
        if (!$this->offsetExists($offset)) {
            throw new \OutOfBoundsException("Undefined offset: " . $offset);
        }
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
        // columns are immutable, cannot be set
        throw new ImmutableException($msg ?: 'Cannot change immutable column data');
    }
}
