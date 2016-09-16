============
Installation
============

.. todo::

    **Issue #109**

    * Need to add ``CSVelte\Exception\DependencyException`` class
    * Need to implement a ``CSVelte::assertDependenciesAvailable()`` method (or something similar)
    * Need to document the use of aforementioned exception class
    * Need to add unit test(s) for aforementioned exception class (for when Carbon or other third-party class is referenced but doesn't exist)
    * Need to document the use of exceptions in CSVelte in general
    * Need to unit test exception use in CSVelte in general (assertion methods and the like)
    * Make sure Carbon.php is the only file CSVelte will need from Carbon
    * Do some research and find out how modern PHP library developers handle dependency management for direct download/non-composer users

I recommend that you install CSVelte using Composer_, PHP's de facto package manager. If you aren't using Composer, I highly recommend that you check it out. It makes dependency management ridiculously easy and it's used by virtually every modern PHP library and framework in use today. There are other ways to install the library (which I will outline below), but Composer is by far the cleanest and easiest. Not to mention, the most well-tested.

Requirements
------------

As of |release|, CSVelte requires |phpversion|. It was originally my goal to support PHP5.3 and up, but I ran into some unforeseen issues with development dependencies (namely, PHPUnit_) and in my haste to just release some code, I went ahead and upped the minimum version to |phpversion|. This will almost certainly be lowered before I release CSVelte v1.0. PHP7 is also supported, but at least for now, it isn't nearly as well tested as PHP5.

.. note::

    CSVelte does not currently require any PHP extensions, but internationalization and localization, as well as character transcoding are features that are on the :doc:`/roadmap`. These features (and probably others as well) will most likely require one or more of the mbstring_, iconv_, intl_ and possibly other extension(s) to be installed. So I can't promise the library won't require certain PHP extensions in the future.

.. todo::

    Put together a project roadmap document so that the above link doesn't land on a basically blank page.

Installation
------------

With Composer
^^^^^^^^^^^^^

If you've never used Composer_, you'll want to head over to getcomposer.org_ and follow the installation instructions on their `download page`_ first. Once Composer has been successfully installed, you may use the following command to install CSVelte within your Composer package/project. First ``cd`` into your project directory and then issue this command.

.. code-block:: bash

    $ php composer.phar require nozavroni/csvelte @dev-master

.. important::

    CSVelte is currently under heavy development. Once it reaches a stable version, it will be simply a matter of ``php composer.phar require nozavroni/csvelte``, but for the time being you will need the ``@dev-master`` flag or Composer_ will complain and refuse to install (or you can `lower your minimum-stability setting`_, which will have the same effect for *all* your project's dependencies).

Direct Download
^^^^^^^^^^^^^^^

.. danger::

    Unless you know what you're doing and/or you have a good reason not to, it's **highly** recommended that you install CSVelte using Composer_. It's virtually impossible for me to ensure you have the correct dependency(s) if you install manually, so in that case you're on your own.

.. todo::

    * Change convention for release tag names to include "CSVelte-" before the version number so that the download links don't look like the one above. So when I release v0.2 it should be tagged as "CSVelte-v0.2" or "csvelte-v0.2".
    * Play around with GitHub's release editor interface. It has some link that says "Attach binaries"... maybe I could include Carbon.php there?
    * Look into PSR-4 for autoloading. According to a book I was just reading, PSR-4 eliminates the need for me to register an autoload function. See what this is all about...
    * Look into the other PSRs and see if any of them might benefit you as well (after looking through them, PSR-7 and PSR-17 were both very interesting - see GitHub issue #107)

To install CSVelte manually, first download the `latest version of CSVelte`_ (currently |release|) from GitHub_. After extracting the contents of the zip or tarball, simply include the :file:`src/autoload.php` file, which will add the :file:`src` directory to PHP's include path [#]_ and register CSVelte's autoload function [#]_ for you (obviously you'll need to change ``/path/to/csvelte`` to wherever the :file:`src` directory is on your system).  That's it. Happy coding.

.. code-block:: php

    <?php
    require_once "/path/to/csvelte/src/autoload.php";

.. important::

    As of version |release|, CSVelte's only external dependency is Carbon_ [#]_. If you aren't using Composer_ to install CSVelte, you'll need to go to Carbon's website and follow its installation instructions to `install it manually`_ or CSVelte will complain about missing dependencies.

.. _download page: https://getcomposer.org/download/
.. _lower your minimum-stability setting: https://getcomposer.org/doc/04-schema.md#minimum-stability
.. _install it manually: http://carbon.nesbot.com/#nocomposer

.. rubric:: Footnotes

.. [#] See include_path_ ini setting on php.net_
.. [#] See spl_autoload_register_ function on php.net_
.. [#] Carbon_ is a very lightweight, flexible date/time library built on top of `PHP's native DateTime class(es)`_. You can find instructions on its home page to `install it manually`_ if you aren't using Composer_ to manage dependencies.
