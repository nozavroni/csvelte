############
What is CSV?
############

It's highly unlikely that you would even be here reading about this library if you weren't already aware of what CSV is. But what defines :abbr:`CSV (Comma Separated Values)` as a format? Who invented it? What body governs its standardization? Where can one find an RFC specifying the format down to the most mundane detail? The short answer, to all of these questions, is nothing/nobody/there really isn't one.*

.. note::

    There is an RFC ( :rfc:`4180` ) that documents one very specific flavor of CSV, but few would dare call it the *definitive* CSV standard.

CSV as a format
===============

Although CSV is an extremely widely-used format for importing/exporting data, its lack of a unified standard means CSV data out in the wild can vary substantially in its style and format. Exacerbating the problem, CSV (in all its forms) lacks a standardized method for dictating metadata such as column type, character encoding, locale information such as language and date/time/currency formatting, etc. One can't even rely on a comma being the delimiter character within a CSV file and the name of the format is Comma Separated Values! This can make life very difficult for developers attempting to reliably output and/or consume CSV-formatted data.

CSV in General
--------------

For all the reasons I just mentioned, it isn't possible to define the CSV format in any specific way. I can only define its general properties. Basically, CSV is a format that represents tabular data. That is to say, it represents rows of fields where rows are separated by some form of line terminator character sequence and rows are separated by a character called the delimiter (generally this is a comma, but tabs, pipes and semi-colons are often used as well). The number of fields should be the same on every row. The data may optionally contain a header row, dictating column header names, which should not be processed as a row of data, but rather as labels for each column within said data.
