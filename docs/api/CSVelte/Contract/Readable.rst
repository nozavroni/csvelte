---------------------------
CSVelte\\Contract\\Readable
---------------------------

.. php:namespace: CSVelte\\Contract

.. php:interface:: Readable

    Readable Interface

    Implement this interface to be "readable". This means that the CSVelte\Reader class can read you (use you as a source of CSV data).

    .. php:method:: read($chars)

        Read in the specified amount of characters from the input source

        :param $chars:
        :returns: string The specified amount of characters read from input source

    .. php:method:: readLine()

        Read a single line from input source and return it (and move pointer to )
        the beginning of the next line)

        :returns: string The next line from the input source

    .. php:method:: isEof()

        Determine whether the end of the readable resource has been reached

        :returns: boolean

    .. php:method:: rewind()

        File must be able to be rewound when the end is reached

        :returns: void
