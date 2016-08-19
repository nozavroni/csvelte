############
Installation
############

I recommended that you install CSVelte using `Composer <https://www.getcomposer.org>`_, PHP's de facto package manager. If you aren't using Composer, I highly recommend that you check it out. It makes dependency management ridiculously easy and it's used by virtually every modern PHP library and framework in use today. There are other ways to install the library (which I will outline below), but Composer is by far the cleanest and easiest.

Requirements
------------

As of right now, CSVelte requires PHP5.6. It was originally my goal to support PHP5.3 and up, but I ran into some unforeseen issues with development dependencies (namely, PHPUnit) and in my haste to just release some code, I went ahead and upped the minimum PHP version to 5.6. This will almost certainly be lowered to PHP5.4 or possibly PHP5.5 before I release CSVelte v1.0. It hasn't been tested against PHP7, but I intend to support that version as well (eventually).

..  note::

    CSVelte does not currently require any PHP extensions, but internationalization and localization, as well as automatically fixing jarbled characters are features that are on the :doc:`/roadmap`. These features (and probably others as well) will most likely require one or more of the `mbstring <http://php.net/manual/en/book.mbstring.php>`_, `iconv <http://php.net/manual/en/book.iconv.php>`_, `intl <http://php.net/manual/en/book.intl.php>`_ and possibly other extension(s) to be installed. So I can't promise the library won't require certain PHP extensions in the future.

Installation
------------

With Composer
^^^^^^^^^^^^^

If you've never used Composer, you'll want to head over to `getcomposer.org <https://www.getcomposer.org>`_ and follow the installation instructions on their `download page <https://getcomposer.org/download/>`_ first. Once Composer has been successfully installed, you may use the following command to install CSVelte within your Composer package/project.

.. code-block:: bash

    $ php composer.phar require nozavroni/csvelte @dev-master

.. warning::

    CSVelte is currently under heavy development. Once it reaches a stable version, it will be simply a matter of ``composer require nozavroni/csvelte`` but for the time being you will need the ``@dev-master`` flag or Composer will complain and refuse to install (or you can `lower your minimum-stability setting <https://getcomposer.org/doc/04-schema.md#minimum-stability>`_, which will have the same effect for *all* dependencies).

Direct Download
^^^^^^^^^^^^^^^

If you're not using Composer, you should be. But rather than nag at you, I'll just tell you how to install CSVelte without it. First, download the `latest version of CSVelte <https://github.com/deni-zen/csvelte/releases>`_ from `Github <https://github.com>`_. After extracting the contents of the zip or tarball, you simply include the autoloader file, which will add the ``src`` directory to PHP's include path and register CSVelte's autoload function for you. That's it. Happy coding.

.. code-block:: php

    <?php
    require_once "/path/to/CSVelte/src/Autoloader.php";
