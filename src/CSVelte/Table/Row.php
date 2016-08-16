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

use CSVelte\Exception\InvalidHeaderException;
use CSVelte\Utils;

/**
 * Table Row Class
 * Represents a row of tabular data (CSVelte\Table\Cell objects).
 *
 * @copyright  (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author     Luke Visinoni <luke.visinoni@gmail.com>
 */
class Row extends AbstractRow
{
    /**
     * @var array Same as columns only indexed using headers rather than numbers
     */
    protected $assocCols = [];

    /**
     * Set the header row (so that it can be used to index the row).
     *
     * @param CSVelte\Table\HeaderRow
     *
     * @return void (return $this?)
     */
    public function setHeaderRow(AbstractRow $headers)
    {
        if (!($headers instanceof HeaderRow)) {
            $headers = new HeaderRow($headers->toArray());
        }
        $headerArray = $headers->toArray();
        if (($hcount = $headers->count()) !== ($rcount = $this->count())) {
            if ($hcount > $rcount) {
                // header count is long, could be an error, but lets just fill in the short row with null values...
                $this->columns = array_pad($this->columns, $hcount, null);
            } else {
                // @todo This is too strict. I need a way to recover from this a little better...
                // header count is short, this is likely an error...
                throw new InvalidHeaderException("Header count ({$hcount}) does not match column count ({$rcount}).");
            }
        }
        $this->headers = $headers;
        $this->assocCols = array_combine($headerArray, $this->columns);
    }

    /**
     * Is there an offset at specified position?
     *
     * @param int Offset
     *
     * @return bool
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
     * Retrieve data at specified column offset.
     *
     * @param int Offset
     *
     * @return CSVelte\Table\Data
     */
    public function offsetGet($offset)
    {
        try {
            $val = Utils::array_get($this->assocCols, $offset, null, true);
        } catch (\OutOfBoundsException $e) {
            return parent::offsetGet($offset);
        }

        return $val;
    }
}
