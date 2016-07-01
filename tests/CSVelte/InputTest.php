<?php

use PHPUnit\Framework\TestCase;
use CSVelte\Input\File;
use CSVelte\Input\Stream;

/**
 * CSVelte\Input Tests
 *
 * @package   CSVelte Unit Tests
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class InputTest extends TestCase
{
    public function testCreateNewFile()
    {
        //$file = new File(__DIR__ . '/../files/banklist.csv');
        //$this->assertEquals($expectedFilename = 'banklist.csv', $file->name());
    }

    public function testStreamInfoMethods()
    {
        $banklist = file_get_contents(__DIR__ . '/../files/banklist.csv');
        $stream = new Stream($path = 'file://' . __DIR__ . '/../files/banklist.csv');
        $this->assertEquals($expectedName = 'banklist.csv', $stream->name());
        // $this->assertEquals($expectedPath = realpath(__DIR__ . '/../files'), $stream->path());
        $this->assertEquals($expectedPath = dirname($path), $stream->path());
    }

    public function testStreamReadSpecifiedNumberOfCharacters()
    {
        $banklist = file_get_contents(__DIR__ . '/../files/banklist.csv');
        $stream = new Stream('file://' . realpath(__DIR__ . '/../files/banklist.csv'));
        $this->assertEquals($expectedFirst100 = substr($banklist, 0, 100), $stream->read(100));
        // now make sure it picks up from where it left off...
        $this->assertEquals($expectedNext50 = substr($banklist, 100, 50), $stream->read(50));
    }

    public function testStreamSupportsComplexStreamNames()
    {
        $upper = fopen($streamName = 'php://filter/read=string.toupper/resource=file://' . realpath(__DIR__ . '/../files/banklist.csv'), 'r+');
        $stream = new Stream($streamName);
        $this->assertEquals($expected = fread($upper, 100), $stream->read(100));
    }
}
