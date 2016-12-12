<?php

/*
 * CSVelte: Slender, elegant CSV for PHP
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV
 * standardization efforts, CSVelte was written in an effort to take all the
 * suck out of working with CSV.
 *
 * @version   v0.2.3
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace CSVelte;

use CSVelte\Exception\ImmutableException;
use InvalidArgumentException;

/**
 * CSV Flavor.
 *
 * Represents a particular "flavor"  of CSV. Inspired by python's csv "dialects".
 * Also inspired by Frictionless Data's "dialect description" format and the W3C's
 * CSV on the Web Working Group and their work on CSV dialects.
 *
 * @package CSVelte
 * @subpackage Flavor
 *
 * @since v0.1
 *
 * @property string $delimiter The delimiter character
 * @property string $quoteChar The quoting character
 * @property string $lineTerminator The character sequence used to terminate rows of data
 * @property string $escapeChar The character used to escape quotes within a quoted string
 *     Mutually exclusive to $doubleQuote
 * @property bool $doubleQuote If true, quote characters will be escaped by preceding them
 *     with another quote character. Mutually exclusive to $escapeChar
 * @property string $quoteStyle One of four class constants that determine which cells are quoted
 * @property bool $header If true, first row should be treated as a header row
 */
class Flavor
{
    /**
     * Quote all cells.
     * Set Flavor::$quoteStyle to this to quote all cells, regardless of data type.
     *
     * @var string
     */
    const QUOTE_ALL = 'quote_all';

    /**
     * Quote no cells.
     * Set Flavor::$quoteStyle to this to quote no columns, regardless of data type.
     *
     * @var string
     */
    const QUOTE_NONE = 'quote_none';

    /**
     * Quote minimal columns.
     * Set Flavor::$quoteStyle to this to quote only cells that contain special
     * characters such as newlines or the delimiter character.
     *
     * @var string
     */
    const QUOTE_MINIMAL = 'quote_minimal';

    /**
     * Quote non-numeric cells.
     * Set Flavor::$quoteStyle to this to quote only cells that contain
     * non-numeric data.
     *
     * @var string
     */
    const QUOTE_NONNUMERIC = 'quote_nonnumeric';

    /**
     * Delimiter character.
     * This is the character that will be used to separate data cells within a
     * row of CSV data. Usually a comma.
     *
     * @var string
     */
    protected $delimiter = ',';

    /**
     * Quote character.
     * This is the character that will be used to enclose (quote) data cells. It
     * is usually a double quote character but single quote is allowed.
     *
     * @var string
     */
    protected $quoteChar = '"';

    /**
     * Escape character.
     * This character will be used to escape quotes within quoted text. It is
     * mutually exclusive to the doubleQuote attribute. Usually a backspace.
     *
     * @var string
     */
    protected $escapeChar = '\\';

    /**
     * Double quote escape mode.
     * If set to true, quote characters within quoted text will be escaped by
     * preceding them with the same quote character.
     *
     * @var bool
     */
    protected $doubleQuote = true;

    /**
     * Not yet implemented.
     *
     * @ignore
     */
    // protected $skipInitialSpace = false;

    /**
     * Quoting style.
     * This may be set to one of four values:
     *     * *Flavor::QUOTE_NONE* - To never quote data cells
     *     * *Flavor::QUOTE_ALL* - To always quote data cells
     *     * *Flavor::QUOTE_MINIMAL* - To only quote data cells that contain special characters such as quote character or delimiter character
     *     * *Flavor::QUOTE_NONNUMERIC* - To quote data cells that contain non-numeric data.
     *
     * @var string
     */
    protected $quoteStyle = self::QUOTE_MINIMAL;

    /**
     * Line terminator string sequence.
     * This is a character or sequence of characters that will be used to denote
     * the end of a row within the data.
     *
     * @var string
     */
    protected $lineTerminator = "\r\n";

    /**
     * Header.
     * If set to true, this means the first line of the CSV data is to be treated
     * as the column headers.
     *
     * @var bool
     */
    protected $header;

    /**
     * Class constructor.
     *
     * The attributes that make up a flavor object can only be specified by
     * passing them in an array as key => value pairs to the constructor. Once
     * the flavor object is created, its attributes cannot be changed.
     *
     * @param array $attributes The attributes that define this particular flavor. These
     *                          attributes are immutable. They can only be set here.
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

    /**
     * Attribute accessor magic method.
     *
     * @param string $attr The attribute to "get"
     *
     * @throws InvalidArgumentException
     *
     * @return string The attribute value
     *
     * @internal
     */
    public function __get($attr)
    {
        $this->assertValidAttribute($attr);

        return $this->$attr;
    }

    /**
     * Attribute accessor (setter) magic method.
     * Disabled because attributes are immutable (read-only).
     *
     * @param string $attr The attribute name you're attempting to set
     * @param mixed  $val  The attribute value
     *
     * @throws ImmutableException
     *
     * @internal param The $string attribute to "set"
     * @internal param The $string attribute value
     * @internal
     */
    public function __set($attr, $val)
    {
        throw new ImmutableException('Cannot change attributes on an immutable object: ' . self::class . '::$' . $attr);
    }

    /**
     * Does this flavor of CSV have a header row?
     *
     * The difference between $flavor->header and $flavor->hasHeader() is that
     * hasHeader() is always going to give you a boolean value, whereas
     * $flavor->header may be null. A null value for header could mean that the
     * taster class could not reliably determine whether or not there was a
     * header row or it could simply mean that the flavor was instantiated with
     * no value for the header property.
     *
     * @return bool
     */
    public function hasHeader()
    {
        return (bool) $this->header;
    }

    /**
     * Copy this flavor object.
     *
     * Because flavor attributes are immutable, it is implossible to change their
     * attributes. If you need to change a flavor's attributes, call this method
     * instead, specifying which attributes are to be changed.
     *
     * @param array $attribs An array of attribute name/values to change in the copied flavor
     *
     * @return Flavor A flavor object with your new attributes
     *
     * @todo I may want to remove the array type-hint so that this can accept
     *     array-like objects and iterables as well. Not sure...
     */
    public function copy(array $attribs = [])
    {
        return new self(array_merge($this->toArray(), $attribs));
    }

    /**
     * Get this object as an array.
     *
     * @return array This object as an array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }

    /**
     * Assert valid attribute name.
     * Assert that a particular attribute is valid (basically just that it exists)
     * and throw an exception otherwise.
     *
     * @param string $attr The attribute to check validity of
     *
     * @throws InvalidArgumentException
     *
     * @internal
     *
     * @todo This should accept a second parameter for value that asserts the value
     *     is a valid value
     */
    protected function assertValidAttribute($attr)
    {
        if (!property_exists(self::class, $attr)) {
            throw new InvalidArgumentException('Unknown attribute: ' . $attr);
        }
    }
}
