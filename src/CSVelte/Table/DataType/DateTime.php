<?php namespace CSVelte\Table\DataType;

use Carbon\Carbon;

/**
 * DateTime data type
 *
 * @package    CSVelte
 * @subpackage CSVelte\Table\DataType
 * @copyright  (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author     Luke Visinoni <luke.visinoni@gmail.com>
 * @todo Perhaps I should also create a CSVelte\Table\DataType\DateTime\Date,
 *     Time, TimeZone, Duration, Period, Interval? etc. Think about it. Also
 *     check the CSVW documents and see what they recommend.
 * @todo It might be a good idea to move the DataType namespace up a level. They
 *     don't necessarily have to reside within a "table" to be data values of a
 *     specific type, possessing specific properties and behaviors.
 */
class DateTime extends AbstractType
{
    /**
     * @var string
     */
    protected $type = 'date-time';

    /**
     * @var Carbon\Carbon
     */
    protected $value;

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
     * Convert a value (typically a string) to a Carbon\Carbon object
     *
     * @param string A date/time string to parse into a valid Carbon date/time
     * @return Carbon\Carbon
     * @access protected
     */
    protected function convert($val)
    {
        if (is_null($val)) {
            return Carbon::now();
        };
        if (is_integer($val)) {
            // assume it's a unix timestamp
            return Carbon::createFromTimestamp($val);
        }
        if (is_object($val)) {
            if ($val instanceof Carbon) {
                return $val;
            }
            if ($val instanceof \DateTime) {
                return Carbon::instance($val);
            }
        }
        return Carbon::parse($val);
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
