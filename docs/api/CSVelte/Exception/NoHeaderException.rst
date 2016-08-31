-------------------------------------
CSVelte\\Exception\\NoHeaderException
-------------------------------------

.. php:namespace: CSVelte\\Exception

.. php:class:: NoHeaderException

    CSVelte\Exception\NoHeaderException
    There are various methods throughout the library that expect a CSV source to
    have a header row. Rather than doing something like:
    if (if $file->hasHeader()) {
    $header = $file->getHeader()
    }
    you can instead simply call $header->getHeader() and handle this exception if
    said file has no header

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
