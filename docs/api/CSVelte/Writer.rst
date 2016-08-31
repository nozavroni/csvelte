---------------
CSVelte\\Writer
---------------

.. php:namespace: CSVelte

.. php:class:: Writer

    CSVelte Writer Base Class
    A PHP CSV utility library (formerly PHP CSV Utilities).

    .. php:attr:: flavor

        protected CSVelte\Flavor

    .. php:attr:: output

        protected CSVelte\Contracts\Writable

    .. php:attr:: headers

        protected \Iterator

    .. php:attr:: written

        protected int

    .. php:method:: __construct(Writable $output, Flavor $flavor = null)

        Class Constructor

        :type $output: Writable
        :param $output:
        :type $flavor: Flavor
        :param $flavor:
        :returns: void

    .. php:method:: getFlavor()

        Get the CSV flavor (or dialect) for this writer

        :returns: CSVelte\Flavor

    .. php:method:: setHeaderRow($headers)

        Sets the header row
        If any data has been written to the output, it is too late to write the
        header row and an exception will be thrown. Later implementations will
        likely buffer the output so that this may be called after writeRows()

        :param $headers:
        :returns: boolean

    .. php:method:: writeRow($row)

        Write a single row

        :param $row:
        :returns: int

    .. php:method:: writeHeaderRow(HeaderRow $row)

        :type $row: HeaderRow
        :param $row:

    .. php:method:: writeRows($rows)

        Write multiple rows

        :param $rows:
        :returns: int number of lines written

    .. php:method:: prepareRow(Iterator $row)

        Prepare a row of data to be written
        This means taking an array of data, and converting it to a Row object

        :type $row: Iterator
        :param $row:
        :returns: CSVelte\Table\AbstractRow

    .. php:method:: prepareData($data)

        Prepare a cell of data to be written (convert to Data object)

        :param $data:
        :returns: CSVelte\Table\Data

    .. php:method:: quoteString($str)

        :param $str:

    .. php:method:: escapeString($str, $isQuoted = true)

        :param $str:
        :param $isQuoted:
