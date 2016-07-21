<?php namespace CSVelte\Table\DataType;

use Carbon\Carbon;

/**
 * Duration data type
 *
 * @package    CSVelte
 * @subpackage CSVelte\Table\DataType
 * @copyright  (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author     Luke Visinoni <luke.visinoni@gmail.com>
 */
class Duration extends AbstractType
{
    protected $type = 'duration';

    protected function convert($val)
    {
        throw new NotYetImplementedException();
    }
}
