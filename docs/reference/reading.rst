################
Reading CSV Data
################

..  todo::

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

Due to PHP's array-centric programming philosophy and its unprecedented community of "copy-and-paste programmers", the vast majority of people searching Google for a "CSV parser" are really just looking for a quick and dirty script that will accept the name of a CSV file and produce a two-dimensional array containing that file's data. If that sounds like you, head over to the :ref:`/tutorials/index` section where you'll find :ref:`just such a script </tutorials/convert_file_to_array>`. This guide will be way more information than you will ever want/need.

If however, you're looking for a way to efficiently read CSV data from any source using a consistent, object-oriented interface, please read on.

The Reader class
================

CSVelte's reader class, ``CSVelte\Reader``, was designed with extensibility and loose coupling in mind. Rather than write an abstract CSV reader class and extend it to create readers for local files, PHP strings, streams, etc., I have instead opted for `composition over inheritance`_.

To instantiate a ``CSVelte\Reader`` object, you must first instantiate an implementation of the ``CSVelte\Contracts\Readable`` interface such as ``CSVelte\Input\File``. You may then use this object to instantiate the reader.

..  code-block::

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

``CSVelte\Reader`` implements PHP's built-in ``Iterator`` interface (`see "Iterator" interface on PHP.net <http://php.net/manual/en/class.iterator.php>`_), allowing the use of foreach to loop over each row in the dataset. At each iteration, you will get a key and a value (which are assigned in `the usual foreach fashion <http://php.net/manual/en/control-structures.foreach.php>`_). The key will refer to the current line number, while the value will contain a ``CSVelte\Table\Row`` object.

..  code-block:: php

    <?php
    $reader = new CSVelte\Reader(new CSVelte\Input\File('./data/products.csv'));
    foreach ($reader as $line_no => $row) {
        // do stuff with the row
    }

This is how the majority of CSV reading with CSVelte will be done. You simply instantiate a reader, and loop over that reader object using foreach. We will get into what you can do with the ``CSVelte\Table\Row`` object a little later on.

Filtering/skipping certain rows
-------------------------------

Although you could loop over every row in a CSV file, and place if/elseif/else branches directly inside the body of your foreach loop, like the following:

..  code-block:: php

    <?php
    $reader = new CSVelte\Reader(new CSVelte\Input\File('./data/products.csv'));
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

This approach feels cluttered. A much cleaner, and clearer way to do this would be to filter out these rows using anonymous functions as filters. The reader object can accept any number of ``Callables`` (`see "Callable" type-hinting on PHP.net <http://php.net/manual/en/language.types.callable.php>`_) to filter out these rows instead. Let's see how this might look.

..  code-block:: php

    <?php
    $reader = new CSVelte\Reader(new CSVelte\Input\File('./data/products.csv'));
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

..  warning::

    As I've mentioned several times, CSVelte is still in its infancy. Its API and many other things about it are not yet stable. Several features can't even be called complete yet. Reader filtering is one such incomplete feature. There is currently no way to remove or alter filters once they've been added to the reader. If you need to change the filters you've added to the reader in any way, you will need to completely reinstantiate the reader from scratch. In the future there will be ways to remove filters after they've been added. In fact the reader filter feature(s) will likely change quite a bit before CSVelte reaches stability at version 1.0 so use them (and CSVelte in general) at your own risk until then.

Working with rows
=================

When looping through CSV data using ``CSVelte\Reader`` and ``foreach``, you will have access to a ``CSVelte\Table\Row`` object at each iteration. You can use this object to access the row's fields in various ways as well as to loop through its fields using ``foreach`` just as you did with the reader object.

..  code-block:: php

    <?php
    $reader = new CSVelte\Reader(new CSVelte\Input\File('./data/products.csv'));
    foreach ($reader as $line_no => $row) {
        foreach ($row as $col_no => $field) {
            // now do something with $field
        }
    }

Row indexing
------------

By default, rows will be indexed numerically, starting at zero. This means that in order to work with a particular column's value within a row, you will need to know what its numeric index will be. Let's assume we're working with the following data:

..  csv-table:: ./data/great-albums.csv
    :header: 0, 1, 2, 3

    "Lateralus", "Tool", 2001, "Volcano Entertainment"
    "Wish You Were Here", "Pink Floyd", 1975, "Columbia"
    "The Fragile", "Nine Inch Nails", 1999, "Interscope"
    "Mezzanine", "Massive Attack", 1998, "Virgin"
    "Panopticon", "ISIS", 2004, "Ipecac"

The table above will represent our CSV data. The first row represents the index number for each column. So, let's take a look at how we might interact with such a dataset using ``CSVelte\Reader`` and ``CSVelte\Table\Row``.

..  code-block:: php

    <?php
    $reader = new CSVelte\Reader(new CSVelte\Input\File('./data/great-albums.csv'));
    foreach ($reader as $line_no => $row) {
        // for the first row, this will print:
        // "One of my favorite albums is Lateralus by Tool."
        printf("One of my favorite albums is %s by %s.\n", $row[0], $row[1]);
    }

Indexing with the column headers
--------------------------------

If your CSV data contains a header row, you can use column header values as your row indexes (rather than the numerical indexing shown above). Let's use the same dataset from before, only this time we'll add a header row.

..  csv-table:: ./data/great-albums.csv
    :header: "Album", "Artist", "Release Year", "Label"

    "Lateralus", "Tool", 2001, "Volcano Entertainment"
    "Wish You Were Here", "Pink Floyd", 1975, "Columbia"
    "The Fragile", "Nine Inch Nails", 1999, "Interscope"
    "Mezzanine", "Massive Attack", 1998, "Virgin"
    "Panopticon", "ISIS", 2004, "Ipecac"

In order to be able to use column header values rather than numeric indexes, you must first ensure that your ``CSVelte\Flavor`` object has its header attribute set to true. This will tell the reader that the first row in the dataset should be considered the header row, rather than treated as data.

..  code-block:: php
    :emphasize-lines: 3,8,9

    <?php
    $flavor = new CSVelte\Flavor([
        'header' => true
    ]);
    $reader = new CSVelte\Reader(new CSVelte\Input\File('./data/great-albums.csv'), $flavor);
    foreach ($reader as $line_no => $row) {
        // now we can use column headers rather than numeric indexes
        $album = $row['Album'];
        $artist = $row['Artist'];
        // or, if you like, you can still use the numerical indexes as well
        $year = $row[2];
        $label = $row[3];
    }

..  attention::

    You must remember to use the exact spelling and capitalization that the header row uses. "Album" is not the same as "album". If you use the latter, it will trigger an exception. You don't want that. In the future, I will likely relax this to allow any capitalization but for now, you must remember to use the header value exactly as it appears in the data.



..  _composition over inheritance: https://en.wikipedia.org/wiki/Composition_over_inheritance

..  _PHP streams: http://php.net/manual/en/book.stream.php
