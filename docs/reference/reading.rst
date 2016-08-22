################
Reading CSV Data
################

Due to PHP's array-centric programming philosophy and its unprecedented community of "copy-and-paste programmers", the vast majority of people searching Google for a "CSV parser" are really just looking for a quick and dirty script that will accept the name of a CSV file and produce a two-dimensional array containing that file's data. If that sounds like you, head over to the :ref:`/tutorials/index` section where you'll find :ref:`just such a script </tutorials/convert_file_to_array>`. This guide will be way more information than you will ever want/need.

If however, you're looking for a way to efficiently read CSV data from any source using a consistent, object-oriented interface, please read on.

The Reader class
================

CSVelte's reader class, ``CSVelte\Reader``, was designed with extensibility and loose coupling in mind. Rather than write an abstract CSV reader class and extend it to create readers for local files, PHP strings, streams, etc., I have instead opted for `composition over inheritance`_.

To instantiate a ``CSVelte\Reader`` object, you must first instantiate an implementation of the ``CSVelte\Contracts\Readable`` interface such as ``CSVelte\Input\File``. You may then use this object to instantiate the reader.

..  code-block:: php

    <?php
    // local file implementation of ``CSVelte\Contracts\Readable``
    $in = new CSVelte\Input\File('./data/customers.csv');

    $reader = new CSVelte\Reader($in);

The Readable interface
======================

CSVelte comes with several ``Readable`` classes out of the box, allowing you to read CSV data from all the most common sources. Let's take a look at them.

    ``CSVelte\Input\File``
        For reading a CSV file from the local filesystem. Constructor accepts a local file name as its only argument.

    ``CSVelte\Input\String``
        For reading CSV data directly from a PHP string. Constructor accepts a PHP variable containing CSV data as its only argument.

    ``CSVelte\Input\Stream``
        For reading CSV data from any valid PHP stream resource. Constructor accepts either a fully-qualified stream URI (such as ``php://stdin``) or a valid stream resource as its only argument. (see `PHP streams`_)

..  hint::

    Try creating your own ``Readable`` class! If you need to read CSV data in some custom way or from a source not listed above, writing your own readable is as simple as implementing the ``CSVelte\Contracts\Readable`` interface. For a list of its methods and to see how the built-in readables are written, check out the `API docs <http://phpcsv.com/apidocs/class-CSVelte.Contract.Readable.html>`_.

Dictating CSV Flavor
====================

Up to this point, when instantiating the reader, we've left it up to CSVelte to determine what :doc:`flavor </reference/flavors>` of CSV we're working with (see :doc:`/reference/tasting`), but if you want to specify a flavor explicitly, you can pass one as the second argument when instantiating your reader object.

..  code-block:: php

    <?php
    $in = new CSVelte\Input\File("./data/purchases.csv");
    // explicitly pass a flavor object to the reader to disable auto-detection
    $reader = new CSVelte\Reader($in, new Flavor([
        'delimiter' => '|',
        'lineTerminator' => "\n",
        'quoteStyle' => self::QUOTE_ALL,
        'escapeChar' => '//',
        'doubleQuote' => false,
        'header' => true
    ]));

Iterating using foreach
=======================

``CSVelte\Reader`` implements PHP's built-in ``Iterator`` interface, allowing the use of foreach to loop over each row in the dataset.

..  code-block:: php

    <?php
    $reader = new CSVelte\Reader(new CSVelte\Input\File('./data/products.csv'));
    foreach ($reader as $line_no => $row) {
        // do stuff with the row
    }

Working with rows
-----------------

Just as you can iterate over the CSVelte\

..  _composition over inheritance: https://en.wikipedia.org/wiki/Composition_over_inheritance

..  _PHP streams: http://php.net/manual/en/book.stream.php
