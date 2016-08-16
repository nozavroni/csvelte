#####################################
CSVelte: Slender, elegant CSV for PHP
#####################################

CSVelte is a simple yet flexible CSV and tabular data library for PHP5.6+. It was written, originally, as a pseudo-port of Python's CSV module in early 2008. It was the result of the author's dissatisfaction with not only PHP's native CSV handling functions, but also with the lack of quality third-party PHP libraries for CSV and tabular data in general. Unfortunately, due to its author's personal and professional obligations, that original library (PHP CSV Utilities or PCU) was abandoned before it even reached API stability (the `last version was 0.3 <https://code.google.com/archive/p/php-csv-utils/>`_). Recently however, its author has had a bit of spare time and so decided to revive PCU, rewriting it from the ground up using modern PHP best practices and tools over a solid foundation of object-oriented design principles and re-introducing it to the world as CSVelte (pronounced just like the word `svelte <http://www.dictionary.com/browse/svelte>`_).

..  note::

    **Why the name CSVelte?**

    The library was originally called PHP CSV Utilities, which I always hated. So when I revived the project a month or so ago, I decided that if I was going to release a new version, I wanted a new name. I wanted the name to reflect the library's goal of being simple and elegant, while still being obvious that it is a CSV library. The word "svelte" means "slender and elegant". So I just added a C to the beginning of it and voila! I had my library name!

Features
========

Although eventually this library's scope will widen to include much of the `CSV on the Web Working Group <https://www.w3.org/2013/csvw/wiki/Main_Page>`_'s recommendations, for now (and probably until at least v1.0), its aim is to read and write CSV data in as simple and concise a manner as possible. It's object-oriented design relies on well-known design patterns and best practices so that it can remain flexible and easy to use regardless of what features I may add in the future. As of now, its feature set is limited.

    :doc:`reference/flavors`
        Due to CSV's lack of an established standard, numerous "flavors" or "dialects" of the format can be found "in the wild".
    :doc:`reference/tasting`
        Automatically detect the "flavor" of CSV data by inspecting a sample of it.
    :doc:`reference/reading`
        Read CSV data from local files, streams, or from a PHP string.
    :doc:`reference/writing`
        Write CSV data to local files or streams.

..  note::

    For a more detailed list of features, and to find out what kind of stuff is planned for later releases, check out the :doc:`roadmap`.

Getting Started
===============

Ready to get started using CSVelte? Check out the :doc:`getting_started/index` guide to get it installed and become acquainted with it.

..  toctree::
    :hidden:

    getting_started/index

.. include:: getting_started/map.rst.inc

API Documentation
=================

The API documentation is a thorough, detailed reference containing precisely what you need to know to use CSVelte and its classes. It details each function and class method's arguments, return types, etc.

You can find CSVelte's API documentation at http://phpcsv.com/csvelte/apidocs/

..  note::

    The API documentation is generated automatically by `ApiGen <http://www.apigen.org>`_ and reads as such. For more detailed, human-friendly documentation, take a look at the reference guides and tutorials sections.

Reference Guides
================

The reference guides are somewhere between API docs and tutorials. They are essentially like the API documentation, only with more prose describing the problems each class addresses and how they solve them. Eventually I would like to include user comments in this section but that will have to wait until a later version.

..  toctree::
    :hidden:

    reference/index

..  include:: reference/map.rst.inc

Tutorials
=========

Tutorials address a specific problem and walk you through each step of its solution using CSVelte, explaining in detail exactly how its classes work. To learn how to solve a particular problem and truly understand what each class is doing, the tutorials are going to be your greatest resource.

..  toctree::
    :hidden:

    tutorials/index

..  include:: reference/map.rst.inc

..  note::

	This library is still very much in its infancy and its API is subject to change, even break backwards compatibility, at any time until it reaches version 1.0. After that, its API can be relied upon to remain stable and not break backwards compatibility until the next major version change (v2.x, v3.x, etc.)
