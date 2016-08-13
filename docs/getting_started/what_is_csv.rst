############
What is CSV?
############

It's highly unlikely that you would even be here reading about this library if you weren't already aware of what CSV is. But what defines :abbr:`CSV (Comma Separated Values)` as a format? Who invented it? What body governs its standardization? Where can one find an RFC specifying the format down to the most mundane detail? The short answer, to all of these questions, is nothing/nobody/there really isn't one.*

    *There actually is an RFC (:rfc:`4180`) that defines a MIME type for CSV, but it is by no means *the* CSV standard.

CSV as a format
===============

Although CSV is an extremely widely-used format for importing/exporting data, its lack of a true standard means CSV data out in the wild can vary substantially in its style and format. Exacerbating the problem, CSV (in all its forms) lacks a standardized method for dictating metadata such as column type, character encoding, locale information such as language and date/time/currency formatting, etc. One can't even rely on a comma being the delimiter character within a CSV file and the name of the format is Comma Separated Values! This can make life very difficult for developers attempting to reliably output and/or consume CSV-formatted data.

How does CSVelte address these inconsistencies?
===============================================

Flavors of CSV
--------------

Taking cues from `Python <https://www.python.org>_`'s `CSV module <https://docs.python.org/2/library/csv.html>_`, `Frictionless Data <http://specs.frictionlessdata.io/>_`'s `CSV Dialect Description Format <http://specs.frictionlessdata.io/csv-dialect/>_`, as well as the `W3C <https://www.w3.org/>_`'s `CSV on the Web Working Group <https://www.w3.org/2013/csvw/wiki/Main_Page>_`, CSVelte allows developers to define distinct :ref:`flavors` of CSV so that consumers of CSV can rely on publishers using their specific :ref:`flavor`. Python has a similar concept they call "`dialects <https://docs.python.org/2/library/csv.html#dialects-and-formatting-parameters>_`". To define a flavor in CSV, you simply instantiate a ``CSVelte\Flavor`` object and specify its attributes.

.. code-block:: php

    <?php
    $flavor = new CSVelte\Flavor([
        'delimiter' => ",",
        'quoteChar' => '"',
        'doubleQuote' => true,
        'quoteStyle' => Flavor::QUOTE_MINIMAL,
        'lineTerminator' => "\n",
    ]);

Flavor auto-detection
---------------------

If you know in advance what :ref:`flavor` of CSV you're working with, the ``CSVelte\Flavor`` class is going to work great for you. But what if you don't? Does CSV have some way of telling the developer what :ref:`flavor` of CSV it's written in? Unfortunately, no. It doesn't. But CSVelte does. Any time you read CSV data, whether it be from a local file, a string, or otherwise, CSVelte will attempt to determine the :ref:`flavor` automatically. The upshot being that in the majority of cases, you can feed CSV data to CSVelte and it will just work. For more on flavors and auto-detection, see the ``reference documentation``.
