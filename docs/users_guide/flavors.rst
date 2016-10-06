##############
Flavors of CSV
##############

How does CSVelte address the extremely loose nature of CSV as a format? It allows the developer define "flavors" of CSV, or in other words, classes or objects representing a particular set of CSV formatting attributes. Flavors can either be defined at runtime by instantiating a :php:class:`Flavor` class, passing an associative array of CSV formatting attributes to its constructor, or they can be defined at compile time, by extending the :php:class:`Flavor` class and setting its attributes internally. CSVelte ships with :php:class:`Flavor` classes representing several of the most commonly-used CSV flavors, but we'll get to that in a minute. First let's go over the various attributes that, together, define a CSV flavor.

Flavor Attributes
=================

    header
        Specifies whether to treat the first row of the dataset as a header row. If ``true``, the first row will be ignored by the :php:class:`Reader` class when iterating over a dataset. Defaults to ``null``

    delimiter
        Specifies a single character to be used as the field separator. Defaults to ``,``. Other common values are ``\t``, and ``|``.

    lineTerminator
        Specifies a character or sequence of characters used to terminate each row. Defaults to ``\r\n``. Other common values are ``\n`` and ``\r``.

    quoteChar
        Specifies a single character to be used for quoting fields. Defaults to ``"``. Other common values are ``'`` and `````.

    doubleQuote
        Specifies how to handle quote characters that fall within a quoted string. If set to ``true``, two consecutive ``quoteChar`` characters will be treated as one. Defaults to ``true``.

    escapeChar
        Specifies a single character to be used for escaping the delimiter character within an unquoted field or a quote within a quoted field. Defaults to ``null`` as it is mutually exclusive to ``doubleQuote``.

    quoteStyle
        Specifies the types of fields that should be enclosed with ``quoteChar``. Value must be one of the following class constants. Defaults to Flavor::QUOTE_MINIMAL.

            QUOTE_NONE
                No fields should be quoted, regardless of data type or contents.

            QUOTE_MINIMAL
                Only fields containing ``quoteChar``, ``lineTerminator`` or ``delimiter`` should be quoted.

            QUOTE_NONNUMERIC
                Only fields containing non-numeric data should be quoted.

            QUOTE_ALL
                All fields should be quoted, regardless of data type or contents.

Defining a flavor at runtime
============================

.. code-block:: php

    <?php
    // instantiate a new flavor object, defining its attributes on-the-fly
    $flavor = new Flavor([
        'delimiter' => ",",
        'quoteChar' => '"',
        'doubleQuote' => true,
        'quoteStyle' => Flavor::QUOTE_MINIMAL,
        'lineTerminator' => "\n",
    ]);

.. tip::

    To avoid any possibility of producing CSV data written half with commas and half with tabs (or other such nonsense), the ``CSVelte\Flavor`` class's attributes are immutable. Once it's been instantiated, its attributes cannot be altered. If you find yourself needing to alter a flavor object, just make a `copy <http://phpcsv.com/csvelte/apidocs/class-CSVelte.Flavor.html#_copy>`_ of it instead, specifying which attributes you'd like changed in the copy.

    .. code-block:: php

        <?php
        $flavor = new Flavor([
            'delimiter' => ",",
            'quoteChar' =>'"',
            'doubleQuote' => true,
            'lineTerminator' => "\r\n"
        ]);
        // cannot do this!! CSVelte will throw an exception
        $flavor->quoteStyle = Flavor::QUOTE_NONNUMERIC;

        // do this instead...
        $newflavor = $flavor->copy([
            'quoteStyle' => Flavor::QUOTE_NONNUMERIC
        ]);

Common Flavors
==============

Although the range of CSV flavors out *in the wild* is virtually limitless, there are definitely certain combinations of these attributes that are most common. The first of them I'll mention, and the only one with an RFC ( :rfc:`4180` ), is the flavor that Microsoft Excel uses when exporting spreadsheets as CSV data. This is the flavor you'll get when you instantiate a :php:class:`Flavor` object with no arguments. In addition to the default :php:class:`Flavor` class, CSVelte provides four concrete classes representing common flavors of CSV.

    :php:class:`Flavor\\Excel`
        This is just basically an alias for :php:class:`Flavor`. It's included simply for clarity and consistency.

    :php:class:`Flavor\\ExcelTab`
        Exactly the same as ``Excel``, except with tabs rather than commas as the delimiter.

    :php:class:`Flavor\\Unix`
        A common flavor of CSV used by non-Microsoft software. Uses Unix-style line endings (LF), uses backslash as the ``escapeChar``, and quotes all non-numeric fields.

    :php:class:`Flavor\\UnixTab`
        Exactly the same as ``Unix``, except with tabs rather than commas as the delimiter.

These class work exactly the same way that :php:class:`Flavor` does, except that they are preset to a different set of attributes. And just as you can override attributes using the default flavor class, so you can with these.

.. code-block:: php

    <?php
    $excelPipe = new Flavor\Excel([
        'delimiter' => '|'
    ]);
    $excelPipeQuoteAll = $excelPipe->copy([
        'quoteStyle' => Flavor::QUOTE_ALL
    ]);

Defining your own common flavors
================================

If there is a particular flavor of CSV you find yourself using all the time, try extending :php:class:`Flavor`. Any attributes you don't override in your class will remain their default value.

.. code-block:: php

    <?php
    // my custom flavor uses semi-colons rather than commas to delimit fields
    // it also uses old mac-style line endings, doubles up quote characters to escape them,
    // and quotes all fields no matter what!
    class MyCustomFlavor extends Flavor
    {
        public $delimiter = ';';
        public $lineTerminator = "\r";
        public $escapeChar = null;
        public $doubleQuote = true;
        public $quoteStyle = self::QUOTE_ALL;
    }

But what do I do with it?
=========================

As I've explained, the :php:class:`Flavor` class allows you to define a particular set of formatting attributes for CSV. But what then? Knowing a particular set of formatting attributes for CSV does you no good without some data to apply it to. This is where the reader and writer classes come in. And I promise, we will get to them very soon.
