<?php namespace CSVelte\Table;

use CSVelte\Table\Data\Numeric;

/**
 * Table Data Class
 * Represents data within a particular row/column within a set of tabular data
 *
 * @package    CSVelte
 * @subpackage CSVelte\Table
 * @copyright  (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author     Luke Visinoni <luke.visinoni@gmail.com>
 */
class Data
{
    public static function fromString($str)
    {
        if (is_numeric($str)) return new Numeric($str);
    }

    public function __toString()
    {
        return (string) $this->value;
    }

}
