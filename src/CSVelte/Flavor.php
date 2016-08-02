<?php namespace CSVelte;

use CSVelte\Exception\UnknownAttributeException;
use CSVelte\Exception\UnknownFlavorException;
use CSVelte\Exception\ImmutableException;

/**
 * CSVelte\Flavor
 * Represents a particular CSV format
 *
 * @package   CSVelte
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @todo      Might move this into CSVelte\Flavor\Flavor and make it abstract
 */
class Flavor
{
    /**
     * @costant All columns should be quoted, regardless of data type
     */
    const QUOTE_ALL = 'quote_all';

    /**
     * @costant No columns should be quoted, regardless of data type
     */
    const QUOTE_NONE = 'quote_none';

    /**
     * @costant Quote only columns that contain special characters such as
     *          delimiter characters, quotes, newlines, etc.
     */
    const QUOTE_MINIMAL = 'quote_minimal';

    /**
     * @costant All non-numeric columns should be quoted
     */
    const QUOTE_NONNUMERIC = 'quote_nonnumeric';

    protected $delimiter = ",";
    protected $quoteChar = '"';
    // @todo should this be null?
    protected $escapeChar = '\\';
    protected $doubleQuote = true;
    protected $skipInitialSpace = false;
    protected $quoteStyle = self::QUOTE_MINIMAL;
    protected $lineTerminator = "\r\n";
    protected $header;

    /**
     * Class constructor
     *
     * @param array The attributes that define this particular flavor. These
     *     attributes are immutable. They can only be set here.
     * @return void
     * @todo Should this throw an exception when attributes are invalid? I think so...
     */
    public function __construct($attributes = null)
    {
        if (!is_null($attributes)) {
            if (!is_array($attributes)) {
                // @todo throw exception?
                return;
            }
            foreach ($attributes as $attr => $val) {
                $this->assertValidAttribute($attr);
                $this->$attr = $val;
            }
        }
    }

    // this is the stupidest thing ever... it just keeps telling me "Class CSVelte\Flavor\Flavor not found on line 79"... $classpath is NEVER equal to CSVelte\Flavor\Flavor. Not at ANY point! WTF?? This is driving me nuts!!
    // public static function create($name)
    // {
    //     $class = implode(array_map(function($v){ return ucfirst(strtolower($v)); }, explode('-', $name)));
    //     $classpath = 'CSVelte\\Flavor\\' . $class;
    //     if (!class_exists($classpath)) {
    //         throw new UnknownFlavorException('Unknown CSV flavor: ' . $name);
    //     }
    //     return new $classpath;
    // }

    /**
     * Assert that a particular attribute is valid (basically just that itexists) and throw an exception otherwise
     * exists) and throw an exception otherwise
     *
     * @param string The attribute to check validity of
     * @return void
     * @access protected
     * @throws UnknownAttributeException
     * @todo This should accept a second parameter for value that asserts the value
     *     is a valid value
     */
    protected function assertValidAttribute($attr)
    {
        if (!property_exists(self::class, $attr))
            throw new UnknownAttributeException("Unknown attribute: " . $attr);
    }

    /**
     * Copy this flavor object (optionally with new attributes)
     *
     * @param array
     * @return CSVelte\Flavor
     * @access public
     * @todo I may want to remove the array type-hint so that this can accept
     *     array-like objects and iterables as well. Not sure...
     */
    public function copy(array $attribs = array())
    {
        // $attributes = array_merge(get_class_vars(self::class), get_object_vars($this));
        $attributes = get_object_vars($this);
        return new Flavor(array_merge($attributes, $attribs));
    }

    /**
     * Attribute accessor magic method
     *
     * @param string The attribute to "get"
     * @return string The attribute value
     * @access public
     * @throws CSVelte\Exception\UnknownAttributeException
     */
    public function __get($attr)
    {
        $this->assertValidAttribute($attr);
        return $this->$attr;
    }

    /**
     * Attribute accessor (setter) magic method - Disabled because attributes are
     * immutable (read-only)
     *
     * @param string The attribute to "set"
     * @param string The attribute value
     * @return void
     * @access public
     * @throws CSVelte\Exception\ImmutableException
     */
    public function __set($attr, $val)
    {
        throw new ImmutableException("Cannot change attributes on an immutable object: " . self::class);
    }

}
