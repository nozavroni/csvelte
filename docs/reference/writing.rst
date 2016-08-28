################
Writing CSV Data
################

PHP provides its own function, ``fputcsv`` for writing CSV data to a file (or to any valid stream resource). It works alright, but has a number of shortcomings and peculiarities (see `its doc page on PHP.net <http://php.net/manual/en/function.fputcsv.php>`_). CSVelte does not use this function (nor does it use ``fgetcsv`` or ``str_getcsv`` for reading CSV data). This allows for much greater flexibility and control when writing CSV data.

The Writer class
================

The ``CSVelte\Writer`` class is the main workhorse for writing CSV data to any number of output sources. The interface for the writer is very similar to the interface for ``CSVelte\Reader``. You simply instantiate any class that implements the ``CSVelte\Contracts\Writable`` interface and use that to instantiate a writer object.

Let's assume we want to write a CSV file called ``./data/products.csv``. We would need to instantiate a ``CSVelte\Output\File`` object pointing to that file. Let's see how that looks:

..  code-block:: php
    :emphasize-lines: 2

    <?php
    $out = new CSVelte\Output\File('./data/products.csv');
    $writer = new CSVelte\Writer($out);

Setting the flavor
==================

If you want to use a specific flavor of CSV (rather than the default :rfc:`4180`), you can do so by passing a ``CSVelte\Flavor`` object as the second parameter to the writer's constructor and the writer will write CSV according to your specified flavor. See :doc:`/reference/flavors` for more on flavors and formatting.

..  code-block:: php

    <?php
    $out = new CSVelte\Output\File('./data/products.csv');
    $flavor = new CSVelte\Flavor(['delimiter' => "\t"]);
    $writer = new CSVelte\Writer($out, $flavor);

Writing a single row
====================

Once you've instantiated a ``CSVelte\Writer`` object, you can use the ``writeRow`` method to write CSV line-by-line. You simply pass it an array or anything `traversable <http://php.net/manual/en/class.traversable.php>`_ and it will output a field for each element in the array/iterator.

..  code-block:: php
    :emphasize-lines: 2

    <?php
    $out = new CSVelte\Output\File('./data/products.csv');
    $writer = new CSVelte\Writer($out);
    $writer->writeRow(array('one', 2, 'three', 'fore'));
    $writer->writeRow(new ArrayIterator(array('five', 'sicks', '7 "ate" 9', 10)));

Depending on the ``CSVelte\Flavor`` object you use, this should output something along the lines of:

..  code-block: csv

    one,2,three,fore
    five,sicks,"7 ""ate"" 9",10
