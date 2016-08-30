<?php
namespace CSVelteTest;

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;

/**
 * Base Unit Test for CSVelte
 *
 * @package   CSVelte Unit Tests
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
abstract class UnitTestCase extends TestCase
{
    protected $root;

    public function setUp()
    {
        $this->root = vfsStream::setup();
    }

    public function tearDown()
    {
        // Do I need to destroy anything for vfsStream?
    }
}
