<?php

/*
 * CSVelte: Slender, elegant CSV for PHP
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV
 * standardization efforts, CSVelte was written in an effort to take all the
 * suck out of working with CSV.
 *
 * @version   v0.2.3
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace CSVelte;

/**
 * CSVelte Autoloader.
 *
 * For those crazy silly people who aren't using Composer, simply include this
 * file to have CSVelte's files auto-loaded by PHP.
 *
 * @package CSVelte
 * @subpackage Autoloader
 *
 * @since v0.1
 */
class Autoloader
{
    /**
     * @var string Constant for namespace separator
     */
    const NAMESPACE_SEPARATOR = '\\';

    /**
     * An array of paths that will be searched when attempting to load a class.
     *
     * @var array
     */
    protected $paths;

    /**
     * Autoloader Constructor.
     *
     * @param array $paths Paths to search for classes
     */
    public function __construct($paths = [])
    {
        $this->paths = explode(PATH_SEPARATOR, get_include_path());
        foreach ($paths as $path) {
            $this->addPath($path);
        }
    }

    /**
     * Add path to list of search paths.
     *
     * Attempts to deduce the absolute (real) path from the path specified by the
     * $path argument. If successful, the absolute path is added to the search
     * path list and the method returns true. If one can't be found, it adds $path
     * to the search path list, as-is and returns false
     *
     * @param string $path A path to add to the list of search paths
     *
     * @return bool
     */
    public function addPath($path)
    {
        $paths = $this->getPaths();
        if ($rp = realpath($path)) {
            if (in_array($rp, $paths)) {
                return true;
            }
            $this->paths []= $rp;

            return true;
        }
        $this->paths []= $path;

        return false;
    }

    /**
     * Retrieve search path list (array).
     *
     * Simply returns the array containing all the paths that will be searched
     * when attempting to load a class.
     *
     * @return array An array of paths to search for classes
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * Register the autoloader.
     *
     * Registers this library's autoload function with the SPL-provided autoload
     * queue. This allows for CSVelte's autoloader to work its magic without
     * having to worry about interfering with any other autoloader.
     *
     * Also adds all of this class's search paths to PHP's include path.
     *
     * @return bool Whatever the return value of spl_autoload_register is
     *
     * @see spl_autoload_register
     */
    public function register()
    {
        set_include_path(implode(PATH_SEPARATOR, $this->getPaths()));
        spl_autoload_register([$this, 'load']);
    }

    /**
     * Load a class.
     *
     * This is the function (or method in this case) used to autoload all of
     * CSVelte's classes. It need not be called directly, but rather regestered
     * with the SPL's autoload queue using this class's register method.
     *
     * @param string $className The fully qualified class name to load
     *
     * @return bool
     *
     * @see Autoloader::register()
     */
    public function load($className)
    {
        if (class_exists($className)) {
            return;
        }
        $fqcp  = str_replace(self::NAMESPACE_SEPARATOR, DIRECTORY_SEPARATOR, $className);
        $paths = $this->getPaths();
        foreach ($paths as $path) {
            $classPath = $path . DIRECTORY_SEPARATOR . $fqcp . '.php';
            if (file_exists($classPath) && is_readable($classPath)) {
                require_once($classPath);

                return;
            }
        }
    }
}
