<?php
use PHPUnit\Framework\TestCase;
use CSVelte\Output\Stream;
use CSVelte\Output\File;
use org\bovigo\vfs\vfsStream;
/**
 * CSVelte\Writer Tests
 *
 * @package   CSVelte Unit Tests
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class WritableTest extends TestCase
{
    protected $fs;

    public function setUp()
    {
        $root = vfsStream::setup('home');
        $this->fs = $root;
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
}
