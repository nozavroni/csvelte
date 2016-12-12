<?php

/*
 * CSVelte: Slender, elegant CSV for PHP
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV
 * standardization efforts, CSVelte was written in an effort to take all the
 * suck out of working with CSV.
 *
 * @version   v0.2.3
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace CSVelte\Table;

use CSVelte\Exception\HeaderException;
use OutOfBoundsException;

use function CSVelte\collect;

/**
 * Table Row Class
 * Represents a row of tabular data (CSVelte\Table\Cell objects).
 *
 * @package    CSVelte
 * @subpackage CSVelte\Table
 *
 * @copyright  (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author     Luke Visinoni <luke.visinoni@gmail.com>
 *
 * @todo       May need to put toArray() method in here so that it uses headers
 *             as keys here
 */
class Row extends AbstractRow
{
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
        try {
            return $this->fields->getKeyAtPosition($this->position);
        } catch (OutOfBoundsException $e) {
            return parent::key();
        }
    }

    /**
     * Set the header row (so that it can be used to index the row).
     *
     * @param AbstractRow|HeaderRow $headers Header row to set
     *
     * @throws HeaderException
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
                $this->fields = $this->fields->pad($hcount);
            } else {
                // @todo This is too strict. I need a way to recover from this a little better...
                // header count is short, this is likely an error...
                throw new HeaderException("Header count ({$hcount}) does not match column count ({$rcount}).", HeaderException::ERR_HEADER_COUNT);
            }
        }
        $this->fields = collect(array_combine(
            $headerArray,
            $this->fields->toArray()
        ));
    }

    /**
     * Is there an offset at specified position?
     *
     * @param mixed $offset
     *
     * @return bool
     *
     * @internal param Offset $integer
     */
    public function offsetExists($offset)
    {
        try {
            $this->fields->get($offset, null, true);
        } catch (\OutOfBoundsException $e) {
            return parent::offsetExists($offset);
        }

        return true;
    }

    /**
     * Retrieve data at specified column offset.
     *
     * @param mixed $offset The offset to get
     *
     * @return mixed The value at $offset
     */
    public function offsetGet($offset)
    {
        try {
            $val = $this->fields->get($offset, null, true);
        } catch (\OutOfBoundsException $e) {
            return parent::offsetGet($offset);
        }

        return $val;
    }
}
