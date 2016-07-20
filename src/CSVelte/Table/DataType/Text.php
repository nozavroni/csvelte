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

    public function castTo($type)
    {

    }

    /**
     * @inheritDoc
     */
    public function toNumeric()
    {
        if (false !== strpos($this->value, '.')) {
            return (float) $this->value;
        }
        return (int) $this->value;
    }
}
