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
class Boolean extends BaseData
{
    protected $value;

    public function __toString()
    {
        return ($this->value) ? 'true' : 'false';
    }

    protected static function is($checkval)
    {
        $lwr = strtolower($checkval);
        return (
            is_bool($checkval) ||
            ($lwr == 'true' || $lwr == 'false') ||
            ($lwr == '1' || $lwr == '0') ||
            ($lwr == 'on' || $lwr == 'off')
        );
    }

    protected function cast($val)
    {
        $lwr = strtolower($val);
        if ($lwr == 'false' || $lwr == '0' || $lwr == 'off') $val = false;
        $val = (bool) $val;
        return $val;
    }
}
