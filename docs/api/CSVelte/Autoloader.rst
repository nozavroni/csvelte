-------------------
CSVelte\\Autoloader
-------------------

.. php:namespace: CSVelte

.. php:class:: Autoloader

    CSVelte Autoloader

    For those crazy silly people who aren't using Composer, simply include this file to have CSVelte's files auto-loaded by PHP.

    .. php:const:: NAMESPACE_SEPARATOR

    .. php:attr:: paths

        protected array

        An array of paths that will be searched when attempting to load a class

    .. php:method:: __construct($paths = array())

        Autoloader Constructor

        :param $paths:

    .. php:method:: addPath($path)

        Add path to list of search paths

        Attempts to deduce the absolute (real) path from the path specified by the
        $path argument. If successful, the absolute path is added to the search
        path list and the method returns true. If one can't be found, it adds
        $path to the search path list, as-is and returns false

        :param $path:
        :returns: boolean

    .. php:method:: getPaths()

        Retrieve search path list (array)

        Simply returns the array containing all the paths that will be searched
        when attempting to load a class.

        :returns: [type] [description]

    .. php:method:: register()

        Register the autoloader

        Registers this library's autoload function with the SPL-provided autoload
        queue. This allows for CSVelte's autoloader to work its magic without
        having to worry about interfering with any other autoloader.

        Also adds all of this class's search paths to PHP's include path.

        :returns: boolean Whatever the return value of spl_autoload_register is

    .. php:method:: load($className)

        Load a class

        This is the function (or method in this case) used to autoload all of
        CSVelte's classes. It need not be called directly, but rather regestered
        with the SPL's autoload queue using this class's register method.

        :param $className:
        :returns: boolean
