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
namespace CSVelte\Reader;

use CSVelte\Reader as CsvReader;
use FilterIterator;

/**
 * Filtered Reader Iterator.
 *
 * This class is not intended to be instantiated manually. It is returned by the
 * CSVelte\Reader class when filter() is called to iterate over the CSV file,
 * skipping all rows that don't pass the filter(s) tests.
 *
 * @package CSVelte
 * @subpackage Reader
 *
 * @since v0.1
 *
 * @internal
 */
class FilteredIterator extends FilterIterator
{
    /**
     * A list of callback functions.
     *
     * @var array of Callable objects/functions
     */
    protected $filters = [];

    /**
     * FilteredIterator Constructor.
     *
     * Initializes the iterator using the CSV reader and its array of callback
     * filter functions/callables.
     *
     * @param \CSVelte\Reader $reader  The CSV reader being iterated
     * @param array           $filters The list of callbacks
     */
    public function __construct(CsvReader $reader, array $filters)
    {
        $this->filters = $filters;
        parent::__construct($reader);
    }

    /**
     * Run filters against each row.
     * Loop through all of the callback functions, and if any of them fail, do
     * not include this row in the iteration.
     *
     * @return bool
     *
     * @todo filter functions should accept current row, index, AND ref to reader
     */
    public function accept()
    {
        $reader = $this->getInnerIterator();
        foreach ($this->filters as $filter) {
            if (!$filter($reader->current())) {
                return false;
            }
        }

        return true;
    }

    /**
     * Return this object as an array.
     *
     * @return array This object as an array
     */
    public function toArray()
    {
        return array_map(function ($row) {
            return $row->toArray();
        }, iterator_to_array($this));
    }
}
