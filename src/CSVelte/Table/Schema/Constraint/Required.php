<?php namespace CSVelte\Table\Schema\Constraint;

/**
 * "Required" Schema Constraint
 *
 * @package    CSVelte
 * @subpackage CSVelte\Table
 * @copyright  (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author     Luke Visinoni <luke.visinoni@gmail.com>
 */
class Required extends AbstractConstraint
{
    /**
     * @var string
     */
    protected $type = "required";

    protected function isValid($str)
    {
        return !empty($str);
    }
}
