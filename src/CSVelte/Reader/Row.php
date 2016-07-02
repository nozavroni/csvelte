<?php namespace CSVelte\Reader;

use CSVelte\Reader;
use CSVelte\Utils;

/**
 * Reader row class
 * The CSVelte\Reader returns Row objects for each row it reads
 *
 * @package   CSVelte\Reader
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class Row extends RowBase
{
    /**
     * @var array Same as columns only indexed using headers rather than numbers
     */
    protected $assocCols = array();

    /**
     * Set the header row (so that it can be used to index the row)
     *
     * @param CSVelte\Reader\HeaderRow
     * @return void ($this?)
     * @access public
     * @todo Throw exception if header row is wrong length
     */
    public function setHeaderRow(HeaderRow $headers)
    {
        $this->headers = $headers;
        $this->assocCols = array_combine($headers->toArray(), $this->columns);
    }

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
            Utils::array_get($this->assocCols, $offset, null, true);
        } catch (\OutOfBoundsException $e) {
            // now check $this->properties?
            return parent::offsetExists($offset);
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
        try {
            $val = Utils::array_get($this->assocCols, $offset, null, true);
        } catch (\OutOfBoundsException $e) {
            // now check $this->properties?
            return parent::offsetGet($offset);
        }
        return $val;
    }
}
