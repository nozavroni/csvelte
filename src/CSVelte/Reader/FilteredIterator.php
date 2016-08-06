<?php

namespace CSVelte\Reader;

use CSVelte\Reader as CsvReader;
use \FilterIterator;

class FilteredIterator extends FilterIterator
{
    protected $filters = array();

    public function __construct(CsvReader $reader, array $filters)
    {
        $this->filters = $filters;
        parent::__construct($reader);
    }

    /**
     * This isnt working
     * @todo as a solution to this, return a new ReaderFilter($this) from
     * $reader->filter()
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
}
