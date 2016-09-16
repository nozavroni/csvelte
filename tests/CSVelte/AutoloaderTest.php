<?php
namespace CSVelteTest;

use CSVelte\Autoloader;

/**
 * CSVelte\Autoloader Tests
 *
 * @package   CSVelte Unit Tests
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class AutoloaderTest extends UnitTestCase
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

    public function testLoadClassReturnsNullIfClassExists()
    {
        $auto = new Autoloader;
        $this->assertNull($auto->load('CSVelte\Utils'));
    }

    public function testLoadClassReturnsNullIfClassDoesntExistAtAll()
    {
        $auto = new Autoloader;
        $this->assertNull($auto->load('CSVelte\Foo'));
    }

    public function testLoadClassLoadsClassIfItHasntBeenLoaded()
    {
        $auto = new Autoloader;
        $this->assertNull($auto->load($classname = 'CSVelte\Table\Row'));
        $this->assertTrue(class_exists($classname));
    }

    public function testRequireSrcAutoloadClass()
    {
        // set include path to something meaningless first, just to make sure that
        // including the autoload.php class fixes things...
        $meaningless = __DIR__;
        set_include_path($meaningless);
        $this->assertEquals($meaningless, get_include_path(), "Just a control test to make sure that include path was messed up first");
        require_once $meaningless . "/../../src/autoload.php";
        $this->assertEquals($meaningless . PATH_SEPARATOR . realpath(__DIR__ . '/../../src'), get_include_path(), "Test that including the autoload file adds CSVelte's src directory to the include path");
    }
}
