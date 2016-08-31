---------------------------
CSVelte\\Contract\\DataType
---------------------------

.. php:namespace: CSVelte\\Contract

.. php:interface:: DataType

    Data Type interface

    Implement this interface to be a "data type"

    .. php:method:: isValid()

        Test string against regex validation pattern

        :returns: boolean

    .. php:method:: getValue()

        Retrieve internal, semantic value of this value

        :returns: mixed

    .. php:method:: __toString()

        Magic method for returning string version of this value

        :returns: string
