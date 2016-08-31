---------------
CSVelte\\Taster
---------------

.. php:namespace: CSVelte

.. php:class:: Taster

    CSVelte\Taster
    Given CSV data, Taster will "taste" the data and provide its buest guess at
    its "flavor". In other words, this class inspects CSV data and attempts to
    auto-detect various CSV attributes such as line endings, quote characters, etc..

    .. php:const:: EOL_UNIX

        End-of-line constants

    .. php:const:: HORIZONTAL_TAB

        ASCII character codes for "invisibles"

    .. php:const:: DATA_NONNUMERIC

        Data types -- Used within the lickQuotingStyle method

    .. php:const:: PLACEHOLDER_NEWLINE

        Placeholder strings -- hold the place of newlines and delimiters contained
        within quoted text so that the explode method doesn't split incorrectly

    .. php:const:: SAMPLE_SIZE

        Recommended data sample size

    .. php:attr:: input

        protected CSVelte\Contract\Readable

    .. php:attr:: sample

        protected string

        Sample of CSV data to use for tasting (determining CSV flavor)

    .. php:method:: __construct(Readable $input)

        Class constructor--accepts a CSV input source

        :type $input: Readable
        :param $input:
        :returns: void

    .. php:method:: create(Readable $input)

        I'm not sure what this is for...

        :type $input: Readable
        :param $input:
        :returns: CSVelte\Taster

    .. php:method:: lick()

        Examine the input source and determine what "Flavor" of CSV it contains.
        The CSV format, while having an RFC (https://tools.ietf.org/html/rfc4180),
        doesn't necessarily always conform to it. And it doesn't provide meta such
        as the delimiting character, quote character, or what types of data are
        quoted.
        such as the delimiting character, quote character, or what types of data
        are quoted.
        are quoted.

        :returns: CSVelte\Flavor The metadata that the CSV format doesn't provide

    .. php:method:: removeQuotedStrings($data)

        Replaces all quoted columns with a blank string. I was using this method
        to prevent explode() from incorrectly splitting at delimiters and newlines
        within quotes when parsing a file. But this was before I wrote the
        replaceQuotedSpecialChars method which (at least to me) makes more sense.

        :param $data:
        :returns: string The input string with quoted strings removed

    .. php:method:: lickLineEndings()

        Examine the input source to determine which character(s) are being used
        as the end-of-line character

        :returns: char The end-of-line char for the input data

    .. php:method:: lickQuoteAndDelim()

        The best way to determine quote and delimiter characters is when columns
        are quoted, often you can seek out a pattern of delim, quote, stuff,
        quote, delim
        but this only works if you have quoted columns. If you don't you have to
        determine these characters some other way... (see lickDelimiter)

        :returns: array A two-row array containing quotechar, delimchar

    .. php:method:: lickDelimiter($eol = "\n")

        Take a list of likely delimiter characters and find the one that occurs
        the most consistent amount of times within the provided data.

        :param $eol:
        :returns: string One of four Flavor::QUOTING_* constants

    .. php:method:: lickQuotingStyle($data, $quote, $delim, $eol)

        Determine the "style" of data quoting. The CSV format, while having an RFC
        (https://tools.ietf.org/html/rfc4180), doesn't necessarily always conform
        to it. And it doesn't provide metadata such as the delimiting character,
        quote character, or what types of data are quoted. So this method makes a
        logical guess by finding which columns have been quoted (if any) and
        examining their data type. Most often, CSV files will only use quotes
        around columns that contain special characters such as the dilimiter,
        the quoting character, newlines, etc. (we refer to this style as )
        QUOTE_MINIMAL), but some quote all columns that contain nonnumeric data
        (QUOTE_NONNUMERIC). Then there are CSV files that quote all columns
        (QUOTE_ALL) and those that quote none (QUOTE_NONE).

        :param $data:
        :param $quote:
        :param $delim:
        :param $eol:
        :returns: string One of four "QUOTING_" constants defined above--see this method's description for more info.

    .. php:method:: unQuote($data)

        Remove quotes around a piece of text (if there are any)

        :param $data:
        :returns: string The data passed in, only with quotes stripped (off the edges)

    .. php:method:: isQuoted($data)

        Determine whether a particular string of data has quotes around it.

        :param $data:
        :returns: boolean Whether the data is quoted or not

    .. php:method:: lickDataType($data)

        Determine what type of data is contained within a variable
        Possible types:
        - nonnumeric - only numbers
        - special - contains characters that could potentially need to be quoted
        (possible delimiter characters)
        - unknown - everything else
        This method is really only used within the "lickQuotingStyle" method to
        help determine whether a particular column has been quoted due to it being
        nonnumeric or because it has some special character in it such as a
        delimiter
        or newline or quote.

        :param $data:
        :returns: string The type of data (one of the "DATA_" constants above)

    .. php:method:: replaceQuotedSpecialChars($data, $delim)

        Replace all instances of newlines and whatever character you specify (as
        the delimiter) that are contained within quoted text. The replacements are
        simply a special placeholder string. This is done so that I can use the
        very unsmart "explode" function and not have to worry about it exploding
        on delimiters or newlines within quotes. Once I have exploded, I typically
        sub back in the real characters before doing anything else. Although
        currently there is no dedicated method for doing so I just use str_replace

        :param $data:
        :param $delim:
        :returns: string The data with replacements performed

    .. php:method:: lickType($data)

        Determine the "type" of a particular string of data. Used for the
        lickHeader
        method to assign a type to each column to try to determine whether the
        first for is different than a consistent column type.

        :param $data:
        :returns: string One of the TYPE_ string constants above

    .. php:method:: lickHeader($data, $quote, $delim, $eol)

        Examines the contents of the CSV data to make a determination of whether
        or not it contains a header row. To make this determination, it creates
        an array of each column's (in each row)'s data type and length and then
        compares them. If all of the rows except the header look similar, it will
        return true. This is only a guess though. There is no programmatic way to
        determine 100% whether a CSV file has a header. The format does not
        provide metadata such as that.

        :param $data:
        :param $quote:
        :param $delim:
        :param $eol:
        :returns: boolean True if the data (most likely) contains a header row
