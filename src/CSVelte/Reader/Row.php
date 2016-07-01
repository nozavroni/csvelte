<?php namespace CSVelte\Reader;

use CSVelte\Reader;

/**
 * Reader row class
 * The CSVelte\Reader returns Row objects for each row it reads
 *
 * @package   CSVelte\Reader
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class Row implements \Countable
{
    /**
     * @var array The columns within the row
     */
     protected $columns;

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
        $this->columns = $columns;
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
        return current($this->columns);
    }

    /**
     * Get the current key
     *
     * @return string The "current" key
     * @access public
     */
    public function key()
    {
        return key($this->columns);
    }

    /**
     * Get the next column
     *
     * @return string The "next" column
     * @access public
     */
    public function next()
    {
        return next($this->columns);
    }

    /**
     * Rewind back to the beginning...
     *
     * @return void
     * @access public
     */
    public function rewind()
    {
        return reset($this->columns);
    }
}
