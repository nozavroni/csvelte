<?php namespace CSVelte\Table\Schema\Constraint;

/**
 * "MinLength" Schema Constraint
 *
 * @package    CSVelte
 * @subpackage CSVelte\Table
 * @copyright  (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author     Luke Visinoni <luke.visinoni@gmail.com>
 */
class MinLength extends AbstractConstraint
{
    /**
     * @var string
     */
    protected $type = "minLength";

    /**
     * @var int
     */
    protected $min;

    /**
     * Class Constructor
     *
     * @param int
     */
    public function __construct($min)
    {
        $this->min = (int) $min;
    }

    /**
     * @inheritDoc
     */
    protected function isValid($str)
    {
        return strlen($str) >= $this->min;
    }
}
