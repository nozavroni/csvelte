<?php namespace CSVelte\Table\DataType;
/**
 * Boolean data type
 *
 * @package    CSVelte
 * @subpackage CSVelte\Table\DataType
 * @copyright  (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author     Luke Visinoni <luke.visinoni@gmail.com>
 */
class Boolean extends AbstractType
{
    const TRUE = 1;
    const FALSE = 0;

    /**
     * @var string A string label for this data type
     */
    protected $label = 'boolean';

    protected static $binaryStrings = array(
        array('false', 'true'),
        array('no', 'yes'),
        array('off', 'on'),
        array('-', '+'),
        array('0', '1'),
    );

    protected function convert($val)
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
    public static function addBinarySet(array $set)
    {
        if (count($set) !== 2) {
            throw new \InvalidArgumentException("Invalid argument, " . __METHOD__ . " expects an array, got: " . gettype($set));
        }
        return array_push(self::$binaryStrings, $set);
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
