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
}
