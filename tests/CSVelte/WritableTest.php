<?php

use CSVelte\Output\File;
use CSVelte\Output\Stream;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

/**
 * CSVelte\Writer Tests.
 *
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class WritableTest extends TestCase
{
    protected $fs;

    protected $tmpdir;

    public function setUp()
    {
        $root = vfsStream::setup('home');
        $this->fs = $root;

        if (!is_dir($this->tmpdir = realpath(__DIR__.'/../files').'/temp')) {
            if (!mkdir($this->tmpdir, 0755)) {
                throw new \Exception('Cannot create temp dir');
            }
        }
    }

    public function tearDown()
    {
        @unlink(realpath(__DIR__.'/../files/temp/deleteme.csv'));
        @rmdir(realpath(__DIR__.'/../files/temp'));
    }

    public function testWriteStream()
    {
        $stream = new Stream($url = 'php://memory');
        $data = "I,love,cake!\r\n";
        $this->assertEquals(strlen($data), $stream->write($data));
    }

    public function testWriteFile()
    {
        $filename = vfsStream::url('home/test.txt');
        $file = new File($filename);
        $data = "This,is,some,data\r\n";
        $this->assertEquals(strlen($data), $file->write($data));
        $this->assertEquals(file_get_contents($filename), $data);
    }

    public function testCreateFileIfDoesntExist()
    {
        $filename = $this->tmpdir.'/deleteme.csv';
        $file = new File($filename);
        $data = "this,is,some,data\r\nand,this,is,more\r\nand,so,is,this\r\n";
        $this->assertEquals(strlen($data), $file->write($data));
    }

    /**
     * @expectedException \Exception
     */
    public function testInstantiateInvalidFileThrowsException()
    {
        $file = new File('./foo/bar.biz');
    }
}
