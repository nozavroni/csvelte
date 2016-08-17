############################
The various "flavors" of CSV
############################

How does CSVelte address the extremely loose nature of CSV as a format? It allows the developer to define "flavors" of CSV, as well as providing several :api:`common` flavors out of the box. Let's see how they work.

Flavors of CSV
==============

Taking cues from `Python's CSV module <https://docs.python.org/2/library/csv.html>`_, `Frictionless Data's CSV Dialect Description Format <http://specs.frictionlessdata.io/csv-dialect/>`_, as well as the `W3C's <https://www.w3.org/>`_ `CSV on the Web Working Group <https://www.w3.org/2013/csvw/wiki/Main_Page>`_, CSVelte allows developers to define distinct :ref:`flavors </reference/flavors>` of CSV so that consumers can rely on publishers using a specific :ref:`flavor </reference/flavors>`. Python has a similar concept they call "`dialects <https://docs.python.org/2/library/csv.html#dialects-and-formatting-parameters>`_". To define a flavor in CSV, you simply instantiate a ``CSVelte\Flavor`` object and specify its attributes.

.. code-block:: php

    <?php
    $flavor = new CSVelte\Flavor([
        'delimiter' => ",",
        'quoteChar' => '"',
        'doubleQuote' => true,
        'quoteStyle' => Flavor::QUOTE_MINIMAL,
        'lineTerminator' => "\n",
    ]);

.. note::

    To avoid any possibility of producing CSV data written half with commas and half with tabs (or other such nonsense), the ``CSVelte\Flavor`` class's attributes are immutable. Once it's been instantiated, its attributes cannot be altered. If you find yourself needing to alter a flavor object, just make a `copy <http://phpcsv.com/apidocs/class-CSVelte.Flavor.html#_copy>`_ of it instead, specifying which attributes you'd like changed in the copy.

    .. code-block:: php

        <?php
        $flavor = new CSVelte\Flavor([
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

Flavor Attributes
=================

    header
        Specifies whether to treat the first row of the dataset as a header row. If ``true``, the first row will be ignored by the ``CSVelte\Reader`` class when iterating over a dataset. Defaults to ``null``
        
    delimiter
        Specifies a single character to be used as the field separator. Defaults to ``,``. Other common values are ``\t``, and ``|``.

    lineTerminator
        Specifies a character or sequence of characters used to terminate each row. Defaults to ``\r\n``. Other common values are ``\n`` and ``\r``.

    quoteChar
        Specifies a single character to be used for quoting fields. Defaults to ``"``. Other common values are ``'`` and `````.

    doubleQuote
        Specifies how to handle quote characters that fall within a quoted string. If set to ``true``, two consecutive ``quoteChar`` characters will be treated as one. Defaults to ``true``.

    escapeChar
        Specifies a single character to be used for escaping. Defaults to ``null`` as it is mutually exclusive to ``doubleQuote``.

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

Common Flavors
==============

Although the range of CSV flavors out *in the wild* is virtually limitless, there are definitely certain combinations of these attributes that are most common. The first of them I'll mention, and the only one with an RFC ( :rfc:`4180` ), is the flavor that Microsoft Excel uses when exporting spreadsheets as CSV data. This is the flavor you'll get when you instantiate a ``CSVelte\Flavor`` object with no arguments. In addition to the default ``CSVelte\Flavor`` class, CSVelte provides four concrete classes representing common flavors of CSV.

    ``CSVelte\Flavor\Excel``
        This is just basically an alias for ``CSVelte\Flavor``. It's included simply for clarity and consistency.

    ``CSVelte\Flavor\ExcelTab``
        Exactly the same as ``Excel``, except with tabs rather than commas as the delimiter.

    ``CSVelte\Flavor\Unix``
        A common flavor of CSV used by non-Microsoft software. Uses Unix-style line endings (carriage returns), uses backslash as the ``escapeChar``, and quotes all non-numeric fields.

    ``CSVelte\Flavor\UnixTab``
        Exactly the same as ``Unix``, except with tabs rather than commas as the delimiter.

These class work exactly the same way that ``CSVelte\Flavor`` does, except that they are preset to a different set of attributes. And just as you can override attributes using the default flavor class, so you can with these.

.. code-block:: php

    <?php
    $excelPipe = new CSVelte\Flavor\Excel([
        'delimiter' => '|'
    ]);
    $excelPipeQuoteAll = $excelPipe->copy([
        'quoteStyle' => Flavor::QUOTE_ALL
    ]);
```
