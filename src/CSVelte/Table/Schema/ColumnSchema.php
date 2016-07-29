<?php namespace CSVelte\Table\Schema;

use CSVelte\Utils;
use CSVelte\Contract\DataType;

/**
 * Table Column Schema Definition
 *
 * @package    CSVelte
 * @subpackage CSVelte\Table
 * @copyright  (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author     Luke Visinoni <luke.visinoni@gmail.com>
 * @todo Maybe rename this to CSVelte\Table\Schema\TableSchema?
 */
class ColumnSchema
{
    protected $id;

    protected $properties = array(
        'name' => null,
        'title' => null,
        'type' => 'string',
        'format' => null,
        'description' => null,
        'constraints' => array (
            'required' => null,
            'minLength' => null,
            'maxLength'=> null,
            'unique' => null,
            'pattern' => null,
            'minimum' => null,
            'maximum' => null,
            'enum' => null
        )
    );

    /**
     * Column Schema Constructor
     *
     * @param string A unique identifier for the column
     * @param array Properties for this column
     */
    public function __construct($id, $properties = null)
    {
        $this->setId($id);
        $this->setProperties($properties);
    }

    protected function setId($id)
    {
        $this->id = (string) $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function isValid(DataType $value)
    {
        foreach ($this->getConstraintAssertions() as $assert) {
            try {
                $assert((string) $value);
            } catch (FailedConstraintException $e) {
                $this->errors[] = $e;
            }
        }
    }

    /**
     * Return an array of functions that assert the value of each cell in the
     * column passes a given constraint (required, unique, maxLength, etc.)
     *
     * @todo Come back to this... it's not important for v0.0.1
     */
    protected function getConstraintAssertions()
    {
        foreach ($this->getConstraints() as $constraint => $param) {

        }
    }

    public function getConstraints()
    {
        return $this->properties['constraints'];
    }

    /**
     * Set properties
     *
     * @param array Properties for this column
     */
    protected function setProperties(array $properties)
    {
        foreach ($properties as $prop => $value) {
            if (array_key_exists($prop, $this->properties)) {
                if ($prop == 'constraints') $this->setConstraints($value);
                else $this->properties[$prop] = $value;
            } else {
                // unknown property... make note of it?
            }
        }
    }

    protected function setConstraints(array $constraints)
    {
        foreach ($constraints as $cnstr => $value) {
            // dd($this->properties['constraints']);
            if (array_key_exists($cnstr, $this->properties['constraints'])) {
                $this->properties['constraints'][$cnstr] = $value;
            } else {
                // unknown property... make note of it?
            }
        }
    }

    /**
     * Magic Property Getter
     */
    public function __call($method, $args)
    {
        $propname = substr(strtolower($method), 3);
        if (array_key_exists($propname, $this->properties)) {
            return Utils::array_get($this->properties, $propname);
        }
        throw new \BadMethodCallException('Unknown method: ' . get_class($this) . '::' . $method);
    }
}
