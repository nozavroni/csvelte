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

Once you've instantiated a ``CSVelte\Writer`` object, you can use its ``writeRow`` method to write CSV line-by-line. You simply pass it an array or anything `traversable <http://php.net/manual/en/class.traversable.php>`_ and it will output a field for each element in the array/iterator.

..  code-block:: php

    <?php
    $out = new CSVelte\Output\File('./data/products.csv');
    $writer = new CSVelte\Writer($out);
    // you can pass an array...
    $writer->writeRow(array('one', 2, 'three', 'fore'));
    // or anything that is iterable with foreach...
    $writer->writeRow(new ArrayIterator(array('five', 'sicks', '7 "ate" 9', 10)));

Depending on the ``CSVelte\Flavor`` object you use, this should output something along the lines of:

..  code-block:: csv

    one,2,three,fore
    five,sicks,"7 ""ate"" 9",10

Writing multiple rows
=====================

If you have an two-dimensional array or an iterable of iterables, you can pass it to the ``writeRows`` method to write multiple rows at once.

..  code-block:: php

    <?php
    $out = new CSVelte\Output\File('./data/albums.csv');
    $writer = new CSVelte\Writer($out);
    $data = array(
        array('Lateralus', 'Tool', 2001, 'Volcano Entertainment'),
        array('Wish You Were Here', 'Pink Floyd', 1975, 'Columbia'),
        array('The Fragile', 'Nine Inch Nails', 1999, 'Interscope'),
    );
    $writer->writeRows($data);

Depending on your ``CSVelte\Flavor`` attributes, this should output something along the lines of:

..  code-block:: csv

    Lateralus,Tool,2001,Volcano Entertainment
    Wish You Were Here,Pink Floyd,1975,Columbia
    The Fragile,Nine Inch Nails,1999,Interscope

Setting the header row
======================

CSV files allow an optional header row to designate labels for each column within the data. If present, it should always be the first row in the data. You can go about writing your header row one of two ways. There's the dumb way, which is to simply make sure the first row you write is your header row.

..  code-block:: php
    :emphasize-lines: 5

    <?php
    $out = new CSVelte\Output\File('./data/albums.csv');
    $writer = new CSVelte\Writer($out);
    $data = array(
        array('Album', 'Artist', 'Year', 'Label'),
        array('Lateralus', 'Tool', 2001, 'Volcano Entertainment'),
        array('Wish You Were Here', 'Pink Floyd', 1975, 'Columbia'),
        array('The Fragile', 'Nine Inch Nails', 1999, 'Interscope'),
    );
    $writer->writeRows($data);

As you can see in the highlighted line above, I simply made the first row the header row. There is nothing particularly wrong with this approach. It works well enough. But if you'd like to be more explicit, you can do that with ``CSVelte\Writer::setHeaderRow()``.

..  code-block:: php
    :emphasize-lines: 9

    <?php
    $out = new CSVelte\Output\File('./data/albums.csv');
    $writer = new CSVelte\Writer($out);
    $data = array(
        array('Lateralus', 'Tool', 2001, 'Volcano Entertainment'),
        array('Wish You Were Here', 'Pink Floyd', 1975, 'Columbia'),
        array('The Fragile', 'Nine Inch Nails', 1999, 'Interscope'),
    );
    $writer->setHeaderRow(array('Album', 'Artist', 'Year', 'Label'));
    $writer->writeRows($data);

This does the exact same thing as the first approach did, only it's more explicit and more clear to programmers who come along later, what's going on here.

..  warning::

    You must be careful not to call ``setHeaderRow()`` after data has already been written to the output source. That is to say, after any calls to ``writeRow()`` or ``writeRows()``. This will trigger an exception. In the future, I intend to implement a write buffer that will allow you to call ``setHeaderRow()`` almost any time you like, but until then, you must call ``setHeaderRow()`` before any write methods.  

Using reader and writer together
================================

The reader and writer classes are very useful by themselves, but when you combine them, you can really start to see the power and usability of CSVelte. Let's take a look at a few ways you can use ``CSVelte\Reader`` and ``CSVelte\Writer`` together to accomplish common tasks.

Reformatting by changing flavor
-------------------------------

As I mentioned before, ``CSVelte\Writer::writeRows()`` accepts either an array of arrays or an iterable of iterables (or a combination thereof). Instances of the ``CSVelte\Reader`` class, by design, fall within this category. This means that you can instantiate a reader object and pass it to ``CSVelte\Writer::writeRows()`` as a means to either filter out certain rows, change its flavor (formatting), or both. Let's take a look at a few examples.

..  code-block:: php

    <?php
    // create our reader object, allowing it to automatically determine CSV flavor
    $in = new CSVelte\Input\File("./data/albums.csv");
    $reader = new CSVelte\Reader($in);

    // now create a writer object, passing it an explicit flavor we want to reformat to
    $out = new CSVelte\Output\File("./data/albums.tsv");
    $writer = new CSVelte\Writer($out, new CSVelte\Flavor\ExcelTab());

    // now you can simply pass the reader object to writeRows to get a tab-delimited file
    $writer->writeRows($reader);

Filtering out unwanted rows
---------------------------

As demonstrated in :doc:`/reference/reading`, you can use the ``CSVelte\Reader::addFilter`` method to attach any number of anonymous functions to your reader to filter out unwanted rows. You can then iterate your filtered reader using the ``CSVelte\Reader::filter()`` method. Again, because ``CSVelte\Writer::writeRows()`` can accept any iterable, you can pass the result of the ``filter`` method to ``writeRows`` to write a new CSV file, less your filtered rows.

..  code-block:: php
    :emphasize-lines: 14

    <?php
    // create our reader object
    $reader = new CSVelte\Reader(new CSVelte\Input\File("./data/albums.csv"));
    // this will filter out all but '90s albums
    $reader->addFilter(function($row) {
        return ($row['Year'] >= 1990 && $row['Year'] < 2000);
    });

    // now create a writer object, pointing to a new "90s-albums.csv" file
    $writer = new CSVelte\Writer(new CSVelte\Output\File("./data/90s-albums.csv"));

    // now you can simply pass the reader object to writeRows to CSV file with
    // only 90s albums from the original CSV file
    $writer->writeRows($reader->filter());
