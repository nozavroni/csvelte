<?php namespace CSVelte\Table\DataType;
/**
 * Boolean data type
 *
 * @package    CSVelte
 * @subpackage CSVelte\Table\DataType
 * @copyright  (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author     Luke Visinoni <luke.visinoni@gmail.com>
 */
class Boolean extends AbstractType
{
    /**
     * @var string A string label for this data type
     */
    protected $label = 'boolean';

    public function castTo($type)
    {

    }
}
