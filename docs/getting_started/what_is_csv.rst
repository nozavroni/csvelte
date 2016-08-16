############
What is CSV?
############

It's highly unlikely that you would even be here reading about this library if you weren't already aware of what CSV is. But what defines :abbr:`CSV (Comma Separated Values)` as a format? Who invented it? What body governs its standardization? Where can one find an RFC specifying the format down to the most mundane detail? The short answer, to all of these questions, is nothing/nobody/there really isn't one.*

.. note::

    There is an RFC ( :rfc:`4180` ) that documents one very specific flavor of CSV, but few would dare call it the *definitive* CSV standard.

CSV as a format
===============

Although CSV is an extremely widely-used format for importing/exporting data, its lack of a unified standard means CSV data out in the wild can vary substantially in its style and format. Exacerbating the problem, CSV (in all its forms) lacks a standardized method for dictating metadata such as column type, character encoding, locale information such as language and date/time/currency formatting, etc. One can't even rely on a comma being the delimiter character within a CSV file and the name of the format is Comma Separated Values! This can make life very difficult for developers attempting to reliably output and/or consume CSV-formatted data.
