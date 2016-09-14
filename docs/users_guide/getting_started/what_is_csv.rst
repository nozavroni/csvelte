============
What is CSV?
============

It's highly unlikely that you would even be here reading about this library if you weren't already familiar with CSV in some capacity. But what defines :abbr:`CSV (Comma Separated Values)` as a format? Who invented it? What body governs its standardization? Where can one find a detailed specification that defines the format down to its most mundane detail? Unfortunately, I can't provide you with a satisfying answer to any of those questions. CSV is a very old format. It has been defined and redefined endlessly by any number of organizations and software products over the course of its over forty-year lifespan.

.. note::

    There is an RFC ( :rfc:`4180` ) that documents one very specific *flavor* of CSV, but few would dare call it the *definitive* CSV standard.

CSV as a format
---------------

Although CSV is an extremely widely-used format for importing/exporting data, its lack of a unified standard means CSV data out in the wild can vary substantially in its style and format. Exacerbating the problem, CSV (in all its flavors) lacks a standardized method for dictating metadata such as column type, character encoding, locale information such as language and date/time/currency formatting, etc. One can't even rely on a comma being the delimiter character within a CSV file and the name of the format is *comma*-separated values! This can make life very difficult for developers attempting to reliably output and/or consume CSV-formatted data.

CSV in General
--------------

For all the reasons I just mentioned, it isn't possible for me to define the CSV format in any specific way. I can only define its general properties. Basically, CSV is a human-readable data interchange format that represents tabular data. That is to say, it represents rows of fields where rows are separated by some form of line terminator character sequence (typically ``\n``, ``\r`` or ``\r\n``) and rows are separated by a character called the delimiter (generally this is a comma, but tabs, pipes and semi-colons are often used as well). The number of fields *should* be the same on every row, although this cannot be relied upon. The data may optionally contain a header row, dictating column header names, which should not be processed as a row of data, but rather as labels for each column *within* that data. Fields may contain the delimiter character and/or line breaks, but if they do, they should be enclosed by quotes. If a quoted field itself contains quotes, it is the general rule that it should be escaped by doubling it up. That is, replacing it with two consecutive quote characters.

Beyond that very general description, CSV files can vary substantially. Whitespace before or after a field is typically ignored, although there is no rule stating that it *must* (and in fact, :rfc:`4180` specifies that it *must not*). Blank lines are also typically ignored. Sometimes it's acceptable to use a backslash rather than an additional double quote to escape quote characters. Also, quote characters are generally not allowed unless they are escaped and the field containing them is itself enclosed by quotes.  Sometimes CSV files contain more than one header row. Sometimes they contain row titles as well as column titles. All of these little quirks are what make the CSV pseudo-format such a *pleasure* to work with (see sarcasm [#]_). This is why I wrote CSVelt--to handle as much of this inconsistency and silliness as possible so that you don't have to.

.. tip::

    For a much more reliable and well-informed history, as well as a more detailed and articulate description of the CSV psuedo-format and a wide selection of examples, I refer you to the Wikipedia entry for `comma-separated values`_.

An example
----------

Although I'm certain that you've seen CSV data before, I'll go ahead and show you an example to demonstrate, at the very least, the terminology mentioned above.

.. csv-table:: Contacts
   :header: "id", "lastname", "firstname", "misc",  "email"

   1,Visinoni,Luke,"A.K.A. ""The CSV Master""",luke.visinoni@gmail.com
   2,Jones,Davey,Loves the sea,djones@locker.io
   3,Kelly,Marge,"Marge, Margery, or Margo",margeincharge@mekelly.info
   4,Smith,John,,john.smith@yahoo.com
   5,Doe,Jane,Been missing a while,janeydoeyes@example.com

The contacts table above may be represented in CSV format as follows. It's *delimited* by the comma character, *lines are terminated* by the (usually invisible, but included here for clarity) line feed character, *quoted* by the double-quote character, and its quotes are escaped by doubling them up (two consecutive quote characters). It's perfectly acceptable to leave a field blank, as you can see for the "misc" field for "John Smith". Notice though, that a comma was included even though the field was blank. Also of note, fields are only quoted when they contain either the delimiter (comma), a line terminator character (``\n`` in this case), or a quote character. This is probably the most common behavior, although technically, any field may be quoted without adversely affecting any potential consuming script/program.

.. code-block:: csv
   :emphasize-lines: 1

    id,lastname,firstname,misc,email
    1,Visinoni,Luke,"A.K.A. ""The CSV Master""",luke.visinoni@gmail.com\n
    2,Jones,Davey,Loves the sea,djones@locker.io\n
    3,Kelly,Marge,"Marge, Margery, or Margo",margeincharge@mekelly.info\n
    4,Smith,John,,john.smith@yahoo.com\n
    5,Doe,Jane,Been missing a while,janeydoeyes@example.com\n

It isn't difficult to imagine how the preceeding CSV data could produce the table above. This is because, despite CSV's failings as a standard, it is meant to be human-readable as well as machine-readable. And it succeeds, for the most part, on both counts. Which is why it has stood the test of time and has been around for over forty years.

Will this be on the test?
-------------------------

Fortunately for you, CSVelte will abtract away a lot of these details and you won't have to deal with them. At least not directly. As long as you understand the basic characteristics that define CSV as a format (rows contain fields, fields contain data separated by a delimiter, etc.), you can pretty much forget the rest. In the vast majority of cases, CSVelte will provide sane defaults and/or automatically detect a CSV dataset's formatting parameters for you anyway. But we'll get to that later.

.. rubric:: Footnotes

.. [#] the use of irony to mock or to convey contempt -- Google.com definition for "sarcasm"
