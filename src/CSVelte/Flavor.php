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
 *             - skipblank - I don't know where this one came from but it might
 *               be a decent idea to include it. Self explanatory
 *             - hasHeader - I'm not sure if the flavor should include this or
 *               not. The python module doesnt include it in its "dialect" class
 *               but I always kind of wondered why... maybe play around with the
 *               library once you have it flushed out a little and see what u think
 *               After thinking about it a bit, I realized why the csv module doesn't
 *               include it in the Dialiect. This is because they want to be able
 *               to include various concrete implementations of Dialect for "excel-tab",
 *               "standard-csv", "standard-tsv", etc. They cannot include things
 *               such as hasHeader and characterEncoding because then they would
 *               need to create concrete implementations of each of these variations
 *               of their concrete dialects "excel-tab-header", "excel-tab-noheader",
 *               "excel-tab-utf8", "excel-tab-utf8-header", etc. This would quickly
 *               get out of hand. So, what's the solution? Add a second set of
 *               attributes to flavor that is both optional AND mutable (unlike )
 *               attributes which are all required and totally immutable)
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
    protected $attributes = array(
        'delimiter' => ',',
        'quoteChar' => '"',
        'escapeChar' => '\\',
        'doubleQuote' => false,
        'skipInitialSpace' => false,
        'quoteStyle' => self::QUOTE_MINIMAL,
        'lineTerminator' => "\r\n",
        'header' => null
    );

    /**
     * Class constructor
     *
     * @param array The attributes that define this particular flavor. These
     *     attributes are immutable. They can only be set here.
     * @return void
     * @todo Should this throw an exception when attributes are invalid? I think so...
     * @todo I'm not sure I care for the idea of attributes being stored in an
     *     array rather than simply using class properties. What's the benefit?
     */
    public function __construct($attributes = null, $properties = array())
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
        return new Flavor(array_merge($this->attributes, $attribs));
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
