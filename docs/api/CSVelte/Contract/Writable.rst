---------------------------
CSVelte\\Contract\\Writable
---------------------------

.. php:namespace: CSVelte\\Contract

.. php:interface:: Writable

    Writable Interface

    Implement this interface to be writable by a CSVelte\Writer object

    .. php:method:: write($data)

        Write data to the output

        :param $data:
        :returns: int The number of bytes written
