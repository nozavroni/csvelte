################
Reading CSV Data
################

.. todo::

    * Create an issue in Github for new filter features
        * Remove filters (pop/shift)
        * Possibly allow labels for filters so you can reference them by name for deletion/overwriting
        * Clear all filters
    * Need a glossary so that using terms such as "flavor" and "taster" doesn't feel so awkward
    * Write a Sphinx extension that allows me to do something like :apidoc-class:`CSVelte\Reader` and :apidoc-namespace:`CSVelte`
    * Write a Sphinx extension that allows me to automate API docs within Sphinx (preferrably find one already written)
        * Once this happens, you will need to change the :apidoc: references to point to new location
    * Figure out how to do URI short-references so that instead of always doing `Some PHP page <http://php.net/manual/en/some.php.page.php>`_ I can simply do :php:`Some PHP page`
    * Do the same thing as above only with Wikipedia entries or any other site I want
    * Go back and get rid of all ``some/file.csv`` references. I was using it incorrectly. That syntax should only be used when the filename has actual semantic value for the library - see issue #95

Instantiating a :php:class:`Reader` object
==========================================

Before we can instantiate a :php:class:`Reader` object, we must first instantiate a readable :php:class:`IO\\Stream` object. Let's assume we want to read a local CSV file located at ``/var/www/inventory.csv``, and formatted according to the default flavor.

.. code-block:: php

    // first instantiate a readable stream object...
    $stream = new IO\Stream("/var/www/inventory.csv");
    // then pass it to the reader
    $reader = new Reader($stream);

We could also have used CSVelte's reader factory method to do the same thing.

.. code-block:: php

    $reader = CSVelte::reader("/var/www/inventory.csv");

.. tip::

    Any class that implements the :php:interface:`Contract\\Readable` interface can be read by the :php:class:`Reader` object. This means you can write your own custom "readable" class if you're so inclined. You aren't in any way restricted to just the stream class provided by CSVelte.

Specifying CSV Flavor
=====================

If you know in advance what flavor of CSV you're working with, you can pass a :php:class:`Flavor` object, or an associative array of flavor attributes as the second parameter to the reader's constructor [#]_.

.. code-block:: php

    // create readable stream
    $in = new IO\Stream("./data/purchases.csv");

    // explicitly pass a flavor object to the reader
    $reader = new Reader($in, new Flavor([
        'delimiter' => '|',
        'lineTerminator' => "\n",
        'quoteStyle' => self::QUOTE_ALL,
        'escapeChar' => '//',
        'doubleQuote' => false,
        'header' => true
    ]));

    // or...

    // pass an associative array of flavor attributes
    // the reader will convert it to a flavor object internally
    $reader = new Reader($in, [
        'delimiter' => '|',
        'lineTerminator' => "\n",
        'quoteStyle' => self::QUOTE_ALL,
        'escapeChar' => '//',
        'doubleQuote' => false,
        'header' => true
    ]);

Taking the Pepsi challenge
--------------------------

Omitting the flavor parameter when instantiating a reader object tells CSVelte you want it to automatically detect the CSV flavor. It will use the :php:class:`Taster` class to analyze a sample of your CSV dataset and provide its best guess as to what its flavor is. This applies whether you instantiate the reader manually or you use the factory method. All this happens behind the scenes and is completely transparent unless something goes wrong, in which case you can expect an :php:exc:`Exception\\TasterException`.

Iterating using foreach
=======================

:php:class:`Reader` implements PHP's built-in :php:interface:`Iterator` interface [#]_, allowing the use of foreach to loop over each row in the dataset. At each iteration, the key will refer to the current line number, while the value will contain a :php:class:`Table\\Row` object.

.. code-block:: php

    <?php
    foreach (CSVelte::reader('./data/inventory.csv') as $line_no => $row) {
        do_something_with($row, $line_no);
    }

Filtering/skipping certain rows
-------------------------------

Although you could loop over every row in a CSV file, and place if/elseif/else branches directly inside the body of your foreach loop, like the following:

.. code-block:: php

    <?php
    $reader = new Reader(new IO\Stream('./data/products.csv'));
    foreach ($reader as $line_no => $row) {
        if (isset($row[2]) && strlen($row[2]) > 10) {
            continue;
        }
        if (isset($row[5]) && (int) $row[5] <= 1000) {
            continue;
        }
        if (empty($row[8])) {
            continue;
        } elseif (isset($row[8]) && $row[8] == 'false') {
            continue;
        }
        // now we can do something with $row
        do_something_with($row);
    }

This approach feels cluttered. A much cleaner, and clearer way to do this would be to filter out these rows using anonymous functions as filters. The reader object can accept any number of ``Callables`` [#]_ to filter out these rows instead. Let's see how this might look.

.. code-block:: php

    <?php
    $reader = CSVelte::reader('./data/products.csv');
    $reader->addFilter(function($row) {
        return ($row[2] < 10);
    })->addFilter(function($row) {
        return ($row[5] > 1000);
    })->addFilter(function($row) {
        return (!empty($row[8]) && $row[8] != 'false');
    });
    // now we can simply loop over our filtered reader and our unwanted rows
    // will be filtered out for us automatically
    foreach ($reader->filter() as $line_no => $row) {
        do_something_with($row);
    }

.. warning::

    As I've mentioned several times, CSVelte is still in its infancy. Its API and many other things about it are not yet stable. Several features can't even be called complete yet. Reader filtering is one such incomplete feature. There is currently no way to remove or alter filters once they've been added to the reader. If you need to change the filters you've added to the reader in any way, you will need to completely reinstantiate the reader from scratch. In the future there will be ways to remove filters after they've been added. In fact the reader filter feature(s) will likely change quite a bit before CSVelte reaches stability at version 1.0 so use them (and CSVelte in general) at your own risk until then.

Working with rows
=================

When looping through CSV data using ``Reader`` and ``foreach``, you will have access to a ``Table\Row`` object at each iteration. You can use this object to access the row's fields in various ways as well as to loop through its fields using ``foreach`` just as you did with the reader object.

.. code-block:: php

    <?php
    $reader = new Reader(new IO\Stream('./data/products.csv'));
    foreach ($reader as $line_no => $row) {
        foreach ($row as $col_no => $field) {
            // now do something with $field
        }
    }

Row indexing
------------

By default, rows will be indexed numerically, starting at zero. This means that in order to work with a particular column's value within a row, you will need to know what its numeric index will be. Let's assume we're working with the following data:

.. csv-table:: ./data/great-albums.csv
   :header: 0, 1, 2, 3

    "Lateralus", "Tool", 2001, "Volcano Entertainment"
    "Wish You Were Here", "Pink Floyd", 1975, "Columbia"
    "The Fragile", "Nine Inch Nails", 1999, "Interscope"
    "Mezzanine", "Massive Attack", 1998, "Virgin"
    "Panopticon", "ISIS", 2004, "Ipecac"

The table above will represent our CSV data. The first row represents the index number for each column. So, let's take a look at how we might interact with such a dataset using ``Reader`` and ``Table\Row``.

.. code-block:: php

    <?php
    $reader = new Reader(new IO\Stream('./data/great-albums.csv'));
    foreach ($reader as $line_no => $row) {
        // for the first row, this will print:
        // "One of my favorite albums is Lateralus by Tool."
        printf("One of my favorite albums is %s by %s.\n", $row[0], $row[1]);
    }

Indexing with the column headers
--------------------------------

If your CSV data contains a header row, you can use column header values as your row indexes (rather than the numerical indexing shown above). Let's use the same dataset from before, only this time we'll add a header row.

.. csv-table:: ./data/great-albums.csv
   :header: "Album", "Artist", "Release Year", "Label"

    "Lateralus", "Tool", 2001, "Volcano Entertainment"
    "Wish You Were Here", "Pink Floyd", 1975, "Columbia"
    "The Fragile", "Nine Inch Nails", 1999, "Interscope"
    "Mezzanine", "Massive Attack", 1998, "Virgin"
    "Panopticon", "ISIS", 2004, "Ipecac"

In order to be able to use column header values rather than numeric indexes, you must first ensure that your ``Flavor`` object has its header attribute set to true. This will tell the reader that the first row in the dataset should be considered the header row, rather than treated as data.

.. code-block:: php
   :emphasize-lines: 3,8,9

    <?php
    $flavor = new Flavor([
        'header' => true
    ]);
    $reader = new Reader(new IO\Stream('./data/great-albums.csv'), $flavor);
    foreach ($reader as $line_no => $row) {
        // now we can use column headers rather than numeric indexes
        $album = $row['Album'];
        $artist = $row['Artist'];
        // or, if you like, you can still use the numerical indexes as well
        $year = $row[2];
        $label = $row[3];
    }

.. attention::

    You must remember to use the exact spelling and capitalization that the header row uses. "Album" is not the same as "album". If you use the latter, it will trigger an exception. You don't want that. In the future, I will likely relax this to allow any capitalization but for now, you must remember to use the header value exactly as it appears in the data.

.. todo::

    The callback used for Reader::addFilter() should accept an instance of the reader itself, the current row, and the current key (line number) instead of just the row.

.. _composition over inheritance: https://en.wikipedia.org/wiki/Composition_over_inheritance

.. _PHP streams: http://php.net/manual/en/book.stream.php

.. rubric:: Footnotes

.. [#] Any function or method that accepts a :php:class:`Flavor` object will also accept an associative array of flavor attributes. The two are often interchangeable.
.. [#] see Iterator interface at http://php.net/manual/en/class.iterator.php
.. [#] see Callable type-hinting at http://php.net/manual/en/language.types.callable.php
