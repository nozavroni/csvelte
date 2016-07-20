<?php namespace CSVelte\Table\DataType;

use Carbon\Carbon;
use CSVelte\Contract\DataType as DataTypeContract;

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

    public function __construct($val)
    {
        $this->value = $val;
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

    /**
     * Cast/Convert to another (specified or implied) data type, returning a new
     * object rather than changing this one.
     *
     * @param string The type to convert to
     * @return CSVelte\Table\DataType
     */
    public function cast($type = null)
    {
        if (is_null($type)) {
            // try to infer intended data type from value
            if (is_numeric($this->value)) {
                // can only be boolean or numeric
                // it's possible for completely numeric data to be a date/time
                // but because there is absolutely NO way to differentiate fully
                // numeric data/time formats from numeric data without some type
                // of dashes, slashes, or punctuation, I have to assume numeric
                // The same is true for boolean values
                $type = self::TYPE_NUMERIC;
            } else {
                // not numeric, let's see if Carbon can parse it, then we know it
                // at least CAN be a date/time string
                // try {
                //     $datetime = Carbon::parse($this->value);
                // } catch (\Exception $e) {
                //     dd($e);
                // }
            }
        }
        $method = 'to' . ucfirst(strtolower($type));
        if (!method_exists($this, $method)) $method = 'toText';
        $cast = $this->$method();
        $class = 'CSVelte\\Table\\DataType\\' . implode(array_map(function($w){ return ucfirst(strtolower($w)); }, explode('-', $type)));
        return new $class($cast);
    }

    /**
     * Return a numeric representation of this object (type)
     * Specific type classes may further specify a routine to convert itself to
     * a meaningful numeric value. If no meaningful numeric value can be determ-
     * ined, then just cast to int. Although technically we should probably throw
     * an exception. Come back to this...
     *
     * @param void
     * @return int|float Numeric data
     * @access public
     * @todo I can't decide yet whether or not this abstract base class should
     *     define each of these toType() methods as abstract, as regular public
     *     functions that throw Implementation/NoMeaningfulConversionExceptions,
     *     I suppose that decision will likely be easier to make once I've worked
     *     more with my little "type" creation here...
     * @note These toType methods, I think, should return primitive data, or in
     *     the case of DateTime, maybe a Carbon object. But just not DataType
     *     objects. Just values to be passed to those objects' constructors.
     */
    public function toNumeric()
    {
        return (int) $this->value;
    }

    /**
     * Return a boolean representation of this object (type)
     *
     * @param void
     * @return boolean
     * @access public
     */
    public function toBoolean()
    {
        return (boolean) $this->value;
    }

    /**
     * Return a date/time representation of this object (type), if a meaningful
     * date/time representation can be determined. Or throw an exception. Deffs.
     *
     * @param void
     * @return boolean
     * @access public
     */
    public function toDateTime()
    {
        return (boolean) $this->value;
    }

    /**
     * Return a duration representation of this object (type)
     *
     * @param void
     * @return string Duration string according to same specs as iCalendar (RFC2445)
     * @access public
     */
    public function toDuration()
    {
        // @todo throw better exception...
        throw new \Exception('No meaningful "duration" representation of this object can be made.');
    }

    /**
     * Return a text (string) representation of this object (type)
     *
     * @param void
     * @return string
     * @access public
     */
    public function toText()
    {
        return (string) $this->value;
    }

    /**
     * String conversion overloading
     *
     * @param void
     * @return string
     * @access public
     */
    public function __toString()
    {
        return $this->toText();
    }
}
