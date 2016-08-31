--------------------------------------
CSVelte\\Exception\\EndOfFileException
--------------------------------------

.. php:namespace: CSVelte\\Exception

.. php:class:: EndOfFileException

    CSVelte\Exception\EndOfFileException
    Thrown when user attempts to access/read from a file/stream/resource when its
    internal pointer has already reached the end of the file.

    .. php:attr:: message

        protected

    .. php:attr:: code

        protected

    .. php:attr:: file

        protected

    .. php:attr:: line

        protected

    .. php:method:: __clone()

    .. php:method:: __construct($message, $code, $previous)

        :param $message:
        :param $code:
        :param $previous:

    .. php:method:: __wakeup()

    .. php:method:: getMessage()

    .. php:method:: getCode()

    .. php:method:: getFile()

    .. php:method:: getLine()

    .. php:method:: getTrace()

    .. php:method:: getPrevious()

    .. php:method:: getTraceAsString()

    .. php:method:: __toString()
