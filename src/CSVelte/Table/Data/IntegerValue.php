<?php namespace CSVelte\Table\Data;

/**
 * Integer Value Class
 *
 * @package    CSVelte
 * @subpackage CSVelte\Table\Data
 * @copyright  (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author     Luke Visinoni <luke.visinoni@gmail.com>
 */
class IntegerValue extends Value
{
    protected $pattern = '/^\d+$/';

    protected function fromString($str)
    {
        return (int) $str;
    }
}
