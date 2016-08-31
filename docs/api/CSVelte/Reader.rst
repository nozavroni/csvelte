---------------
CSVelte\\Reader
---------------

.. php:namespace: CSVelte

.. php:class:: Reader

    CSV Reader

    Reads CSV data from any object that implements CSVelte\Contract\Readable.

    .. php:attr:: source

        protected CSVelte\Contract\Readable

        This class supports any sources of input that implements this interface.
        This way I can read from local files, streams, FTP, any class that
        implements
        the "Readable" interface

    .. php:attr:: flavor

        protected CSVelte\Flavor

    .. php:attr:: current

        protected CSVelte\Table\AbstractRow

    .. php:attr:: line

        protected integer

    .. php:attr:: header

        protected CSVelte\Table\HeaderRow

    .. php:attr:: filters

        protected array

    .. php:attr:: open

        protected bool

    .. php:attr:: escape

        protected bool

    .. php:method:: __construct(Readable $input, Flavor $flavor = null)

        Reader Constructor.
        Initializes a reader object using an input source and optionally a flavor

        :type $input: Readable
        :param $input:
        :type $flavor: Flavor
        :param $flavor:

    .. php:method:: load()

        Load a line into memory

        :returns: void ($this?)

    .. php:method:: readLine()

        Read single line from CSV data source (stream, file, etc.), taking into
        account CSV's de-facto quoting rules with respect to designated line
        terminator character when they fall within quoted strings.

        :returns: string

    .. php:method:: inQuotedString($line, $quoteChar, $escapeChar)

        Determine whether last line ended while a quoted string was still "open"

        :param $line:
        :param $quoteChar:
        :param $escapeChar:
        :returns: bool

    .. php:method:: getFlavor()

        Flavor Getter.
        Retreive the "flavor" object being used by the reader

        :returns: CSVelte\Flavor

    .. php:method:: hasHeader()

        Check if flavor object defines header

        Determine whether or not the input source's CSV data contains a header row
        or not. Unless you explicitly specify so within your Flavor object,
        this method is a logical best guess. The CSV format does not provide
        metadata of any kind and therefor does not provide this info.

        :returns: boolean True if the input source has a header row (or, to be more ) accurate, if the flavor SAYS it has a header row)

    .. php:method:: replaceQuotedSpecialChars($data, $delim, $quo, $eol)

        Temporarily replace special characters within a quoted string

        Replace all instances of newlines and whatever character you specify (as
        the delimiter) that are contained within quoted text. The replacements are
        simply a special placeholder string. This is done so that I can use the
        very unsmart "explode" function and not have to worry about it exploding
        on delimiters or newlines within quotes. Once I have exploded, I typically
        sub back in the real characters before doing anything else.

        :param $data:
        :param $delim:
        :param $quo:
        :param $eol:
        :returns: string The data with replacements performed

    .. php:method:: undoReplaceQuotedSpecialChars($data, $delim, $eol)

        Undo temporary special char replacements

        Replace the special character placeholders with the characters they
        originally substituted.

        :type $data: string
        :param $data: The data to undo replacements in
        :type $delim: string
        :param $delim: The delimiter character
        :type $eol: string
        :param $eol: The character or string of characters used to terminate lines
        :returns: string The data with placeholders replaced with original characters

    .. php:method:: unQuote($data)

        Remove quotes wrapping text.

        :param $data:
        :returns: string The data with quotes stripped from the outside of it

    .. php:method:: unEscape($str, $esc, $quo)

        :param $str:
        :param $esc:
        :param $quo:

    .. php:method:: parse($line)

        Parse a line of CSV data into an array of columns

        :param $line:
        :returns: array An array of columns

    .. php:method:: current()

    .. php:method:: next()

    .. php:method:: valid()

    .. php:method:: key()

    .. php:method:: rewind()

    .. php:method:: header()

    .. php:method:: addFilter(Closure $filter)

        :type $filter: Closure
        :param $filter:

    .. php:method:: addFilters($filters)

        :param $filters:

    .. php:method:: filter()
