###########
CSV Streams
###########

Rather than provide you with a whole arsenal of input and output classes for every conceivable source or destination for CSV data, CSVelte takes advantage of the power and flexibility of PHP's native streams functionality, allowing you to instantiate an :php:class:`IO\\Stream` object using any valid stream URI, open stream resource, :php:class:`SplFileObject`, PHP string, or any object that implements a ``__toString()`` method.

What is a stream?
=================

.. pull-quote::

    Streams were introduced with PHP 4.3.0 as a way of generalizing file, network, data compression, and other operations which share a common set of functions and uses. In its simplest definition, a stream is a resource object which exhibits streamable behavior. That is, it can be read from or written to in a linear fashion, and may be able to fseek() to an arbitrary locations within the stream.

    -- php.net_ definition of streams_ [#]_

Streams provide a unified interface for reading and writing to just about any conceivable source, whether they be local files, HTTP resources, stdin/stdout, the options are virtually endless. CSVelte takes advantage of this amazing flexibility and power by means of its :php:class:`IO\\Stream` class, which simply wraps PHP's native streams functions. Later on you will learn how to create CSV reader and writer objects, both of which delegate I/O functionality entirely to :php:class:`IO\\Stream`.

.. warning::

    Be careful not to confuse PHP streams with :php:class:`IO\\Stream`. These are two separate things. :php:class:`IO\\Stream` is a class defined by the CSVelte library, while PHP streams are a native feature of the PHP language itself. :php:class:`IO\\Stream` was written as an object-oriented API to PHP's native streams_.

The :php:class:`IO\\Stream` class
=================================

:php:class:`IO\\Stream` is a very simple, yet flexible class that allows you to manipulate data from just about any conceivable source, so long as it's supported by PHP's native stream system (although it is possible to write your own custom stream wrappers, that is a topic for another day). The class supports read, write, and seek operations so long as the underlying stream it represents supports these operations. For example, you can perform read operations on an HTTP stream, but you cannot perform write operations on it. The HTTP protocol simply doesn't allow write operations (if it did, the entire internet would descend into a cesspool containing nothing but ads for male enhancement drugs, every nook and cranny completely defaced with drawings of "peepees" and "weewees", not to mention lolcats).

Because you can never really know until runtime whether a particular stream is readable, writable, and/or seekable, :php:class:`IO\\Stream` provides methods that will tell you. You can call ``isReadable()``, ``isWritable()``, or ``isSeekable()`` to determine whether those operations are supported on your stream object.

.. code-block:: php

    <?php
    $stream = new Stream('http://www.example.com/data.csv');
    echo $stream->isReadable(); // outputs "true"
    echo $stream->isWritable(); // outputs "false"
    echo $stream->isSeekable(); // outputs "true"

.. note::

    Unless you intend to extend the :php:class:`IO\\Stream` class, you honestly don't really need to know all that much about how it works. At least in regards to its API. All you really need to know is that it provides a common interface for :php:class:`Reader`, :php:class:`Writer` and a few other classes to work with and that those classes delegate all actual I/O functionality to this one class.

Create a stream using an URI
----------------------------

PHP natively offers a multitude of possible stream wrappers [#]_. You can stream data using the local file system, FTP, SSL, HTTP, and cURL, just to name a few. Each stream wrapper works a little differently, so you'll need to consult PHP's streams_ documentation if you intend to use a stream wrapper not covered here (which means virtually all of them).

Local filesystem
~~~~~~~~~~~~~~~~

The (arguably) most common stream wrapper is "file", which allows the streaming of local files. To instantiate an :php:class:`IO\\Stream` object using a local file, simply pass a valid file name (including its path) in the constructor (file name may optionally be preceeded with ``file://``). You may also optionally pass a file access mode string [#]_ as a second parameter to tell :php:class:`IO\\Stream` how you intend to use the stream (see `fopen file modes`_ on php.net_). :php:class:`IO\\Stream` respects the rules specified by each of PHP's available access mode characters, so its behavior should be familiar if you've ever worked with PHP's :php:func:`fopen` function.

.. code-block:: php

    <?php
    // create a new local file stream object, and prepare it
    // for binary-safe reading (plus writing)
    $stream = new IO\Stream('file:///var/www/data.csv', 'r+b');
    // or...
    // create a new local file stream object, placing the file pointer at the
    // end of the file and preparing to append the file
    $stream = new IO\Stream('./data.csv', 'a');

HTTP
~~~~

Streaming CSV data over HTTP is made trivial with :php:class:`IO\\Stream`. Simply pass in the fully qualified URI to the CSV file and you're all set!

.. code-block:: php

    <?php
    $stream = new IO\Stream('http://www.example.com/data/products.csv');

PHP
~~~

The PHP stream wrapper provides access to various miscellaneous I/O streams such as standard input and standard output [#]_. You could use this stream wrapper from within a PHP CLI script to stream CSV data directly from the user.

.. code-block:: php

    <?php
    $stream = new IO\Stream('php://stdin');

For more detailed documentation regarding PHP's available stream wrappers and their respective options and parameters, I refer you to the `PHP streams documentation`_ at php.net_.

Create a stream with additional stream context
----------------------------------------------

Each of PHP's native stream wrappers (HTTP, file, FTP, etc.) has a list of optional stream context options and parameters that can be set to change a stream's context. For instance, the `http` stream wrapper allows you to specify such things as request method, headers, timeout, etc. :php:class:`IO\\Stream` allows you to pass in these parameters as the third argument to its constructor.

To demonstrate how this works, let's assume we have a script called ``download_data.php`` on a website called example.com. To make the script work, you must send it an HTTP POST request containing query, type, and format attributes. Our query is "active" and our type is "users". The format we want is, obviously, CSV. So, let's take a look at how we might use :php:class:`IO\\Stream` to stream the resulting CSV data.

.. code-block:: php

    <?php
    $stream = new IO\Stream('http://www.example.com/download_data.php', 'r', [
        'http' => [
            'method'  => 'POST',
            'header'  => 'Content-type: application/x-www-form-urlencoded',
            'content' => 'type=users&query=active&format=csv'
        ]
    ]);

This example is pretty straight-forward, but the point is made. Context parameters can make our :php:class:`IO\\Stream` objects *extremely* flexible and powerful if used correctly. Unfortunately, beyond this brief introduction, stream context parameters are outside the scope of this documentation. If you'd like to learn more about them, please check out the PHP documentation regarding `stream context options and parameters`_.

Using an open :php:class:`SplFileObject` to create a stream
--------------------------------------------------

Although CSVelte cannot work with the ``SplFileObject`` class directly, it *can* convert it to a valid :php:class:`IO\\Stream` object, which it understands perfectly.

.. code-block:: php

    <?php
    $file = new \SplFileObject('./files/data.csv', 'r+b');
    $stream = IO\Stream::streamize($file);

Create a stream from a standard PHP string
------------------------------------------

Often times you may end up with a PHP string containing CSV data. In this case, there is a convenient method to convert that PHP string to an :php:class:`IO\\Stream` object so that it may be read by the :php:class:`Reader` class. Yup, you guessed it, :php:meth:`IO\\Stream::streamize()`!

.. code-block:: php

    <?php
    $csv_string = some_func_that_returns_csv_string();
    $stream = IO\Stream::streamize($csv_string);

Create a stream from an existing stream resource
------------------------------------------------

If you already have a stream resource that you've opened using :php:func:`fopen`, you can pass that resource to either :php:meth:`IO\\Stream::streamize()` or directly to the :php:class:`IO\\Stream` constructor to create an :php:class:`IO\\Stream` object.

.. code-block:: php

    <?php
    $stream_resource = @fopen('http://www.example.com/data/example.csv', 'r');
    if (false === $stream_resource) {
        die("Could not read from stream URI.");
    }
    $stream = new IO\Stream($stream_resource);
    // or...
    $stream = IO\Stream::streamize($stream_resource);

.. _PHP streams documentation: http://php.net/manual/en/intro.stream.php

.. rubric:: Footnotes

.. [#] Succinct definition of PHP streams_ pulled from PHP's documentation at php.net_.
.. [#] PHP defines stream wrappers as "additional code which tells the stream how to handle specific protocols/encodings". See `PHP streams documentation`_ for a more complete description.
.. [#] File access mode strings are a short (typically 1-3 characters) string containing very concise instructions about how a file or stream should be opened. See `fopen file modes`_ for a more detailed explanation.
.. [#] Standard input and standard output are preconnected I/O channels, input typically being a data stream going into a program from the user and output being the stream where a program writes its output. See `standard streams`_ Wikipedia page for more on stdin/stdout.