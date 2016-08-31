------------------------------------
CSVelte\\Exception\\CSVelteException
------------------------------------

.. php:namespace: CSVelte\\Exception

.. php:class:: CSVelteException

    CSVelte\Exception
    A generic catch-all exception thrown whenever CSVelte doesn't have a more
    specific or more appropriate exception to throw. Also the exception all other
    exceptions in the library inherit from.

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
