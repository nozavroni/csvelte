<?php namespace CSVelte\Table\DataType;

use Carbon\Carbon;
use CSVelte\Contract\DataType as DataTypeContract;
use CSVelte\Exception\NotYetImplementedException;

/**
 * Abstract/Base Data Type Class
 * Although this class and its descendants represent "types" of data rather than
 * the actual value, objects of this type are given values so that they may be
 * converted into and out of their respective type at will.
 *
 * @package    CSVelte
 * @subpackage CSVelte\Table
 * @copyright  (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author     Luke Visinoni <luke.visinoni@gmail.com>
 * @todo This is my second attempt at DataType and for the second time, they are
 *       becoming way overly complicated and confusing. Back to the drawing board
 *       ...... again!
 */
abstract class AbstractType implements DataTypeContract
{
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_DATETIME = 'date-time';
    const TYPE_DURATION = 'duration';
    const TYPE_NULL = 'null';
    const TYPE_NUMERIC = 'numeric';
    const TYPE_TEXT = 'text';

    /**
     * @var string lower case words delimited by dashes
     */
    protected $type;

    /**
     * @var mixed The original value that was passed to constructor, as-is.
     */
    protected $value;

    public function __construct($val = null)
    {
        // DataType objects are immutable so I don't want a setValue() method,
        // not even a private/protected one. So the convert method simply accepts
        // any value and delegates to descendants to convert that value to a its
        // own internal storage format. Then I set this object's value to what
        // it returns.
        $this->value = $this->convert($val);
    }

    /**
     * Returns data type
     *
     * @param void
     * @return string
     * @access public
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns native internal data value
     *
     * @param void
     * @return mixed
     * @access public
     */
    public function getValue()
    {
        return $this->value;
    }

    protected function convert($val)
    {
        throw new NotYetImplementedException('This method has not been implemented: ' . __METHOD__);
    }

    public function __toString()
    {
        return (string) $this->value;
    }
}
