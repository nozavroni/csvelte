<?php
/**
 * CSVelte
 * Slender, elegant CSV for PHP5.3+
 *
 * @version v0.1
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author Luke Visinoni <luke.visinoni@gmail.com>
 * @license See LICENSE file
 */
namespace CSVelte;
/**
 * CSVelte Autoloader
 *
 * For those crazy silly people who aren't using Composer, simply include this
 * file to have CSVelte's files auto-loaded by PHP.
 *
 * @package CSVelte
 * @subpackage Autoloader
 * @since v0.1
 */
class Autoloader
{
    /**
     * @var string Constant for namespace separator
     */
    const NAMESPACE_SEPARATOR = '\\';

    /**
     * Paths to search for classes in
     * @var array
     */
    protected $paths;

    /**
     * Autoloader Constructor
     */
    public function __construct()
    {
        $this->paths = explode(PATH_SEPARATOR, get_include_path());
    }

    /**
     * Add path to list of paths to search
     * @param string $path A path to add to the list of search paths
     */
    public function addPath($path)
    {
        if ($rp = realpath($path)) {
            $this->paths[] = $rp;
        }
        // @todo throw exception?
        return false;
    }

    /**
     * Register this autoloader
     * @return boolean Whatever the return value of spl_autoload_register is
     */
    public function register()
    {
        return spl_autoload_register(array($this, 'load'));
    }

    /**
     * Load a class
     * @param  string $className The fully qualified class name to Load
     * @return boolean            Success or failure
     */
    public function load($className)
    {
        if(class_exists($className)) {
            return true;
        }
        $fqcp = str_replace(self::NAMESPACE_SEPARATOR, DIRECTORY_SEPARATOR, $className);
        foreach ($this->paths as $path) {
            $classPath = $path . DIRECTORY_SEPARATOR . $fqcp . '.php';
            if(file_exists($classPath) && is_readable($classPath)) {
                require($classPath);
                return true;
            }
        }
        return false;
    }
}

/**
 * Add this file's parent directory to list of search paths and register autoloader
 * @var Autoloader
 */
$autoloader = new Autoloader();
$autoloader->addPath(__DIR__ . '/../');
$autoloader->register();
