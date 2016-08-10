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
use Carbon\CarbonInterval;

/**
 * Date/Time Duration Value Class
 *
 * @package    CSVelte
 * @subpackage CSVelte\Table\Data
 * @copyright  (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author     Luke Visinoni <luke.visinoni@gmail.com>
 * @see ISO-8601 Durations: https://en.wikipedia.org/wiki/ISO_8601#Durations
 */
class DurationValue extends Value
{
    /**
     * @var string Validation regex pattern
     * @todo Should this allow an optional + as well?
     */
    protected $pattern = '/^-?P([0-9]+Y)?([0-9]+M)?([0-9]+W)?([0-9]+D)?T?([0-9]+H)?([0-9]+M)?([0-9]+S)?$/i';

    /**
     * @inheritDoc
     */
    public function __construct($value = null)
    {
        if (empty($value)) {
            $this->value = null;
            $this->strValue = "";
        } elseif ($value instanceof \DateInterval) {
            $this->value = $this->fromString($value);
            // @todo I need to implement a format that generates the ISO8601 duration string
            $this->strValue = (string) $this->value;
        } else {
            parent::__construct($value);
        }
    }

    /**
     * Convert a string to a Carbon\Carbon object
     *
     * @param string A date/time string to parse into a valid Carbon date/time
     * @return Carbon\Carbon
     * @access protected
     */
    protected function fromString($val)
    {
        $intvl = null;
        if (is_object($val)) {
            if ($val instanceof Duration) {
                return $val->getValue();
            } elseif ($val instanceof \DateInterval) {
                $intvl = $val;
            }
        } elseif (is_string($val) && !empty($val)) { // @todo test empty string
            $firstchar = $val[0];
            if ($firstchar == '+' || $firstchar == '-') {
                $val = substr($val, 1);
            }
            // this will throw an exception on invalid duration string
            $intvl = new \DateInterval($val);
            if ($firstchar == '-') $intvl->invert = 1;
        }
        if (is_null($intvl)) throw new \InvalidArgumentException('DataType "duration" initialized with invalid value: "' . $val . '"');
        try {
            return CarbonInterval::instance($intvl);
        } catch (\InvalidArgumentException $e) {
            // most likely means intvl was created from a diff, which is not allowed
            return new CarbonInterval(
                0, // years
                0, // months
                0, // weeks
                $intvl->format('%a'), // days
                $intvl->format('%h'), // hours
                $intvl->format('%i'), // minutes
                $intvl->format('%s') // seconds
            );
        }
    }
}
