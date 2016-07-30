<?php namespace CSVelte;
/**
 * CSVelte Autoloader
 * For those crazy silly people who aren't using Composer, simply include this
 * file to have CSVelte's files auto-loaded by PHP.
 *
 * @package   CSVelte
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   MIT License
 */

/**
 * CSVelte Autoloader
 * For those crazy silly people who aren't using Composer, simply include this
 * file to have CSVelte's files auto-loaded by PHP.
 *
 * @package   CSVelte
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   MIT License
 */
class Autoloader
{
    const NAMESPACE_SEPARATOR = '\\';

    protected $paths;

    public function __construct()
    {
        $this->paths = explode(PATH_SEPARATOR, get_include_path());
    }

    public function addPath($path)
    {
        if ($rp = realpath($path)) {
            $this->paths[] = $rp;
        }
        // @todo throw exception?
        return false;
    }

    public function register()
    {
        return spl_autoload_register(array($this, 'load'));
    }

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

$autoloader = new Autoloader();
$autoloader->addPath(__DIR__ . '/../');
$autoloader->register();
