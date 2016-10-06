################
Writing CSV Data
################

The :php:class:`Writer` class
================

The :php:class:`Writer` class is the main workhorse for writing CSV data to any number of output sources. Instantiating a writer is very similar to instantiating a :php:class:`Reader` object. You simply instantiate any class that implements the :php:interface:`Contracts\\Writable` interface [#]_ and use that to instantiate a writer object.

Let's assume we want to write a CSV file called ``./data/products.csv``. We would need to instantiate a :php:class:`IO\\Stream` object pointing to that file, making sure to supply the "w" access mode to open the file in write mode (you could also use the "a" mode if you want to append a stream rather than write a new one [#]_). Let's see how that looks:

.. code-block:: php

    // we use "w" access mode string to open stream in write mode
    $out = new IO\Stream('./data/products.csv', 'w');
    $writer = new Writer($out);

You could do the same thing using CSVelte's writer factory method.

.. code-block:: php

    $writer = CSVelte::writer('./data/products.csv');

Setting the flavor
==================

If you want to use a specific flavor of CSV (rather than the standard :php:class:`Flavor` class), you can do so by passing a :php:class:`Flavor` object (or an associative array of flavor attributes) as the second parameter to the writer's constructor and the writer will write CSV according to your specified flavor. See :doc:`/users_guide/flavors` for more on flavors and formatting.

.. code-block:: php
   :emphasize-lines: 2

    $out = new IO\Stream('./data/products.csv', 'w');
    $flavor = new Flavor(['delimiter' => "\t"]);
    $writer = new Writer($out, $flavor);

As I mentioned before, it is also acceptable to pass an associative array to the writer class rather than an :php:class:`Flavor` object to override the default flavor's attributes. Here, we will override the standard delimiter, which is a comma, and use a tab character instead.

.. code-block:: php
   :emphasize-lines: 2

    $out = new IO\Stream('./data/products.csv', 'w');
    $writer = new Writer($out, ['delimiter' => "\t"]);

We can shave off even *more* keystrokes by using CSVelte's writer factory method to generate our writer for us. As long as you don't need some custom stream output or something, this is the quickest and easiest way and it works just fine. Again, you can pass either a :php:class:`Flavor` object *or* an associative array of flavor attributes as the second parameter.

.. code-block:: php

    $writer = CSVelte::writer('./data/products.csv', new Flavor\ExcelTab);

    // or...

    $writer = CSVelte::writer('./data/products.csv', ['delimiter' => "\t"]);

Writing a single row
====================

Once you've instantiated a :php:class:`Writer` object, you can use the :php:meth:`Writer::writeRow()` method to write CSV line-by-line. You simply pass it an array or traversable (just be sure it contains the correct number of fields in the correct order [#]_).

.. code-block:: php

    <?php
    $out = new IO\Stream('./data/products.csv', 'w');
    $writer = new Writer($out);
    // you can pass an array...
    $writer->writeRow(['one', 2, 'three', 'fore']);
    // or any traversable object, so long as it contains the correct number of fields...
    $writer->writeRow(new ArrayIterator(['five', 'sicks', '7 "ate" 9', 10]));

Depending on the :php:class:`Flavor` object you use, this should output something along the lines of:

.. code-block:: csv

    one,2,three,fore
    five,sicks,"7 ""ate"" 9",10

Writing multiple rows
=====================

If you have a two-dimensional array or any other traversable tabular data [#]_, you can pass it to the :php:meth:`Writer::writeRows()` method to write multiple rows at once.

.. code-block:: php

    <?php
    $out = new IO\Stream('./data/albums.csv', 'w');
    $writer = new Writer($out);
    $writer->writeRows([
        ['Lateralus', 'Tool', 2001, 'Volcano Entertainment'],
        ['Wish You Were Here', 'Pink Floyd', 1975, 'Columbia'],
        ['The Fragile', 'Nine Inch Nails', 1999, 'Interscope']
    ]);

Depending on your :php:class:`Flavor` attributes, this should output something along the lines of:

.. code-block:: csv

    Lateralus,Tool,2001,Volcano Entertainment
    Wish You Were Here,Pink Floyd,1975,Columbia
    The Fragile,Nine Inch Nails,1999,Interscope

Setting the header row
======================

.. todo::

    It would be nice if the writer was smart enough to look at the keys being passed to its writeRow method and if they are associative, use them as the header (if the flavor has header => true)

CSV files allow an optional header row to designate labels for each column within the data. If present, it should always be the first row in the data. You can go about writing your header row one of two ways. You can do it implicitly, by simply making sure the first row you write is your header row, like so:

.. code-block:: php
   :emphasize-lines: 4

    $out = new IO\Stream('./data/albums.csv', 'w');
    $writer = new Writer($out);
    $writer->writeRows([
        ['Album', 'Artist', 'Year', 'Label'],
        ['Lateralus', 'Tool', 2001, 'Volcano Entertainment'],
        ['Wish You Were Here', 'Pink Floyd', 1975, 'Columbia'],
        ['The Fragile', 'Nine Inch Nails', 1999, 'Interscope']
    ]);

But if you prefer to be explicit, like I do, you may use the :php:meth:`Writer::setHeaderRow()` method. Just be sure to call it before writing any other rows to your output.

.. code-block:: php
   :emphasize-lines: 3

    $out = new IO\Stream('./data/albums.csv');
    $writer = new Writer($out);
    $writer->setHeaderRow(['Album', 'Artist', 'Year', 'Label']);
    $writer->writeRows([
        ['Lateralus', 'Tool', 2001, 'Volcano Entertainment'],
        ['Wish You Were Here', 'Pink Floyd', 1975, 'Columbia'],
        ['The Fragile', 'Nine Inch Nails', 1999, 'Interscope']
    ]);

This does the exact same thing as the first approach did, only it's more explicit and more clear to any programmer who comes along later, what you are trying to do.

.. danger::

    You must be careful not to call :php:meth:`Writer::setHeaderRow()` after data has already been written to the output source. That is to say, after any calls to :php:meth:`Writer::writeRow()` or :php:meth:`Writer::writeRows()`. This will trigger an :php:exc:`Exception\\WriterException`.

.. todo::

    Rather than throw a WriterException in the writer, you should have some way for the stream object you're writing to, to buffer its write operations and then if and only if the buffer has been flushed the writer will throw an exception.

    **Update:** Even better, add a isEmpty() method or something like that, to the ``IO\Stream`` class that will return true if and only if the stream is empty (which will be true even if there is stuff in its buffer, so long as it hasn't been flushed yet).

.. todo::

    Just had an idea pop into my head. I'm sure somebody has done it before, but anyway, see if you can figure out some way to have Travis or some other service put together a documentation coverage score/percentage just like test coverage. There would be like a Docs Coverage badge along with all the others. You could write a sphinx extension that allowed you to mark which class/method/function/etc. you are documention on each page... I dunno... might be kind of hard to make it accurate but it'd be nice to have if you could get it to work.

Using reader and writer together
================================

The reader and writer classes are very useful by themselves, but when you combine them, you can really start to see the power and usability of CSVelte. Let's take a look at a few ways you can use :php:class:`Reader` and :php:class:`Writer` together to accomplish common tasks.

Reformatting by changing flavor
-------------------------------

As I mentioned before, :php:meth:`Writer::writeRows()` accepts either an array of arrays or any tabular data structure. Instances of the :php:class:`Reader` class, by design, fall within the second category. This means that you can instantiate a reader object and pass it to :php:meth:`Writer::writeRows()` as a means to either filter out certain rows, change its flavor (formatting), or both. Let's take a look at a few examples.

.. code-block:: php

    <?php
    // create our reader object, allowing it to automatically determine CSV flavor
    $reader = CSVelte::reader("./data/albums.csv");

    // now create a writer object, passing it an explicit flavor we want to reformat to
    $writer = CSVelte::writer("./data/albums.tsv", new Flavor\ExcelTab());

    // now you can simply pass the reader object to writeRows to get a tab-delimited file
    $writer->writeRows($reader);

Filtering out unwanted rows
---------------------------

As demonstrated in :doc:`/users_guide/reading`, you can use the :php:meth:`Reader::addFilter()` method to attach any number of anonymous functions to your reader to filter out unwanted rows. You can then iterate your filtered reader using the :php:meth:`Reader::filter()` method. Again, because :php:meth:`Writer::writeRows()` can accept any traversable tabular data structure, you can pass the return value of :php:meth:`Reader::filter()` to :php:meth:`Writer::writeRows()` to write a new CSV file, less your unwanted rows.

.. code-block:: php
   :emphasize-lines: 13

    // create our reader object
    $reader = CSVelte::reader("./data/albums.csv");
    // this will filter out all but 90's albums
    $reader->addFilter(function($row) {
        return ($row['Year'] >= 1990 && $row['Year'] < 2000);
    });

    // now create a writer object, pointing to a new "90s-albums.csv" file
    $writer = CSVelte::writer("./data/90s-albums.csv");

    // now you can simply pass the reader object to writeRows to get a CSV
    // file with only 90's albums from the original CSV file
    $writer->writeRows($reader->filter());

.. rubric:: Footnotes

.. [#] CSVelte only ships with one class that implements the :php:interface:`Contract\\Writable` interface, and that is  :php:class:`IO\\Stream` -- see :doc:`/users_guide/streams` for more about that class
.. [#] See the `fopen file modes`_ section on php.net_ for more possible stream/file access modes.
.. [#] Every row in a CSV dataset should contain the same number of fields in the same order. For full description of CSV format, see ":doc:`/users_guide/getting_started/what_is_csv`"
.. [#] Tabular data, in this context, refers to any traversable_ two-dimensional data structure. Each set of traversables must contain the same number of fields, in the same order or an exception will be thrown
