-------------------------
CSVelte\\Flavor\\ExcelTab
-------------------------

.. php:namespace: CSVelte\\Flavor

.. php:class:: ExcelTab

    Excel CSV "flavor"
    This is the most common flavor of CSV as it is what is produced by Excel, the
    900 pound Gorilla of CSV importing/exporting. It is also technically the
    "standard" CSV format according to RFC 4180

    .. php:const:: QUOTE_ALL

        Quote all cells.
        Set Flavor::$quoteStyle to this to quote all cells, regardless of data type

    .. php:const:: QUOTE_NONE

        Quote no cells.
        Set Flavor::$quoteStyle to this to quote no columns, regardless of data type

    .. php:const:: QUOTE_MINIMAL

        Quote minimal columns.
        Set Flavor::$quoteStyle to this to quote only cells that contain special
        characters such as newlines or the delimiter character

    .. php:const:: QUOTE_NONNUMERIC

        Quote non-numeric cells.
        Set Flavor::$quoteStyle to this to quote only cells that contain
        non-numeric data

    .. php:attr:: delimiter

        protected

    .. php:attr:: escapeChar

        protected

    .. php:attr:: quoteChar

        protected string

        Quote character.
        This is the character that will be used to enclose (quote) data cells. It
        is usually a double quote character but single quote is allowed.

    .. php:attr:: doubleQuote

        protected boolean

        Double quote escape mode.
        If set to true, quote characters within quoted text will be escaped by
        preceding them with the same quote character.

    .. php:attr:: skipInitialSpace

        protected

        Not yet implemented

    .. php:attr:: quoteStyle

        protected string

        Quoting style.
        This may be set to one of four values:
        * *Flavor::QUOTE_NONE* - To never quote data cells
        * *Flavor::QUOTE_ALL* - To always quote data cells
        * *Flavor::QUOTE_MINIMAL* - To only quote data cells that contain special
        characters such as quote character or delimiter character
        * *Flavor::QUOTE_NONNUMERIC* - To quote data cells that contain
        non-numeric data

    .. php:attr:: lineTerminator

        protected string

        Line terminator string sequence.
        This is a character or sequence of characters that will be used to denote
        the end of a row within the data

    .. php:attr:: header

        protected boolean

        Header.
        If set to true, this means the first line of the CSV data is to be treated
        as the column headers.

    .. php:method:: __construct($attributes = null)

        Class constructor

        The attributes that make up a flavor object can only be specified by
        passing them in an array as key => value pairs to the constructor. Once
        the flavor object is created, its attributes cannot be changed.

        :param $attributes:

    .. php:method:: hasHeader()

        Does this flavor of CSV have a header row?

        The difference between $flavor->header and $flavor->hasHeader() is that
        hasHeader() is always going to give you a boolean value, whereas
        $flavor->header may be null. A null value for header could mean that the
        taster class could not reliably determine whether or not there was a
        header row or it could simply mean that the flavor was instantiated with
        no value for the header property.

        :returns: boolean

    .. php:method:: assertValidAttribute($attr)

        Assert valid attribute name.
        Assert that a particular attribute is valid (basically just that it
        exists)
        and throw an exception otherwise

        :param $attr:
        :returns: void

    .. php:method:: copy($attribs = array())

        Copy this flavor object

        Because flavor attributes are immutable, it is implossible to change their
        attributes. If you need to change a flavor's attributes, call this method
        instead, specifying which attributes are to be changed.

        :param $attribs:
        :returns: CSVelte\Flavor A flavor object with your new attributes

    .. php:method:: __get($attr)

        Attribute accessor magic method

        :param $attr:
        :returns: string The attribute value

    .. php:method:: __set($attr, $val)

        Attribute accessor (setter) magic method.
        Disabled because attributes are immutable (read-only)

        :param $attr:
        :param $val:
        :returns: void
