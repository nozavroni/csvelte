#########################
Auto-detecting CSV Flavor
#########################

If you know in advance what :ref:`flavor </reference/flavors>` of CSV you're working with, the ``CSVelte\Flavor`` class is going to work great for you. But what if you don't? Does CSV have some way of telling the developer what flavor of CSV it's written in? Unfortunately, no. It doesn't. But CSVelte does. Any time you read CSV data, whether it be from a local file, a string, or otherwise, CSVelte will attempt to determine its flavor automatically. The upshot being that in the overwhelming majority of cases, you can point CSVelte at some CSV data and it will just work.

How does it work?
=================

It's actually magic. I figured out how to do magic.

.. note::

    **Is it *really* magic?**

    Yes.

Using flavor auto-detection
===========================

Using the auto-detect feature is so easy, you won't even know you're using it. Any time you instantiate a ``CSVelte\Reader`` object without providing an explicit ``CSVelte\Flavor`` object, the library will analyze a sample of the data you're trying to read and build a flavor object *for* you, behind the scenes.

.. code-block:: php

    <?php
    // flavor will be quietly inferred from a sample of "products.csv"
    $reader = CSVelte::reader("./files/products.csv");

There will be a more detailed explanation of this when we get to the section on :doc:`reading`. But for now, all you need to know is that CSVelte will *always* try to figure out the CSV flavor on its own unless you explicitly provide one.

Analyzing CSV data manually
===========================

Behind the scenes, CSVelte's auto-detect feature ultimately boils down to a single method of a single class called ``CSVelte\Taster``. To manually run the data analyzer (flavor taster), you must instantiate a ``CSVelte\Taster`` object. To do this, you'll need a source of CSV data. Let's take a look at how this might look.

.. code-block:: php

    <?php
    // create an input source object that points to a CSV file
    $csv = new CSVelte\Input\File('./data/products.csv');

    // now, using that input object, instantiate your taster
    $taster = new CSVelte\Taster($csv);

    // finally, you can "lick" the CSV data to discern its particular "flavor"
    $flavor = $taster->lick();

This will work for the overwhelming majority of datasets, but if your data is too uniform or your sample too small ``CSVelte\Taster`` will issue an exception. The exception's message will contain a short explanation of why the taster failed to produce a flavor object. This allows your script to recover from such a failure and rather than display some arcane error page, perhaps prompt your end-user to provide this information or failing that, just proceeding with a sane default. Let's see what that might look like.

.. code-block:: php

    <?php
    // this time we wrap our tasting code in a try/catch
    // block for more graceful error recovery
    try {
        $csv = new CSVelte\Input\File('./data/products.csv');
        $taster = new CSVelte\Taster($csv);
        $flavor = $taster->lick();
    } catch (CSVelte\Exception\TasterException $e) {
        // log exception or something...
        my_exception_log_function($e);
        // flavor could not be determined, so lets use a sane default...
        $flavor = new CSVelte\Flavor([
            'lineTerminator' => "\n"
        ]);
    }
    // proceed with data processing...
    $reader = new CSVelte\Reader($csv, $flavor);
