<?php namespace CSVelte\Table\Schema\Constraint;

/**
 * "MaxLength" Schema Constraint
 *
 * @package    CSVelte
 * @subpackage CSVelte\Table
 * @copyright  (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author     Luke Visinoni <luke.visinoni@gmail.com>
 */
class MaxLength extends AbstractConstraint
{
    /**
     * @var string
     */
    protected $type = "maxLength";

    /**
     * @var int
     */
    protected $max;

    /**
     * Class Constructor
     *
     * @param int
     */
    public function __construct($max)
    {
        $this->max = (int) $max;
    }

    /**
     * @inheritDoc
     */
    protected function isValid($str)
    {
        return strlen($str) <= $this->max;
    }
}
