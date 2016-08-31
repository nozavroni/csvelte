-----------------
CSVelte\\IO\\File
-----------------

.. php:namespace: CSVelte\\IO

.. php:class:: File

    CSVelte File.

    Represents a file for reading/writing. Implements both readable and writable interfaces so that it can be passed to either ``CSVelte\Reader`` or
    ``CSVelte\Writer``.

    .. php:const:: ERR_FILENOTFOUND

    .. php:const:: ERR_DIRNOTFOUND

    .. php:attr:: options

        protected array

        Initialization options for this file

    .. php:method:: __construct($filename, $options = [])

        File Object Constructor.

        :type $filename: string
        :param $filename: The path and name of the file
        :type $options: array
        :param $options: An array of any/none of the following options (see $options var above for more details)

    .. php:method:: read($length)

        Read from file.
        Read $length number of characters from file

        :type $length: int
        :param $length: Number of characters to read from the file
        :returns: string Up to $length characters read from the file

    .. php:method:: readLine()

        Read single line.
        Read the next line from the file (moving the internal pointer down a
        line).
        Returns multiple lines if newline character(s) fall within a quoted
        string.

        :returns: string A single line read from the file.

    .. php:method:: isEof()

        Is end of file?

        If the end of the file has been reached, this should return true.

        :returns: boolean True if end of file has been reached

    .. php:method:: write($str)

        Write to file.
        Write $data to the file.

        :param $str:
        :returns: int The number of bytes written to the file

    .. php:method:: rewind()

    .. php:method:: eof()

    .. php:method:: valid()

    .. php:method:: fgets()

    .. php:method:: fgetcsv($delimiter, $enclosure, $escape)

        :param $delimiter:
        :param $enclosure:
        :param $escape:

    .. php:method:: fputcsv($fields, $delimiter, $enclosure, $escape)

        :param $fields:
        :param $delimiter:
        :param $enclosure:
        :param $escape:

    .. php:method:: setCsvControl($delimiter, $enclosure, $escape)

        :param $delimiter:
        :param $enclosure:
        :param $escape:

    .. php:method:: getCsvControl()

    .. php:method:: flock($operation, $wouldblock)

        :param $operation:
        :param $wouldblock:

    .. php:method:: fflush()

    .. php:method:: ftell()

    .. php:method:: fseek($pos, $whence)

        :param $pos:
        :param $whence:

    .. php:method:: fgetc()

    .. php:method:: fpassthru()

    .. php:method:: fgetss($allowable_tags)

        :param $allowable_tags:

    .. php:method:: fscanf($format, $vars)

        :param $format:
        :param $vars:

    .. php:method:: fwrite($str, $length)

        :param $str:
        :param $length:

    .. php:method:: fread($length)

        :param $length:

    .. php:method:: fstat()

    .. php:method:: ftruncate($size)

        :param $size:

    .. php:method:: current()

    .. php:method:: key()

    .. php:method:: next()

    .. php:method:: setFlags($flags)

        :param $flags:

    .. php:method:: getFlags()

    .. php:method:: setMaxLineLen($max_len)

        :param $max_len:

    .. php:method:: getMaxLineLen()

    .. php:method:: hasChildren()

    .. php:method:: getChildren()

    .. php:method:: seek($line_pos)

        :param $line_pos:

    .. php:method:: getCurrentLine()

    .. php:method:: __toString()

    .. php:method:: getPath()

    .. php:method:: getFilename()

    .. php:method:: getExtension()

    .. php:method:: getBasename($suffix)

        :param $suffix:

    .. php:method:: getPathname()

    .. php:method:: getPerms()

    .. php:method:: getInode()

    .. php:method:: getSize()

    .. php:method:: getOwner()

    .. php:method:: getGroup()

    .. php:method:: getATime()

    .. php:method:: getMTime()

    .. php:method:: getCTime()

    .. php:method:: getType()

    .. php:method:: isWritable()

    .. php:method:: isReadable()

    .. php:method:: isExecutable()

    .. php:method:: isFile()

    .. php:method:: isDir()

    .. php:method:: isLink()

    .. php:method:: getLinkTarget()

    .. php:method:: getRealPath()

    .. php:method:: getFileInfo($class_name)

        :param $class_name:

    .. php:method:: getPathInfo($class_name)

        :param $class_name:

    .. php:method:: openFile($open_mode, $use_include_path, $context)

        :param $open_mode:
        :param $use_include_path:
        :param $context:

    .. php:method:: setFileClass($class_name)

        :param $class_name:

    .. php:method:: setInfoClass($class_name)

        :param $class_name:

    .. php:method:: _bad_state_ex()
