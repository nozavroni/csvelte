<?php namespace CSVelte\Table\Schema\Constraint;

/**
 * Schema Constraint
 *
 * @package    CSVelte
 * @subpackage CSVelte\Table
 * @copyright  (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author     Luke Visinoni <luke.visinoni@gmail.com>
 * @todo GET RID OF THIS! I want to use simple anonymous functions instead if
 *     possible. Plus this can wait for the next version. Plus, for the "unique"
 *     constraint, I need to be able to loop through the entire column so I need
 *     access to the column I'm checking so this class won't work. 
 */
abstract class AbstractConstraint
{
    /**
     * @var string
     */
    protected $type;

    /**
     * Assert string validates according to class constraint
     *
     * @param void
     * @return void
     * @throws CSVelte\Exception\SchemaConstraintException
     */
    public function assert($str)
    {
        if (!$this->isValid($str)) {
            throw new SchemaConstraintException("Schema constraint failed: " . $this->getType());
        }
    }

    /**
     * Get constraint type
     *
     * @param void
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Determine if a string is valid according to this constraint
     *
     * @param string
     * @return bool
     */
    abstract protected function isValid($str);
}
