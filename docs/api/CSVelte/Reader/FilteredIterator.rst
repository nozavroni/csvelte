---------------------------------
CSVelte\\Reader\\FilteredIterator
---------------------------------

.. php:namespace: CSVelte\\Reader

.. php:class:: FilteredIterator

    Filtered Reader Iterator

    This class is not intended to be instantiated manually. It is returned by the CSVelte\Reader class when filter() is called to iterate over the CSV file,
    skipping all rows that don't pass the filter(s) tests.

    .. php:attr:: filters

        protected array

        A list of callback functions

    .. php:method:: __construct(CsvReader $reader, $filters)

        FilteredIterator Constructor

        Initializes the iterator using the CSV reader and its array of callback
        filter functions/callables.

        :type $reader: CsvReader
        :param $reader: The CSV reader being iterated
        :param $filters:

    .. php:method:: accept()

        Run filters against each row.
        Loop through all of the callback functions, and if any of them fail, do
        not include this row in the iteration.

        :returns: boolean

    .. php:method:: rewind()

    .. php:method:: valid()

    .. php:method:: key()

    .. php:method:: current()

    .. php:method:: next()

    .. php:method:: getInnerIterator()
