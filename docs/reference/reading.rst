################
Reading CSV Data
################

Due to PHP's array-centric programming philosophy and its unprecedented community of "copy-and-paste programmers", the vast majority of people searching Google for a "CSV parser" are really just looking for a quick and dirty script that will accept the name of a CSV file and produce a two-dimensional array containing that file's data. If that sounds like you, head over to the :ref:`/tutorials/index` section where you'll find :ref:`just such a script </tutorials/convert_file_to_array>`. This guide will be way more information than you will ever want/need.

If however, you're looking for a way to efficiently read CSV data from any source using a consistent, object-oriented interface, you've come to the right place.

Reader and Readables
====================

To read CSV data, whether it be from a local file, a stream, a PHP string or some other source, you'll use a combination of ``CSVelte\Reader`` and any class that implements the ``CSVelte\Contracts\Readable`` interface. Let's take a look at what it might look like to implement a reader object using a ``CSVelte\Input\File`` (local file) object.

..  code-block:: php

    <?php
    // create a local file "readable"
    $csvfile = new CSVelte\Input\File('./data/orders.csv');

    // now pass that "readable" to the "reader"
    $reader = new CSVelte($csvfile);

Working with a reader object
============================

The ``CSVelte\Reader`` class implements PHP's :php:interface:`Iterator` which means that iterating over each row in your CSV data is as simple as a ``foreach`` loop.
