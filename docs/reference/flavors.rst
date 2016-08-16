############################
The various "flavors" of CSV
############################

How does CSVelte address the very configurable nature of CSV as a format? It allows the developer to define "flavors" of CSV, as well as providing several common flavors out of the box. Let's see how they work.

Flavors of CSV
--------------

Taking cues from `Python's CSV module <https://docs.python.org/2/library/csv.html>`_, `Frictionless Data's CSV Dialect Description Format <http://specs.frictionlessdata.io/csv-dialect/>`_, as well as the `W3C <https://www.w3.org/>`_'s `CSV on the Web Working Group <https://www.w3.org/2013/csvw/wiki/Main_Page>`_, CSVelte allows developers to define distinct :ref:`flavors </reference/flavors>` of CSV so that consumers can rely on publishers using a specific :ref:`flavor </reference/flavors>`. Python has a similar concept they call "`dialects <https://docs.python.org/2/library/csv.html#dialects-and-formatting-parameters>`_". To define a flavor in CSV, you simply instantiate a ``CSVelte\Flavor`` object and specify its attributes.

.. code-block:: php

    <?php
    $flavor = new CSVelte\Flavor([
        'delimiter' => ",",
        'quoteChar' => '"',
        'doubleQuote' => true,
        'quoteStyle' => Flavor::QUOTE_MINIMAL,
        'lineTerminator' => "\n",
    ]);
