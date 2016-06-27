<?php namespace CSVelte;

use CSVelte\Exception\UnknownAttributeException;
use CSVelte\Exception\ImmutableException;

/**
 * CSVelte\Flavor
 * Represents a particular CSV format
 *
 * @package   CSVelte
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @todo      The python module that inspired this library has more attributes
 *            than what I'm using here. I need to take a look at those attributes
 *            and make a determination as to whether I want o implement them
 *             - doublequote: boolean value - I think it specifies whether or not
 *               double quotes are escaped with ANOTHER double quote within a quoted string
 *             - skipinitialspace: I'm less certain about this one, but I'm
 *               thinking possibly, columns that have newlines may begin with a
 *               space indent after each newline. look at the RFC
 *             - _name: This is a label for descendants of this class to diff=
 *               erentiate them from each other
 *             - _valid: I think this is some internal legacy python inheritance class thing...
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

    /**
     * @var array Describes a particular CSV file's "flavor" or format. These
     *     attributes are immutable. They cannot be changed after constructing
     *     a flavor object
     */
    protected $attributes = [
        'delimiter' => ',',
        'quoteChar' => '"',
        'quoteStyle' => self::QUOTE_MINIMAL,
        'escapeChar' => '\\',
        'lineTerminator' => "\r\n"
    ];

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
                $this->attributes[$attr] = $val;
            }
        }
    }

    /**
     * Assert that a particular attribute is valid (basically just that itexists) and throw an exception otherwise
     * exists) and throw an exception otherwise
     *
     * @param string The attribute to check validity of
     * @return void
     * @access protected
     * @throws UnknownAttributeException
     */
    protected function assertValidAttribute($attr)
    {
        if (!array_key_exists($attr, $this->attributes))
            throw new UnknownAttributeException("Unknown attribute: " . $attr);
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
        return $this->attributes[$attr];
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
