<?php namespace CSVelte\Table;

use CSVelte\Contract\DataType;
use CSVelte\Table\Data\StringValue;

/**
 * Table Data Item Class
 * Represents data within a particular row/column within a set of tabular data
 *
 * @package    CSVelte
 * @subpackage CSVelte\Table
 * @copyright  (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author     Luke Visinoni <luke.visinoni@gmail.com>
 */
class Cell
{
    /**
     * @var CSVelte\Contract\DataType
     */
    protected $value;

    /**
     * Class constructor
     *
     * @param mixed Can be either a native PHP value or a DataType object
     * @return void
     * @access public
     */
    public function __construct($value)
    {
        if (!($value instanceof DataType)) {
            $value = new StringValue($value);
        }
        $this->setValue($value);
    }

    public function setValue(DataType $value)
    {
        $this->value = $value;
    }

    public function __toString()
    {
        return (string) $this->value;
    }
}
