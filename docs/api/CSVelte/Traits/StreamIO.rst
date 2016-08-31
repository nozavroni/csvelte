-------------------------
CSVelte\\Traits\\StreamIO
-------------------------

.. php:namespace: CSVelte\\Traits

.. php:trait:: StreamIO

    .. php:attr:: source

        protected resource

    .. php:attr:: info

        protected array

    .. php:attr:: position

        protected integer

    .. php:method:: __construct($stream)

        Class constructor

        :param $stream:

    .. php:method:: __destruct()

        Class destructor

        :returns: void

    .. php:method:: close()

        Close the stream
        Close the stream resource and release any other resources opened by this
        stream object.

        :returns: bool

    .. php:method:: getStreamResource()

        Retrieve underlying stream resource

        :returns: resource

    .. php:method:: position()

        Get the current position of the pointer

        :returns: integer Position of pointer within source

    .. php:method:: updateInfo()

        Get the current position of the pointer

        :returns: integer|false Position of pointer within source or false on failure

    .. php:method:: name()

        Retrieve the name of this stream. If stream is a file, it will return the
        file's name. If it's some other type of stream, it's hard to say what,
        exactly, the name will be.

        :returns: string The name of the stream resource

    .. php:method:: path()

        Retrieve the dirname part of the stream name

        :returns: string The dirname of this stream's path

    .. php:method:: getMode()
