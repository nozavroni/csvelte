<?php namespace CSVelte\Reader;

use CSVelte\Reader;
use CSVelte\Utils;

/**
 * Reader row abstract base class
 * The CSVelte\Reader returns Row objects for each row it reads
 *
 * @package   CSVelte\Reader
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
abstract class RowBase implements \Iterator, \Countable //, ArrayAccess
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

    /**
     * Class constructor
     *
     * @param array An array (or anything that looks like one) of columns
     * @return void
     * @access public
     * @todo Look into SplFixedArray for csv sources w/out a header row.
     */
    public function __construct(array $columns)
    {
        $this->columns = array_values($columns);
        $this->rewind();
    }

    /**
     * Convert object to an array
     *
     * @return array
     * @access public
     */
    public function toArray()
    {
        return $this->columns;
    }

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
        return $this->position;
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

    public function offsetGet($offset)
    {
        $this->assertOffsetExists($offset);
        return $this->columns[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->columns[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        $this->assertOffsetExists($offset);
        unset($this->columns[$offset]);
    }

    protected function assertOffsetExists($offset)
    {
        if (!$this->offsetExists($offset)) {
            throw new \OutOfBoundsException("Undefined offset: " . $offset);
        }
    }

    /**
     * Join columns together using specified delimiter
     *
     * @return boolean
     * @access public
     */
    public function join($delimiter = "")
    {
        return implode($delimiter, $this->columns);
    }

}
