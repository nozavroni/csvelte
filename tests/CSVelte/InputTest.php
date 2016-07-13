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
        $file = new File(__DIR__ . '/../files/banklist.csv');
        $this->assertEquals($expectedFilename = 'banklist.csv', $file->name());
    }

    /**
     * @expectedException CSVelte\Exception\InvalidStreamUriException
     */
    public function testInvalidStreamUriThrowsException()
    {
        $stream = new Stream('pickles://iloveham.poo');
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

    public function testSeekToPosition()
    {
        $stream = new SeekableStream('file://' . __DIR__ . '/../files/banklist.csv');
        $stream->read(125);
        $this->assertEquals($expected = 125, $stream->position());
        $stream->seek(10);
        $this->assertEquals($expected = 10, $stream->position());
        $this->assertEquals($expected = "City,ST,CERT,Acquiring In", $stream->read(25));
    }

    public function testChainingMethods()
    {
        $this->assertEquals($expected = "City,ST,CERT,Acquiring In", with(new SeekableStream('file://' . __DIR__ . '/../files/banklist.csv'))->seek(10)->read(25));
    }

    public function testIsEof()
    {
        $filename = realpath(__DIR__ . '/../files/SampleCSVFile_2kb.csv');
        $size = filesize($filename);
        $stream = new SeekableStream('file://' . $filename);
        $this->assertFalse($stream->isEof());
        $read = $stream->read($size+1);
        $this->assertTrue($stream->isEof());
        $stream->seek(0);
        $this->assertFalse($stream->isEof());
        $stream->seek(25);
        $this->assertFalse($stream->isEof());
        $stream->read(100);
        $this->assertFalse($stream->isEof());
        $stream->read($size);
        $this->assertTrue($stream->isEof());
    }

    public function testRewind()
    {
        $filename = realpath(__DIR__ . '/../files/SampleCSVFile_2kb.csv');
        $stream = new Stream($filename);
        $first150 = $stream->read(150);
        $this->assertEquals($expected = "c", $stream->read(1));
        $stream->rewind();
        $this->assertEquals($expected = 0, $stream->position());
        $this->assertEquals($first150, $stream->read(150));
    }

    /**
     * 3xpectedException CSVelte\Exception\
     */
    public function testCloseStreamResourceManually()
    {
        $filename = realpath(__DIR__ . '/../files/banklist.csv');
        $stream = new Stream($filename);
        // should be able to read from stream because it's open
        $this->assertEquals($expected = "Bank Name,City,ST,CERT,Acquiring Institution,Closing Date,Updated Date\r\nFirst CornerStone Bank,King ", $actual = $stream->read(100));
        $this->assertTrue(is_resource($stream->getStreamResource()));
        $this->assertTrue($stream->close());
        $this->assertFalse($stream->close());
        // trying to read from a stream that has been closed should trigger an exception
        //$stream->read(100);
    }

    // public function testStreamCanAcceptStreamResourceInConstructor()
    // {
    //
    // }

    /**
     * 3xpectedException CSVelte\Exception\
     */
    // public function testCloseStreamResourceInDestructor()
    // {
    //
    // }

    /**
     * @expectedException CSVelte\Exception\
     */
    // public function testReadTriggersExceptionOnceStreamHasBeenClosed()
    // {
    //     $filename = realpath(__DIR__ . '/../files/banklist.csv');
    //     $stream = new Stream($filename);
    //     // should be able to read from stream because it's open
    //     $this->assertEquals($expected = "Bank Name,City,ST,CERT,Acquiring Institution,Closing Date,Updated Date\r\nFirst CornerStone Bank,King ", $actual = $stream->read(100));
    //     $this->assertTrue(is_resource($stream->getStreamResource()));
    //     $this->assertTrue($stream->close());
    //     // trying to read from a stream that has been closed should trigger an exception
    //     $stream->read(100);
    // }
}
