<?php
namespace CSVelteTest\IO;

use CSVelte\IO\Stream;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\visitor\vfsStreamPrintVisitor;
/**
 * CSVelte\IO\Stream Tests.
 * This tests the new IO\Stream class that will be replacing CSVelte\Input\Stream
 * and CSVelte\Output\Stream
 *
 * @package   CSVelte Unit Tests
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @coversDefaultClass CSVelte\IO\Stream
 */
class StreamTest extends IOTest
{

    public function testInstantiateIOStreamAcceptsStreamResource()
    {
        $resource = fopen($this->getFilePathFor('veryShort'), 'r+b');
        $stream = new Stream($resource);
        $this->assertSame($resource, $stream->getResource());
    }

    /**
     * @covers ::__construct()
     */
    public function testInstantiateIOStreamAcceptsStreamURI()
    {
        $stream = new Stream($this->getFilePathFor('veryShort'));
        $res = $stream->getResource();
        $this->assertTrue(is_resource($res));
        $this->assertEquals("stream", get_resource_type($res));
    }

    /**
     * @covers ::__construct()
     * @expectedException \InvalidArgumentException
     */
    public function testInstantiateWithContextNotArrayThrowsException()
    {
        $stream = new Stream('php://input', [
            'context' => 'not an array'
        ]);
    }

    // /**
    //  * @covers ::__construct()
    //  */
    // public function testInstantiateStreamWithContextOptionsAndStringURI()
    // {
    //     $data = 'SomeData';
    //     $stream = new Stream('http://www.example.com/', [
    //         'open_mode' => 'rb',
    //         'context' => [
    //             'http' => [
    //                 'method' => 'POST',
    //                 'header' => 'Content-Type: application/x-www-form-urlencoded',
    //                 'content' => $data
    //             ]
    //         ]
    //     ]);
    //     dd($stream->getResource());
    // }

    /**
     * @expectedException CSVelte\Exception\InvalidStreamException
     * @expectedExceptionCode 1
     */
    public function testInstantiateThrowsExceptionIfInvalidStreamURI()
    {
        $stream = new Stream('foo');
    }

    /**
     * @expectedException CSVelte\Exception\InvalidStreamException
     * @expectedExceptionCode 2
     */
    public function testInstantiateThrowsExceptionIfInvalidStreamResource()
    {
        $stream = new Stream(new \stdClass());
    }

    // I made close() protected because I dont really want userland to call it
    // public function testCloseKillsConnection()
    // {
    //     $res = fopen($this->getFilePathFor('veryShort'), 'r+b');
    //     $stream = new Stream($res);
    //     $this->assertEquals("stream", get_resource_type($stream->getResource()));
    //     $this->assertEquals("stream", get_resource_type($res));
    //     $stream->close();
    //     $this->assertNotEquals("stream", get_resource_type($stream->getResource()));
    //     $this->assertNotEquals("stream", get_resource_type($res));
    // }

    public function testDestructKillsConnection()
    {
        $res = fopen($this->getFilePathFor('veryShort'), 'r+b');
        $stream = new Stream($res);
        $this->assertEquals("stream", get_resource_type($res));
        $stream = null;
        $this->assertNotEquals("stream", get_resource_type($res));
    }

    /**
     * @covers ::getUri()
     */
    public function testStreamGetURI()
    {
        $stream = new Stream($this->getFilePathFor('veryShort'));
        $this->assertEquals("vfs://root/testfiles/veryShort.csv", $stream->getUri());
    }

    /**
     * @covers ::fread()
     */
    public function testFreadGetsRightNumChars()
    {
        $stream = new Stream($this->getFilePathFor('veryShort'));
        $this->assertEquals("foo,bar,ba", $stream->fread(10));
    }

    /**
     * @covers ::fgets()
     */
    public function testFgetsReturnsCurrentLineAndAdvancesToNext()
    {
        $stream = new Stream($this->getFilePathFor('veryShort'));
        $this->assertEquals("foo,bar,baz", $stream->fgets());
        $this->assertEquals("bin,boz,bork", $stream->fgets());
        $this->assertEquals("lib,bil,ilb", $stream->fgets());
    }

    /**
     * @covers ::eof()
     */
    public function testEofReturnsTrueWhenAtEndOfFile()
    {
        $stream = new Stream($this->getFilePathFor('veryShort'));
        $this->assertFalse($stream->eof());
        $stream->fgets();
        $this->assertFalse($stream->eof());
        $stream->fgets();
        $this->assertFalse($stream->eof());
        $stream->fgets();
        $this->assertTrue($stream->eof());
    }

    /**
     * @covers ::eof()
     */
    public function testEofAcceptsArbitraryLineTerminator()
    {
        $stream = new Stream($this->getFilePathFor('veryShort'));
        $this->assertEquals('foo', $stream->fgets(","));
        $this->assertEquals('bar', $stream->fgets(","));
        $this->assertEquals("baz\nbin", $stream->fgets(","));
        $this->assertEquals('boz', $stream->fgets(","));
        $this->assertEquals("bork\nlib", $stream->fgets(","));
        $this->assertEquals('bil', $stream->fgets(","));
        $this->assertEquals("ilb\n", $stream->fgets(","));
    }

    /**
     * @covers ::rewind()
     */
    public function testRewindReturnsPointerToBeginning()
    {
        $stream = new Stream($this->getFilePathFor('veryShort'));
        $stream->fread(15);
        $this->assertEquals(",boz,bork", $stream->fgets(), "Just make sure we are somewhere in the middle of the stream.");
        $this->assertTrue($stream->rewind(), "Stream::rewind should return true on success.");
        $this->assertEquals("foo,bar,baz", $stream->fgets(), "Now we should be at the beginning again.");
    }

    /**
     * @covers ::fwrite()
     */
    public function testFwriteWritesDataAndReturnsNumBytesWritten()
    {
        $stream = new Stream($fn = $this->getFilePathFor('veryShort'), ['open_mode' => 'a+']);
        $data = "thisisten!";
        $this->assertEquals(strlen($data), $stream->fwrite($data));
        $stream->rewind();
        $this->assertEquals("foo,bar,baz\nbin,boz,bork\nlib,bil,ilb\nthisisten!", $stream->fread(50));
    }

    /**
     * @covers ::fseek()
     */
    public function testSeekableStreamCanBeSeekd()
    {
        $stream = new Stream($this->getFilePathFor('veryShort'), ['open_mode' => 'r+b']);
        $this->assertTrue($stream->fseek(10, SEEK_SET));
        $this->assertEquals("z\nbin,boz,", $stream->fread(10));
        $this->assertTrue($stream->fseek(5, SEEK_CUR));
        $this->assertEquals("lib,b", $stream->fread(5));
        $this->assertTrue($stream->fseek(-15, SEEK_END));
        $this->assertEquals("rk\nlib,bil", $stream->fread(10));
    }

    /**
     * @covers ::isSeekable()
     */
    public function testSeekableStreamsReturnTrueOnIsSeekable()
    {
        $seekableStream = new Stream($this->getFilePathFor('veryShort'), ['open_mode' => 'r+b']);
        $this->assertTrue($seekableStream->isSeekable());
        $nonSeekableStream = new Stream('php://output', ['open_mode' => 'w']);
        $this->assertFalse($nonSeekableStream->isSeekable());
    }

    /**
     * @covers ::isReadable()
     */
    public function testSeekableStreamsReturnTrueOnIsReadable()
    {
        $readableStream = new Stream($this->getFilePathFor('veryShort'), ['open_mode' => 'r+b']);
        $this->assertTrue($readableStream->isReadable());
        $nonReadableStream = new Stream('php://output', ['open_mode' => 'w']);
        $this->assertFalse($nonReadableStream->isReadable());
    }

    /**
     * @covers ::isWritable()
     */
    public function testSeekableStreamsReturnTrueOnIsWritable()
    {
        $writableStream = new Stream('php://output', ['open_mode' => 'w']);
        $this->assertTrue($writableStream->isWritable());
        $nonWritableStream = new Stream($this->getFilePathFor('veryShort'), ['open_mode' => 'rb']);
        $this->assertFalse($nonWritableStream->isWritable());
    }

    /**
     * @covers ::streamize()
     */
    public function testStreamCanConvertStringIntoStreamWithStreamize()
    {
        $csv_string = $this->getFileContentFor('veryShort');
        $csv_stream = Stream::streamize($csv_string);
        $this->assertEquals($csv_string, $csv_stream->fread(37));
    }

    /**
     * @covers ::streamize()
     */
    public function testStreamCanConvertEmptyStringIntoStreamWithStreamizeWithNoParams()
    {
        $csv_stream = Stream::streamize();
        $this->assertEquals('', $csv_stream->fread(10));
    }

    /**
     * @covers ::streamize()
     */
    public function testStreamCanConvertObjectWithToStringMethodIntoStreamWithStreamize()
    {
        // Create a stub for non-existant StreamableClass.
        $csv_obj = $this->getMockBuilder('StreamableClass')
                        ->setMethods(['__toString'])
                        ->getMock();

        // Configure the stub.
        $csv_obj->method('__toString')
             ->willReturn($csv_string = $this->getFileContentFor('veryShort'));

        // test it
        $csv_stream = Stream::streamize($csv_obj);
        $this->assertEquals($csv_string, $csv_stream->fread(37));
    }

}
