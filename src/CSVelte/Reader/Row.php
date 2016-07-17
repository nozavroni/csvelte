<?php namespace CSVelte\Reader;

use CSVelte\Reader;
use CSVelte\Utils;
use CSVelte\Exception\InvalidHeaderException;

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
        $headerArray = $headers->toArray();
        if (($hcount = $headers->count()) !== ($rcount = $this->count())) {
            if ($hcount > $rcount) {
                // header count is long, could be an error, but lets just fill in the short row with null values...
                $this->columns = array_pad($this->columns, $hcount, null);
            } else {
                // header count is short, this is likely an error...
                throw new InvalidHeaderException("Header count ({$hcount}) does not match column count ({$rcount}).");
            }
        }
        $this->headers = $headers;
        $this->assocCols = array_combine($headerArray, $this->columns);
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
