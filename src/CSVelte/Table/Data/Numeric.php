<?php namespace CSVelte\Table\Data;

use CSVelte\Table\Data as BaseData;

/**
 * Numeric data object
 *
 * @package    CSVelte
 * @subpackage CSVelte\Table
 * @copyright  (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author     Luke Visinoni <luke.visinoni@gmail.com>
 */
class Numeric extends BaseData
{
    protected $value;

    public function __construct($value)
    {
        $this->value = $this->cast($value);
    }

    protected function cast($val)
    {
        /*if (false !== strpos($val, '**')) {
            $parts = explode('**', $val, 2);
            if (strpos($parts[0], '-') === 0) $parts[0] = 0 - $parts[0];
            $val = ($parts[0]) ** ((int) $parts[1]);
        } else*/ if (false === strpos($val, '.')) {
            $val = (int) $val;
        } else {
            $val = (float) floatval($val);
        }
        return $val;
    }
}
