<?php
use PHPUnit\Framework\TestCase;
use CSVelte\Autoloader;
/**
 * CSVelte\Autoloader Tests
 *
 * @package   CSVelte Unit Tests
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class AutoloaderTest extends TestCase
{
    public function testAddPathsUsingConstructor()
    {
        $dir = __DIR__ . '/../../src';
        $paths = array(
            $dir
        );
        $auto = new Autoloader($paths);
        $realdir = realpath($dir);
        $this->assertContains($realdir, $auto->getPaths());
    }

    public function testAddPathsUsingAddPath()
    {
        $dir = __DIR__ . '/../../src';
        $auto = new Autoloader();
        $auto->addPath($dir);
        $realdir = realpath($dir);
        $this->assertContains($realdir, $auto->getPaths());
    }

    public function testNonExistantPathFailsQuietly()
    {
        $dir = __DIR__ . '/../../sourrc';
        $auto = new Autoloader();
        $auto->addPath($dir);
        $realdir = realpath($dir);
        $this->assertFalse($realdir);
        $this->assertContains($dir, $auto->getPaths());
    }

    public function testRegisterAddsPathsToIncludePath()
    {
        $dir = __DIR__ . '/../../src';
        $fakedir = '../../fakedir';
        $paths = array(
            $dir,
            $fakedir
        );
        $auto = new Autoloader($paths);
        $auto->register();
        $realdir = realpath($dir);
        $includepaths = explode(PATH_SEPARATOR, get_include_path());
        $this->assertContains($realdir, $includepaths);
        $this->assertContains($fakedir, $includepaths);
    }
}
