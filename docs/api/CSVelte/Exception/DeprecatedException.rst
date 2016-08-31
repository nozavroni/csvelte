---------------------------------------
CSVelte\\Exception\\DeprecatedException
---------------------------------------

.. php:namespace: CSVelte\\Exception

.. php:class:: DeprecatedException

    CSVelte\Exception\DeprecatedException
    This exception is thrown when users attempt to use features of the library
    that have been deprecated. This allows code to not necessarily braek even when
    the feature they're trying to use no longer exists or will be removed in the
    next version.

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
