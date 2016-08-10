<?php
/**
 * CSVelte: Slender, elegant CSV for PHP
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV 
 * standardization efforts, CSVelte was written in an effort to take all the 
 * suck out of working with CSV. 
 *
 * @version   v0.1
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace CSVelte\Table\Data;

use CSVelte\Contract\DataType;
use CSVelte\Exception\CSVelteException;
use CSVelte\Exception\NotYetImplementedException;

/**
 * Base Data Value Class
 * This class/object represents a specific value within a table cell. It can be
 * one of many various data types. It can even contain multiple values (see
 * CSV\Table\Data\MultipleValue, Array, Object, etc. for more on that).
 *
 * @package    CSVelte
 * @subpackage CSVelte\Table\Data
 * @copyright  (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author     Luke Visinoni <luke.visinoni@gmail.com>
 */
class Value implements DataType
{
    /**
     * @var string The original, string representation of this data value
     */
    protected $strValue;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var string Some data types come in various formats
     */
    protected $format;

    /**
     * @var string Rexex validation pattern
     */
    protected $pattern;

    /**
     * Data value constructor
     * Data values are immutable so the value of an object MUST be set in theconstructor
     * constructor or not at all. All data types should be able to support a null
     * or empty value so that a column of a certain type may contain empty cells.
     *
     * @param string|mixed The value of this data object, often a string to be
     *     converted into whatever enternal storage data type
     * @return void
     * @access public
     */
    public function __construct($value = null)
    {
        // @todo If value is not a string and can't be converted to one, I don't
        // really know what the best option is...
        if (is_object($value) && !method_exists($value, '__toString')) {
            // throw exception? Just make note of it and move on?
        }
        $this->strValue = (string) $value;
        $this->value = $this->fromString($value);;
    }

    /**
     * Convert this object to a string
     *
     * @param void
     * @return string
     * @access public
     */
    public function __toString()
    {
        return $this->strValue;
    }

    /**
     * Determine if $this->strValue input matches $this->pattern
     * This isn't all that important right now but will become important when I
     * go to implement things such as schema validation, table columns, etc.
     *
     * @param void
     * @return boolean
     * @access public
     */
    public function isValid()
    {
        if (is_null($this->pattern)) {
            throw new NotYetImplementedException('No pattern implemented for: ' . get_class($this));
        }
        if (false === ($res = preg_match($this->pattern, $this->strValue))) {
            // @todo Create a RegexException that knows how to report regex errors
            // @see http://php.net/manual/en/pcre.constants.php
            throw new CSVelteException('Invalid regex pattern for type: ' . get_class($this));
        }
        return (bool) $res;
    }

    /**
     * Convert the string representation of this value into its semantic value
     *
     * @param string
     * @return mixed
     * @todo This should probably be renamed init() or like... cast/convert
     */
    protected function fromString($string)
    {
        if (!is_string($string)) {
            // throw exception? return $string?
        }
        return $string;
    }

    /**
     * Return the internal semantic representation of the value (as opposed to
     * the string value that typically comes from reading from a CSV file)
     *
     * @param void
     * @return mixed
     * @access public
     */
    public function getValue()
    {
        return $this->value;
    }

}
