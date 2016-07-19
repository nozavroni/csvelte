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

    protected static function is($checkval)
    {
        return is_numeric($checkval);
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
