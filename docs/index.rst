%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
CSVelte: A slender, elegant CSV library for PHP
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

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
