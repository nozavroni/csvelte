################
Reading CSV Data
################

Due to PHP's array-centric programming philosophy and its unprecedented community of "copy-and-paste programmers", the vast majority of people searching Google for a "CSV parser" are really just looking for a quick and dirty script that will accept the name of a CSV file and produce a two-dimensional array containing that file's data. If that sounds like you, head over to the :ref:`/tutorials/index` section where you'll find :ref:`just such a script </tutorials/convert_file_to_array>`. This guide will be way more information than you will ever want/need.

If however, you're looking for a way to efficiently read CSV data from any source using a consistent, object-oriented interface, you've come to the right place.

The ``CSVelte\Reader`` class
============================

I believe in most cases, CSV data will be read from a file on the local file system. But there are certainly cases where one might need to read CSV directly from a PHP string, or from a stream. The data can come from virtually anywhere. It is for this reason that CSVelte makes available a :php:interface:`CSVelte\\Contracts\\Readable` interface. Any class that implements ``Readable`` can be read by ``CSVelte\Reader``. Simply pass your ``Readable`` object to its constructor as its first argument.

..  code-block:: php

    <?php
    // local file "readable" object
    $in = new CSVelte\Input\File('./data/customers.csv');
    $reader = new CSVelte\Reader($in);

As I mentioned in ":doc:`tasting`", the reader object will always *attempt* to determine the CSV flavor, but the auto-detection feature, while very often correct, is only an educated guess, at best. If you know ahead of time what flavor CSV you are working with, it's **always** recommended that you explicitly pass a flavor object as the second argument to ``CSVelte\Reader`` so that it doesn't have to guess.

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
    foreach ($reader as $row) {
        // now you can do something with $row...
    }

..  warning::

    If you're unsure about the size and/or number of rows in the CSV file you're working with, you'll want to avoid doing things like unpacking it into a PHP array. This will result in the entire file being loaded into memory rather than just one row at a time. In cases where you know your CSV data is only a few hundred rows, this shouldn't be an issue, but it isn't at all uncommon for CSV files to be several orders of magnitude larger.
