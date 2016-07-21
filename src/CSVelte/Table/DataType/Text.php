<?php namespace CSVelte\Table\DataType;
/**
 * Text data type
 *
 * @package    CSVelte
 * @subpackage CSVelte\Table\DataType
 * @copyright  (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author     Luke Visinoni <luke.visinoni@gmail.com>
 */
class Text extends AbstractType
{
    protected $type = 'text';

    protected function convert($val)
    {
        if (is_bool($val)) {
            $val = ($val) ? Boolean::TRUE : Boolean::FALSE;
        }
        return (string) $val;
    }
}
