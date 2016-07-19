<?php namespace CSVelte\Table;

use CSVelte\Table\Data\Numeric;
use CSVelte\Table\Data\Boolean;

/**
 * Table Data Class
 * Represents data within a particular row/column within a set of tabular data
 *
 * @package    CSVelte
 * @subpackage CSVelte\Table
 * @copyright  (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author     Luke Visinoni <luke.visinoni@gmail.com>
 */
abstract class Data
{

    public function __construct($value)
    {
        $this->value = $this->cast($value);
    }

    public static function fromString($str)
    {
        if (Numeric::is($str)) return new Numeric($str);
        elseif (Boolean::is($str)) return new Boolean($str);
        else {
            // getting to this...
        }
    }

    public function __toString()
    {
        return (string) $this->value;
    }

    protected function cast($value)
    {
        // @todo Handle this more gracefully...
        throw new \Exception('Cannot instantiate this class directly.');
    }

    protected static function is($value)
    {
        // @todo Handle this more gracefully...
        throw new \Exception('Cannot call this method on Data class directly.');
    }

}
