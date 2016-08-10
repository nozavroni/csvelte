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

/**
 * Boolean Value Class
 *
 * @package    CSVelte
 * @subpackage CSVelte\Table\Data
 * @copyright  (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author     Luke Visinoni <luke.visinoni@gmail.com>
 */
class BooleanValue extends Value
{
    const FALSE = 0;
    const TRUE = 1;

    protected static $binaryStrings = array(
        array('false','true'),
        // array('f','t'),
        array('no', 'yes'),
        // array('n','y'),
        array('off', 'on'),
        // array('-', '+'),
        array('0', '1'),
    );

    /**
     * @inheritDoc
     * @todo rather than having to redefine the isValid method here, I should
     *     just create a method called getPattern() that would be ran from within
     *     Valid::isValid() so that the pattern could be dynamic as it is here.
     */
    public function isValid()
    {
        $this->pattern = '/^(' . implode('|', array_map(function($set){
            return '(' . implode('|', array_map(function($v){
                return preg_quote($v, '/');
            }, $set)) . ')';
        }, self::$binaryStrings)) . ')$/i';
        return parent::isValid();
    }

    protected function fromString($val)
    {
        if (is_string($val)) {
            // boolean string can be true/false, yes/no, on/off, or 1/0
            // but I only need to check for falsy strings because ANY non-empty
            // string will return true
            $falsey = implode('|', array_map(function($binary){ return preg_quote($binary[0]); }, self::$binaryStrings));
            if (preg_match("/({$falsey})/i", $val)) return false;
        }
        return (bool) $val;
    }

    /**
     * Add custom true/false string set
     *
     * @param array Two-element set like [falsey, truthy]
     * @return integer The number of allowable false/true sets
     * @access public
     * @static
     * @todo Should I add a method called addBinaryPatternSet() that allows a set
     *     of regex patterns? I think that might be useful...
     */
    public static function addBinarySet($false, $true)
    {
        return array_push(self::$binaryStrings, array($false, $true));
    }

    /**
     * Get allowable true/false sets
     *
     * @return array
     * @access public
     * @static
     */
    public static function getBinarySetList()
    {
        return self::$binaryStrings;
    }
}
