#########################
Auto-detecting CSV Flavor
#########################

If you know in advance what :doc:`/users_guide/flavors` you're working with, the :php:class:`Flavor` class is going to work great for you. But what if you don't? Does the CSV format have some way of telling the developer what flavor of CSV it's written in? Unfortunately, it doesn't. But CSVelte does. Any time you read CSV data using the :php:class:`Reader` class, it will attempt to determine the flavor of its provided dataset automatically. The upshot being that in the overwhelming majority of cases, you can point a :php:class:`Reader` object at some CSV data and it will just work.

How does it work?
=================

It's actually magic. I figured out how to do magic.

..  hint::

    **Is it really magic?**

    Yes.

Using flavor auto-detection
===========================

Using the auto-detect feature is so easy, you won't even know you're using it. Any time you instantiate a :php:class:`Reader` object without explicitly providing a flavor object, the library will analyze a sample of the data you're trying to read and build a flavor object *for* you, behind the scenes.

.. code-block:: php

    <?php
    // flavor will be quietly inferred from a sample of "products.csv"
    $reader = new Reader(IO\Stream::open("./files/products.csv"));

.. note::

    There will be a more detailed explanation of this when we get to the section on :doc:`reading`. But for now, all you need to know is that CSVelte will *always* try to figure out the CSV flavor on its own unless you explicitly provide one.

Analyzing CSV data manually
===========================

Behind the scenes, CSVelte's auto-detect feature ultimately boils down to a single method of the :php:class:`Taster` class. To manually run the CSV analyzer (flavor taster), you must instantiate a :php:class:`Taster` object, passing it a readable stream object.

.. code-block:: php

    <?php
    // create an input stream object that points to a CSV file
    $csv = IO\Stream::open('./data/products.csv');

    // now, using that stream object, instantiate your taster
    $taster = new Taster($csv);

    // finally, you can "lick" the CSV data to discern its particular "flavor"
    $flavor = $taster->lick();

This will work for the overwhelming majority of datasets, but if your data is too uniform or your sample too small, the taster object will issue an exception. The exception's message will contain a short explanation of why the taster failed to produce a flavor object, along with an error code. This allows your script to recover from such a failure and rather than display some arcane error page, perhaps prompt your end-user to provide this information or failing that, just proceeding with a sane default. Let's see what that might look like.

.. code-block:: php

    <?php
    // this time we wrap our tasting code in a try/catch
    // block for more graceful error recovery
    try {
        $csv = IO\Stream::open('./data/products.csv');
        $taster = new Taster($csv);
        $flavor = $taster->lick();
    } catch (Exception\TasterException $e) {
        // log exception or something...
        my_exception_log_function($e);
        // flavor could not be determined, so lets use a sane default...
        $flavor = new CSVelte\Flavor([
            'lineTerminator' => PHP_EOL
        ]);
    }
    // proceed with data processing...
    $reader = new CSVelte\Reader($csv, $flavor);
