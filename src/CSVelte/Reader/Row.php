<?php namespace CSVelte\Reader;

use CSVelte\Reader;
use CSVelte\Traits\GetsMagicPropertiesFromArray;

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
    use GetsMagicPropertiesFromArray;

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
        // $this->properties = array_combine();
    }
}
