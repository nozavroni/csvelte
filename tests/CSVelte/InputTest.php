<?php

use PHPUnit\Framework\TestCase;
use CSVelte\Input\File;
use CSVelte\Input\Stream;
use CSVelte\Input\SeekableStream;

/**
 * CSVelte\Input Tests
 *
 * @package   CSVelte Unit Tests
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class InputTest extends TestCase
{
    protected $banklist;

    public function setUp()
    {
        $this->banklist = file_get_contents(__DIR__ . '/../files/banklist.csv');
    }

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

    public function testReadLineReturnsNextLineAndAdvancesPositionToBeginningOfNextLine()
    {
        list($line1, $line2, $line3, $therest) = explode($eol = "\n", $this->banklist, 4);
        $therest = str_replace("\n", "\r", $therest);
        list($line1oftherest, $therestoftherest) = explode("\r", $therest);
        $stream = new Stream('file://' . __DIR__ . '/../files/banklist.csv');
        $this->assertEquals($line1, $stream->readLine());
        // next call should pull line 2
        $this->assertEquals($line2, $stream->readLine());
        // set maximum line length
        $this->assertEquals(substr($line3, 0, 25), $stream->readLine(25));
        // now read the rest of the line
        $this->assertEquals(substr($line3, 25), $stream->readLine());
        // set line ending
        $this->assertEquals($line1oftherest, $stream->readLine(null, "\r"));
    }

    public function testMovePointer()
    {
        $stream = new SeekableStream('file://' . __DIR__ . '/../files/banklist.csv');
        $stream->read(125);
        //$this->assertTrue();
    }

    // public function testPopLineForPoppingHeaderMethodOrignoringLines()
    // {
    //     // the idea here is to hide a line from the reader
    //     $stream = new Stream('file://' . __DIR__ '/../files/banklist.csv');
    //     $line1 = $stream->popLine();
    //
    // }
}
