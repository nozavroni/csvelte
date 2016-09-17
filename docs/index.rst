%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
CSVelte: A slender, elegant CSV library for PHP
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

.. image:: https://travis-ci.org/nozavroni/csvelte.svg?branch=master
   :target: https://travis-ci.org/nozavroni/csvelte

.. image:: https://scrutinizer-ci.com/g/nozavroni/csvelte/badges/quality-score.png?b=master
   :target: https://scrutinizer-ci.com/g/nozavroni/csvelte

.. image:: https://coveralls.io/repos/github/nozavroni/csvelte/badge.svg?branch=master
   :target: https://coveralls.io/github/nozavroni/csvelte?branch=master

.. image:: https://readthedocs.org/projects/csvelte-for-php/badge/?version=latest
   :target: http://csvelte.phpcsv.com/en/latest/?badge=latest

.. image:: https://camo.githubusercontent.com/fe3fa7b3b718009ed4062e984eda6c923a2cda01/68747470733a2f2f706f7365722e707567782e6f72672f6e6f7a6176726f6e692f637376656c74652f6c6963656e7365
   :target: https://packagist.org/packages/nozavroni/csvelte

CSVelte is a simple yet flexible CSV and tabular data library for |phpversion|. It was originally written as a pseudo-port of `Python's CSV module`_ in early 2008 and was called :abbr:`PCU (PHP CSV Utilities)`. Unfortunately my time was very limited and after only version 0.3, PCU went dormant. Fast forward to eight years later and I come across my own library in an unrelated Google search. Surprisingly, PCU had gained and then lost, a somewhat respectable user base. So I revived the project, rewrote it from the ground up using solid object-oriented design principles, keeping only the most basic concepts, and renamed it CSVelte (pronounced just like the word svelte_).

.. note::

    **Why the name CSVelte?**

    The library was originally called `PHP CSV Utilities`_, which I never particularly liked. So when I revived the project, I decided that if I was going to release a new version, I wanted a new name. I wanted the name to reflect the library's goal of being simple and elegant and still have :abbr:`CSV (comma-separated values)` in the name. The word "svelte" means "slender and elegant". So I just added a "C" to the beginning of it and a slender and elegant CSV library was born!

User Documentation
==================

CSVelte's user documentation is organized into three main sections, as outlined below. If you're new to CSVelte, I recommend that you begin here: :doc:`users_guide/getting_started/index`.

User's Guide
------------

.. include:: /users_guide/summary.rst.inc

.. toctree::
   :maxdepth: 2

   users_guide/index

API Documentation
-----------------

.. include:: api_docs/summary.rst.inc

.. toctree::
   :maxdepth: 1

   api_docs/index

Tutorials
---------

.. include:: tutorials/summary.rst.inc

.. toctree::
   :maxdepth: 2

   tutorials/index

.. todolist::
