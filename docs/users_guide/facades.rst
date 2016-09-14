#####################
Facades and Factories
#####################

My goal with CSVelte is to provide a simple, yet powerful and flexible object-oriented interface for CSV data processing and manipulation. Sometimes though, in order to provide the level of flexibility I desire, simplicity and ease of use suffer. But, being the hard-headed fella that I am, rather than give up any of that power or flexibility, I  provide you with facades and factory methods which instead abstract away that flexibility in favor of ease of use.

.. note::

    If you're wondering why I showed you these *last*, it's because I wanted you to learn how to instantiate readers and writers manually before resorting to factory/facade methods exclusively. Using these methods eliminates the possibility for you to use a custom :php:class:`IO\\Stream` object, effectively eliminating a *huge* chunk of functionality just to save a few keystrokes. Don't get me wrong, there's nothing wrong with shortcuts, just don't overdo it.

Factory methods
===============

CSVelte provides, via the :php:class:`CSVelte` class, several methods for quickly and easily generating common objects. Let's take a look at them.

.. php:class:: CSVelte
   :hidden:

.. php:staticmethod:: reader($uri [, $flavor = null ] )

   :param string $uri: A fully-qualified stream URI or the path to a local file.
   :param array|Flavor $flavor: An explicit flavor object or an array of flavor attributes to pass to the reader.
   :returns: Reader object for specified stream URI or file
   :throws: :php:exc:`Exception\\IOException`

    Reader factory method. Provides a shortcut for creating a :php:class:`Reader` object.

    .. code-block:: php

        <?php
        foreach (CSVelte::reader("./data/inventory.csv") as $line_no => $row) {
            do_something_with($row);
        }

.. php:staticmethod:: writer($uri [, $flavor = null ] )

   :param string $uri: A fully-qualified stream URI or the path to a local file.
   :param array|Flavor $flavor: An explicit flavor object or an array of flavor attributes to pass to the reader.
   :returns: Writer object for specified stream URI or file

    Writer factory method. Provides a shortcut for creating a :php:class:`Writer` object.

    .. code-block:: php

        <?php
        $data = some_func_that_returns_tabular_data();
        CSVelte::writer("./data/reports.csv", [
            'delimiter' => "\t",
            'lineTerminator' => "\n"
        ])->writeRows($data);

.. _csvelte-facade-methods:

Facade methods
==============

The :php:class:`CSVelte` class also provides the following facade methods [#]_.

.. php:class:: CSVelte
   :hidden:

.. php:staticmethod:: export($uri [, $data [, $flavor = null ]] )

   :param string $uri: A fully-qualified stream URI or the path to a local file.
   :param mixed $data: Anything that can be passed to Writer::writeRows()
   :param array|Flavor $flavor: An explicit flavor object or an array of flavor attributes to pass to the reader.
   :returns: The number of rows written to the output stream.

    Writer facade method. Provides a shortcut for exporting tabular data to a stream or local file.

    .. code-block:: php

        <?php
        $data = some_func_that_returns_tabular_data();
        CSVelte::export("./data/reports.csv", $data, [
            'delimiter' => "\t",
            'lineTerminator' => "\n"
        ]);

.. hint::

    Although there isn't currently a :php:meth:`CSVelte::import()` method (to produce a two-dimensional array from a CSV dataset), you may combine the :php:meth:`CSVelte::reader()` and :php:meth:`CSVelte::toArray()` methods to approximate this functionality.

    .. code-block:: php

        CSVelte::reader("./data/products.csv")->toArray();

.. todo::

    Once you have a little more time, elaborate on the trade-off concept and provide some examples of when each interface is appropriate (facade or plain object instantiation). Don't forget there is half a page of text I wrote for this just dumped into the examples file.

.. todo::

    * Need to refactor ``CSVelte`` class. It should be using ``IO\Stream`` rather than ``IO\File``. ``IO\File`` is going to be deleted anyway I believe.
    * Update API docs for ``CSVelte`` methods. Flavor param accepts array or flavor now
    * Now throws IOException rather than the ones it mentions

.. tip::

     It's a trade-off, like almost any design decision in programming. And it's one you'll have to make when you go to write your own code using CSVelte. The question you need to ask yourself is, "Do I need power and flexibility, or do I just need to get shit done?"

.. rubric:: Footnotes

.. [#] A facade, in programming, is the abstraction of a complex and/or verbose interface into a more concise, simpler one. See "`facade design pattern`_".
