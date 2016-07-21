<?php namespace CSVelte\Table\DataType;
/**
 * Null (empty/undefined) data type
 *
 * @package    CSVelte
 * @subpackage CSVelte\Table\DataType
 * @copyright  (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author     Luke Visinoni <luke.visinoni@gmail.com>
 */
class Null extends AbstractType
{
    const TEXT = 'NULL';

    protected $type = 'null';

    protected function convert($val)
    {
        return null;
    }
}
