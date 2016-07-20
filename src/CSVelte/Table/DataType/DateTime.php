<?php namespace CSVelte\Table\DataType;

use Carbon\Carbon;

/**
 * DateTime data type
 *
 * @package    CSVelte
 * @subpackage CSVelte\Table\DataType
 * @copyright  (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author     Luke Visinoni <luke.visinoni@gmail.com>
 */
class DateTime extends AbstractType
{
    protected $type = 'date-time';

    public function castTo($type)
    {

    }
}
