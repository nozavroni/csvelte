----------------------
CSVelte\\Input\\Stream
----------------------

.. php:namespace: CSVelte\\Input

.. php:class:: Stream

    CSVelte\Input\Stream
    Represents a stream source for CSV data

    .. php:const:: RESOURCE_TYPE

    .. php:const:: MAX_LINE_LENGTH

    .. php:attr:: source

        protected resource

    .. php:attr:: info

        protected array

    .. php:attr:: position

        protected integer

    .. php:method:: getMode()

        Get the "mode" used to open stream resource handle

        :returns: string

    .. php:method:: read($length)

        :param $length:

    .. php:method:: readLine($max = null, $eol = PHP_EOL)

        :param $max:
        :param $eol:

    .. php:method:: isEof()

        Have we reached the EOF (end of file/stream)?

        :returns: boolean

    .. php:method:: rewind()

        File must be able to be rewound when the end is reached

        :returns: void

    .. php:method:: assertStreamExistsAndIsReadable()

        Does a series of checks on the internal stream resource to ensure it is
        readable, hasn't been closed already, etc. If it finds a problem, the
        appropriate exception will be thrown. Called before any attempts to read
        from the stream resource.

        :returns: void

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
