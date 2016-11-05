========================
First Steps with CSVelte
========================

At this point, you should have CSVelte installed and ready to start working with some data! Before we begin though, it may be a good idea to get some terminology out of the way, just so that we're speaking the same language.

First, a few terms
------------------

    Dataset
        Because CSVelte can read CSV data from a variety of different sources, it's not technically correct to reference CSV "files" specifically when talking about CSV data. For this reason, this documentation uses the term CSV "dataset" rather than CSV "file" to refer to the contents of any given CSV resource.
    Flavor
        As I've mentioned already, CSV is not the most well-defined format there is. In fact, it's likely one of the worst. There is virtually no end to the different ways in which you might expect a CSV dataset to be formatted. In order to bring some order to the chaos, CSVelte defines several attributes that together make up a CSV "Flavor". Attributes such as delimiter, quote character, line terminator, etc. If you and I can agree on a common "flavor" of CSV, we can at least be sure our CSV files are formatted consistently and are therefor compatible.
    Taster
        Another unfortunate side-effect of CSV coming in so many flavors is that you can never really be sure which flavor you're going to get. Making matters worse, the CSV format doesn't natively support meta data of any kind (well, with the possible exception of an optional header row). The only way you can ever 100% reliably determine the flavor of a dataset is to open the file and look at its content yourself. That's what a taster does. It's simply an object that, given a dataset, will analyze (taste) a sample of it, and return a flavor object, each of its attributes set to the taster's educated best guess.
    Stream
        Rather than attempt to write classes for each potential source or destination for CSV data, CSVelte instead relies on the power and flexibility of PHP's native streams_ functionality. A stream, according to php.net_, "is a resource object which exhibits streamable behavior. That is, it can be read from or written to in a linear fashion". CSVelte provides a class called :php:class:`IO\\Stream` which provides an object-oriented interface to this functionality.
    Resource
        In PHP, a resource is a special type of variable used to represent external objects. More specifically, a stream resource is a reference to a streamable data source, or even more specifically, to a specific position within that data source. For example, let's assume we're streaming a file from the local file system. A resource variable in this instance will point to a specific position within your file, changing as you read or seek through it.

        I describe what a PHP resource is only so that you can understand the distinction between a PHP resource variable and a :php:class:`IO\\Resource` object within CSVelte. :php:class:`IO\\Resource` is a class used within CSVelte to represent a stream resource. It cannot be read from, it cannot be written to. It doesn't *do* anything. It simply wraps a native PHP stream resource, providing an object-oriented interface and some conveniences such as lazy-opening and the like. Within CSVelte, it's used wherever one would normally expect a native PHP stream resource.

Getting down to business
------------------------

For the sake of simply writing some code using CSVelte, let's take some common CSV-related use cases and see how CSVelte fares against them.

Producing a two-dimensional array from a CSV dataset
----------------------------------------------------

During the initial research phase of writing this library, I did a Google search for "php csv" and a large portion of the results were various PHP message boards with users asking for an easy way to read a CSV file and produce a two-dimensional array containing its data. This is as good a place as any to start.

Let's assume our CSV file is located on the local file system at ``/var/www/data/products.csv``. Our first step is going to be to create an :php:class:`IO\\Stream` object capable of reading our CSV file. To do that, we first need to instantiate a :php:class:`IO\\Resource` object using a valid stream URI. Every stream resource URI consists of a scheme followed by ``://``, followed by a path or identifier. Since we're accessing a local file, we want the ``file://`` scheme. So we simply prepend our file path with ``file://`` to get our stream URI: ``file:///var/www/data/products.csv`` . Finally, we can use this URI to instantiate a :php:class:`IO\\Resource` object.

.. code-block:: php

    $resource = new IO\Resource('file:///var/www/data/products.csv');

Now that we have a resource object, we can use it to instantiate a stream object, which will give us all the I/O methods we need to read and write data to our local file.

.. code-block:: php

    $resource = new IO\Resource('file:///var/www/data/products.csv');
    $stream = new IO\Stream($resource);
    // you can now ensure the stream object is readable by doing...
    $stream->isReadable(); // should return true

.. note::

A few shortcuts
---------------

Invoke the resource object
~~~~~~~~~~~~~~~~~~~~~~~~~~

For the sake of brevity in these examples, I'm going to show you a couple shortcuts you can use to reduce the amount of code it takes to get a stream object. First off, once you've instantiated a :php:class:`IO\\Resource` object, simply invoke it as if it were a function and it will return a :php:class:`IO\Stream` using your resource object.

.. code-block:: php

    $resource = new IO\Resource('file:///var/www/products.csv');
    // invoke a resource object as if it were a function to get a stream
    $stream = $resource();

Skip the resource object
~~~~~~~~~~~~~~~~~~~~~~~~

To create a stream object without first instantiating a :php:class:`IO\\Resource`, you can use the :php:meth:`IO\\Stream::open()` method, which does it for you. Its signature is very similar to the :php:class:`IO\\Resource` class's constructor.

.. code-block:: php

    // use the stream factory method to skip the resource object
    $stream = IO\Stream::open('file:///var/www/products.csv');

.. attention::

    For the sake of brevity, I will use the latter of these two techniques to create a stream object. But in your own code, you do what works for you.

The file stream wrapper
-----------------------

Although all stream URIs require a valid scheme to identify which stream wrapper is intended, ``file`` is a special case because it is the default stream wrapper or scheme. For this reason it is optional and may be omitted when constructing a stream URI. This means that our example URI could have just as easily been ``/var/www/data/products.csv``. And in fact, from here on out, we will leave out the ``file://`` portion when we reference stream URIs for the local filesystem.

At this point, we need to instantiate a :php:class:`Reader` object to read/parse CSV data from the stream object we just created. We already know that our CSV file is formatted using a comma as its delimiter, a line feed as its line terminator, and it has a header row. Let's create a flavor object with those attributes.

.. code-block:: php

    $flavor = new Flavor([
        'delimiter' => ',',
        'lineTerminator' => "\n",
        'header' => true
    ]);

Now, using our stream and flavor objects, we can finally instantiate the reader and call :php:meth:`Reader::toArray()` to get our two-dimensional array. Let's put it all together.

.. code-block:: php

    // create a stream object to read from our local file...
    $stream = IO\Stream::open('/var/www/data/products.csv');
    if (!$stream->isReadable()) {
        die('Cannot read CSV file.');
    }

    // now create a flavor object using our known flavor attributes...
    $flavor = new Flavor([
        'delimiter' => ',',
        'lineTerminator' => "\n",
        'header' => true
    ]);

    // now we can go ahead and instantiate our reader
    $reader = new Reader($stream, $flavor);
    // and we have our two-dimensional array!
    $array = $reader->toArray();

.. note::

    **Why do we need a Reader object** if we already have :php:class:`IO\\Stream`? Doesn't the :php:class:`IO\\Stream` object *read* data from its underlying stream?

    Yes it does. But the :php:class:`IO\\Stream` class is designed to be stupid (at least as it relates to CSV data). It only knows how to read bytes from a stream resource. Once the data's been read, its job is done. The :php:class:`Reader` object takes over at that point, taking plain text data being read to it by :php:class:`IO\\Stream` and applying semantic meaning to it. These are two entirely different kinds of "reading".

What if I don't know the CSV flavor?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The previous example looks simple enough, but what if we *didn't* know anything about our CSV data? What if we *didn't* know ahead of time what the delimiter and line terminator characters are? No big deal! Simply instantiate your reader the exact same way, only this time, omit the flavor parameter. In the absense of an explicit flavor, the reader will use the :php:class:`Taster` class internally to automatically determine these attributes for us (in other words, it will "taste" the CSV data and tell us its "flavor").

.. code-block:: php

    $stream = IO\Stream::open('/var/www/data/products.csv');
    $reader = new Reader($stream);
    $array = $reader->toArray();

In the vast majority of cases, the reader will be able to deduce the CSV flavor on its own and this will work just fine. However, if a flavor cannot be determined, an :php:exc:`Exception\\TasterException` will be thrown. You can use this to recover from such an error.

.. code-block:: php

    try {
        $stream = IO\Stream::open('/var/www/data/products.csv');
        $reader = new Reader($stream);
        $array = $reader->toArray();
    } catch (Exception\TasterException $e) {
        // this is an extreme action, in your own script you would handle this
        // a bit more gracefully, depending on the situation...
        die("Flavor could not be determined");
    }

Producing CSV data from a two-dimensional array
-----------------------------------------------

Well, I can't in good conscience show you how to convert a CSV file to a PHP array and then not show you how to convert it back! Fortunately it's pretty trivial. Let's assume we have a two-dimensional array containing the following data:

.. csv-table::

    1,Muhammed MacIntyre,3,35,Nunavut,Storage & Organization
    2,Barry French,293,68.02,Nunavut,Appliances
    3,Barry French,293,2.99,Nunavut,Binders and Binder Accessories
    4,Clay Rozendal,483,3.99,Nunavut,Telephones and Communication
    5,Carlos Soltero,515,5.94,Nunavut,Appliances
    6,Carlos Soltero,515,4.95,Nunavut,Office Furnishings
    7,Carl Jackson,613,7.72,Nunavut,Binders and Binder Accessories
    8,Carl Jackson,613,6.22,Nunavut,Storage & Organization
    9,Monica Federle,643,35,Nunavut,Storage & Organization
    10,Dorothy Badders,678,8.33,Nunavut,Paper

Again, our first task is going to be creating an :php:class:`IO\\Stream` object. Only this time, we'll want to prepare it for writing by passing the correct access mode string as the second parameter to :php:meth:`IO\\Stream::open()`. We want to create a new file on the local file system at ``/var/www/data/inventory.csv`` so we'll want to use "w" to open our stream in write mode [#]_.

.. code-block:: php

    $stream = IO\Stream::open('/var/www/data/inventory.csv', 'w');

Just as with our input stream and its :php:meth:`IO\\Stream::isReadable()` method, we can call :php:meth:`IO\\Stream::isWritable()` to make sure that our stream is indeed, writable.

.. code-block:: php

    $stream = IO\Stream::open($resource);
    // you can now ensure the stream object is writable by doing...
    $stream->isWritable(); // should return true

Now that we have an output stream object to write our data for us, we can instantiate our :php:class:`Writer` object. If you have a specific flavor object, you can pass that to the writer as well. Otherwise it will use the default (outlined by :rfc:`4180` [#]_). Let's put it all together.

.. code-block:: php

    <?php
    // we'll assume this variable contains our CSV data in an array...
    $csv_array = some_func_that_returns_array();

    // create stream in write mode...
    $stream = IO\Stream::open('/var/www/data/inventory.csv', 'w');
    if (!$stream->isWritable()) {
        die('Cannot write to CSV file');
    }

    // change the flavor a little...
    $flavor = new Flavor([
        'delimiter' => "\t",
        'lineTerminator' => "\n",
        'quoteStyle' => Flavor::QUOTE_ALL
    ]);

    // create a writer...
    $writer = new Writer($stream, $flavor);
    // now write our array and we're done!
    $writer->writeRows($csv_array);

There's more than one way to skin a cat
---------------------------------------

The two examples provided thus far offer solutions to arguably the two most common use cases involving CSV (for PHP anyway). So you may be asking yourself, "Shouldn't there be quicker, easier ways to do this?". And you'd be right. CSVelte provides shorter, simpler solutions to both these use cases. So why did I show you these verbose solutions rather than the simple ones? Because it's important that you see the entire interface (in all its power and flexibility) before I show you the facades and factory methods that abstract away all that flexibility for brevity and ease of use. For simple tasks like these, it makes no sense to waste keystrokes on instantiating a resource and then a stream and then a reader. But there are vastly more complex problems that CSVelte aims to solve and for them, all this composition suddenly becomes an asset.

In the next section we will explore streams and resources in detail, investigating all the ways we can use them to manipulate, read, and write CSV and tabular data.

.. hint::

    There are methods on the :php:class:`CSVelte` class that can provide solutions to both these use cases using a single line of code. I refer you to CSVelte's :ref:`csvelte-facade-methods` to find out more.

.. rubric:: Footnotes

.. [#] File access mode strings are a short (typically 1-3 characters) string containing very concise instructions about how a file or stream should be opened. See `fopen file modes`_ for a more detailed explanation.
.. [#] :rfc:`4180` was written in 2005 by Yakov Shafranovich in an attempt to formalize Microsoft Excel's particular flavor of CSV as the official CSV standard
