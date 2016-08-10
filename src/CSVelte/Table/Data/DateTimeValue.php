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

use Carbon\Carbon;

/**
 * DateTime Value Class
 *
 * @package    CSVelte
 * @subpackage CSVelte\Table\Data
 * @copyright  (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author     Luke Visinoni <luke.visinoni@gmail.com>
 * @todo Perhaps I should also create a CSVelte\Table\DataType\DateTime\Date,
 *     Time, TimeZone, Duration, Period, Interval? etc. Think about it. Also
 *     check the CSVW documents and see what they recommend.
 * @todo It might be a good idea to move the Data\Value namespace up a level. They
 *     don't necessarily have to reside within a "table" to be data values of a
 *     specific type, possessing specific properties and behaviors. CSVelte\Data\Value
 */
class DateTimeValue extends Value
{
    /**
     * @var string
     * @static
     */
    protected static $defaultToStringFormat = \DateTime::ISO8601;

    /**
     * @var string
     * @static
     */
    protected static $toStringFormat;

    /**
     * @var string The validation regex pattern
     * @todo There are just too many possibilities to reliably validate in this
     *     way. I am using Carbon instead.
     */
    protected $pattern = '/.*/';

    public function __construct($value = null)
    {
        // @todo If value is not a string and can't be converted to one, I don't
        // really know what the best option is...
        if ($value instanceof \DateTime) {
            $this->strValue = $value->format(\DateTime::ISO8601);
        } else $this->strValue = (string) $value;
        $this->value = $this->fromString($value);;
    }

    public function isValid()
    {
        try {
            $date = Carbon::parse($this->strValue);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * Object to string overloading
     *
     * @param void
     * @return string ISO-8601 formatted date/time/timezone
     * @access public
     * @todo For @CSVW-compliance I'll need to do a major overhaul of not just
     *     it's string format, but the class in its entirety. The definition for
     *     dates and times within the CSVW specs is an order of magnitude more
     *     complex and involved... and complicated.
     */
    public function __toString()
    {
        // @todo I am not going for full CSVW-compliance, at least not yet, but
        // if I was, I believe this is the correct textual representation of a date
        return $this->value->format(self::$toStringFormat ?: self::$defaultToStringFormat);
    }

    /**
     * Convert a string to a Carbon\Carbon object
     *
     * @param string A date/time string to parse into a valid Carbon date/time
     * @return Carbon\Carbon
     * @access protected
     */
    protected function fromString($str)
    {
        if (empty($str)) {
            return Carbon::now();
        };
        if (is_integer($str)) {
            // assume it's a unix timestamp
            return Carbon::createFromTimestamp($str);
        }
        if (is_object($str)) {
            if ($str instanceof Carbon) {
                return $str;
            }
            if ($str instanceof \DateTime) {
                return Carbon::instance($str);
            }
        }
        return Carbon::parse($str);
    }

    public static function setToStringFormat($format)
    {
        self::$toStringFormat = (string) $format;
    }

    public static function resetToStringFormat()
    {
        self::$toStringFormat = null;
    }
}
