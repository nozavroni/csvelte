============
Installation
============

.. todo::

    **Issue #109**

    * Need to document the use of exceptions in CSVelte in general
    * Need to unit test exception use in CSVelte in general (assertion methods and the like)
    * Do some research and find out how modern PHP library developers handle dependency management for direct download/non-composer users

I recommend that you install CSVelte using Composer_, PHP's de facto package manager. If you aren't using Composer, I highly recommend that you check it out. It makes dependency management ridiculously easy and it's used by virtually every modern PHP library and framework in use today. There are other ways to install the library (which I will outline below), but Composer is by far the cleanest and easiest. Not to mention, the most well-tested.

Requirements
------------

As of |release|, CSVelte requires |phpversion|. It is my intention to maintain |phpversion| support at least until its end-of-life (until it no longer receives updates and security fixes).

.. note::

    CSVelte does not currently require any PHP extensions, but internationalization and localization, as well as character transcoding are features that are on the :doc:`/roadmap`. These features (and probably others as well) will most likely require one or more of the mbstring_, iconv_, intl_ and possibly other extension(s) to be installed. So I can't promise the library won't require certain PHP extensions in the future.

Installation
------------

With Composer
^^^^^^^^^^^^^

If you've never used Composer_, you'll want to head over to getcomposer.org_ and follow the installation instructions on their `download page`_ first. Once Composer has been successfully installed, you may use the following command to install CSVelte within your Composer package/project. First ``cd`` into your project directory and then issue this command.

.. code-block:: bash

    $ composer require nozavroni/csvelte dev-master

.. important::

    CSVelte is currently under heavy development. Once it reaches a stable version, it will be simply a matter of ``composer require nozavroni/csvelte``, but for the time being you will need the ``dev-master`` flag or Composer_ will complain and refuse to install (or you can `lower your minimum-stability setting`_, which will have the same effect for *all* your project's dependencies).

Direct Download
^^^^^^^^^^^^^^^

.. danger::

    Unless you know what you're doing and/or you have a good reason not to, it's **highly** recommended that you install CSVelte using Composer_. It isn't particularly difficult to install without Composer, it just allows me to ensure everybody has a consistent installation experience and as a result, you will find it much easier to get help (at least from me) if you install using Composer.

.. todo::

    * Change convention for release tag names to include "CSVelte-" before the version number so that the download links don't look like the one above. So when I release v0.2 it should be tagged as "CSVelte-v0.2" or "csvelte-v0.2".
    * Look into PSR-4 for autoloading. According to a book I was just reading, PSR-4 eliminates the need for me to register an autoload function. See what this is all about...
    * Look into the other PSRs and see if any of them might benefit you as well (after looking through them, PSR-7 and PSR-17 were both very interesting - see GitHub issue #107)

To install CSVelte manually, first download the `latest version of CSVelte`_ (currently |release|) from GitHub_. After extracting the contents of the zip or tarball, simply include the :file:`src/autoload.php` file, which will add the :file:`src` directory to PHP's include path [#]_ and register CSVelte's autoload function [#]_ for you (obviously you'll need to change ``/path/to/csvelte`` to wherever the :file:`src` directory is on your system).

.. code-block:: php

    <?php
    require_once "/path/to/csvelte/src/autoload.php";

.. note::

    It *should* be as simple as that, but I can't promise it will be. Your mileage may vary. If you're unable to get CSVelte installed, try sending a message to the official `mailing list`_. And if that fails, you can try e-mailing me directly at csvelte@phpcsv.com and I will do my best to get back to you as soon as possible (just keep in mind I'm a busy guy and this is free software).

.. _download page: https://getcomposer.org/download/
.. _lower your minimum-stability setting: https://getcomposer.org/doc/04-schema.md#minimum-stability

.. rubric:: Footnotes

.. [#] See include_path_ ini setting on php.net_
.. [#] See spl_autoload_register_ function on php.net_
