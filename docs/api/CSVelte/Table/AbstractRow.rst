---------------------------
CSVelte\\Table\\AbstractRow
---------------------------

.. php:namespace: CSVelte\\Table

.. php:class:: AbstractRow

    Table row abstract base class
    Represents a row of tabular data (represented by CSVelte\Table\Data objects)

    .. php:attr:: columns

        protected array

    .. php:attr:: position

        protected integer

    .. php:attr:: headers

        protected HeaderRow

    .. php:attr:: flavor

        protected CSVelte\Flavor

    .. php:method:: __construct($columns, Flavor $flavor = null)

        Class constructor

        :param $columns:
        :type $flavor: Flavor
        :param $flavor:

    .. php:method:: __toString()

        String overloading

        :returns: string representation of this object

    .. php:method:: join($delimiter = null)

        Join columns together using specified delimiter

        :param $delimiter:
        :returns: string

    .. php:method:: toArray()

        Convert object to an array

        :returns: array representation of the object

    .. php:method:: count()

        Count columns within the row

        :returns: integer The amount of columns

    .. php:method:: current()

        Get the current column's data object

        :returns: CSVelte\Table\Data

    .. php:method:: key()

        Get the current key (column number or header, if available)

        :returns: string The "current" key

    .. php:method:: next()

        Advance the internal pointer to the next column's data object
        Also returns the next column's data object if there is one

        :returns: CSVelte\Table\Data The "next" column's data

    .. php:method:: rewind()

        Return the internal pointer to the first column and return that object

        :returns: void

    .. php:method:: valid()

        Is the current position within the row's data columns valid?

        :returns: boolean

    .. php:method:: offsetExists($offset)

        Is there an offset at specified position

        :param $offset:
        :returns: boolean

    .. php:method:: offsetGet($offset)

        Retrieve offset at specified position or by header name

        :param $offset:
        :returns: CSVelte\Table\Data

    .. php:method:: offsetSet($offset, $value)

        Set offset at specified position or by header name

        :param $offset:
        :param $value:
        :returns: void

    .. php:method:: offsetUnset($offset)

        Unset offset at specified position/index

        :param $offset:
        :returns: void

    .. php:method:: assertOffsetExists($offset)

        Throw exception unless offset/index exists

        :param $offset:
        :returns: void

    .. php:method:: raiseImmutableException($msg = null)

        Raise (throw) immutable exception

        :param $msg:
        :returns: void
