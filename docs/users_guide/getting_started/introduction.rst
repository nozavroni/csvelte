============
Introduction
============

CSVelte is a modern, object-oriented :abbr:`CSV (Comma Separated Values)` library for |phpversion|. Its goal is to take the typically tedious, error-prone task of processing and manipulating CSV data, and make it as simple and easy as possible.

A little history
----------------

CSVelte's story actually begins all the way back in 2008. I had just begun to feel confident as a PHP developer and wanted to start my own open source PHP library. I didn't want to bite off more than I could chew, so I chose something I felt would be relatively easy. Something I've been working with since the first time I wrote a line of code. I did a quick Google search and found that there really weren't any proper object-oriented CSV libraries for PHP (at that time), so I set to work writing `PHP CSV Utilities`_ (or PCU). Over the next six months or so, I pumped out version 0.1, then 0.2, and then at 0.3 things just stalled. I lost interest. Found other things to do. And PCU faded into obscurity.

Fast forward to eight years later and I'm in a similar position as I was in 2008. It's been a little while since I contributed anything open source. Not to mention I currently have an actual *need* for a CSV library, so I stripped PCU down to its core concepts, rewrote it from the ground up, and renamed it CSVelte (pronounced exactly like the word, svelte_ [#]_).

..  note::

    **Why the name CSVelte?**

    The library was originally called `PHP CSV Utilities`_, which I never particularly liked. So when I revived the project, I decided that if I was going to release a new version, I wanted a new name. I wanted the name to reflect the library's goal of being simple and elegant and still have :abbr:`CSV (comma-separated values)` in the name. The word "svelte" means "slender and elegant". So I just added a "C" to the beginning of it and a slender and elegant CSV library was born!

Library scope
-------------

So far, the scope of this library has been limited to the basic reading and writing of CSV-formatted data. It also has some format-detection features and a few other goodies. In the not-too-distant future however, I intend to widen that scope considerably to encompass the aggregation, manipulation, and import/exportation of tabular data in general.

There has been a lot of work done in the last several years by various standardization bodies, organizations, and interested individuals, to improve the wild-west nature of the CSV format. Of particular note is the W3C_'s `CSV on the Web Working Group`_ [#]_ and the work they've put into what they're calling "CSVW_", a series of specs and recommendations aimed at improving interoperability between CSV and other tabular data related formats on the web. It is my intention to implement much, if not all of their recommendations by the time CSVelte reaches v1.0.

.. rubric:: Footnotes

.. [#] (of a person) slender and elegant -- Google.com definition for "svelte_"
.. [#] The `CSV on the Web Working Group`_ is a W3C_ chartered group of individuals and organizations working towards improving CSV interoperability on the web
