<?php namespace CSVelte\Table\DataType;
/**
 * Numeric data type
 *
 * @package    CSVelte
 * @subpackage CSVelte\Table\DataType
 * @copyright  (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author     Luke Visinoni <luke.visinoni@gmail.com>
 */
class Numeric extends AbstractType
{
    protected $type = 'numeric';

    protected function convert($val)
    {
        $val = preg_replace('/[^0-9\.]/', '', $val);
        return (false === strpos($val, '.')) ? (int) $val : (float) $val;
    }
}
