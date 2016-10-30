<?php
namespace CSVelteTest\IO;

use \ArrayIterator;
use CSVelte\Exception\NotYetImplementedException;
use \SplFileObject;
use CSVelte\Contract\Streamable;
use CSVelte\IO\Stream;
use CSVelte\IO\BufferStream;
use CSVelte\IO\IteratorStream;
use CSVelte\IO\Resource;

use function CSVelte\streamize;

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
    public function testInstantiateNewStreamWithLazyResource()
    {
        $res = new Resource($this->getFilePathFor('veryShort'));
        $stream = new Stream($res);
        $sr = $stream->getResource();
        $this->assertFalse($sr->isConnected());
        $this->assertTrue(is_resource($sr->getHandle()));
        $this->assertTrue($stream->getResource()->isConnected());
        $this->assertTrue($stream->close());
        $this->assertFalse($stream->getResource()->isConnected());
    }

    public function testInstantiateNewStreamWithNotLazyResource()
    {
        $res = new Resource($this->getFilePathFor('veryShort'), null, $isLazyExp = false);
        $stream = new Stream($res);
        $sr = $stream->getResource();
        $this->assertTrue($sr->isConnected());
        $this->assertTrue(is_resource($sr->getHandle()));
        $this->assertTrue($stream->getResource()->isConnected());
        $this->assertTrue($stream->close());
        $this->assertFalse($stream->getResource()->isConnected());
    }

    public function testInstantiateNewStreamUsingStaticOpenMethod()
    {
        $stream = Stream::open($this->getFilePathFor('veryShort'), 'rb');
        $this->assertInstanceOf(Stream::class, $stream);
        $this->assertTrue($stream->getResource()->isConnected());
    }

    public function testInstantiateNewLazyStreamUsingStaticOpenMethod()
    {
        $stream = Stream::open($this->getFilePathFor('veryShort'), 'rb', null, true);
        $this->assertInstanceOf(Stream::class, $stream);
        $this->assertFalse($stream->getResource()->isConnected());
        $this->assertEquals(
            stream_context_get_params(stream_context_create(null)),
            stream_context_get_params($stream->getResource()->getContext()),
            "Ensure no context was passed to the generated resource."
        );
    }

    public function testInstantiateIOStreamAcceptsStreamResource()
    {
        $handle = fopen(
            $this->getFilePathFor('veryShort'),
            'r+b',
            null,
            stream_context_create(['http' => ['method' => 'POST']])
        );
        $resource = new Resource($handle);
        $stream = new Stream($resource);
        $this->assertTrue($stream->isReadable());
        $this->assertTrue($stream->isWritable());
        $this->assertFalse($stream->getResource()->isTruncated());
        $this->assertTrue($stream->getResource()->isBinary());
        $this->assertFalse($stream->getResource()->isText());
    }

    public function testInstantiateIOStreamAcceptsStreamURI()
    {
        $stream = Stream::open($this->getFilePathFor('veryShort'));
        $res = $stream->getResource();
        $this->assertTrue(is_resource($res->getHandle()));
        $this->assertEquals("stream", get_resource_type($res->getHandle()));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInstantiateWithContextNotArrayThrowsException()
    {
        $stream = Stream::open('php://input', null, 'foo');
    }

    public function testInstantiateStreamWithContextOptionsAndStringURI()
    {
        $stream = Stream::open('http://www.example.com/', 'rb', stream_context_create($expOptions = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => ['foo' => 'bar', 'baz' => 'bin']
            ]
        ]));
        $meta = stream_get_meta_data($stream->getResource()->getHandle());
        $wrapper = $meta['wrapper_data'];
        $this->assertEquals($expOptions, $wrapper->getContextOptions());
    }

    /**
     * @expectedException CSVelte\Exception\IOException
     * @expectedExceptionCode CSVelte\Exception\IOException::ERR_STREAM_CONNECTION_FAILED
     */
    public function testInstantiateThrowsExceptionIfInvalidStreamURI()
    {
        $stream = Stream::open('foo');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInstantiateThrowsExceptionIfInvalidStreamResource()
    {
        $stream = Stream::open(new \stdClass());
    }

    /**
     * @covers ::getMetaData()
     */
    public function testGetMetaDataAll()
    {
        $stream = Stream::open($this->getFilePathFor('veryShort'), 'r+');
        $meta = $stream->getMetaData();
        $this->assertArrayHasKey('mode', $meta);
        $this->assertArrayHasKey('seekable', $meta);
        $this->assertArrayHasKey('unread_bytes', $meta);
        $this->assertArrayHasKey('uri', $meta);
    }

    /**
     * @covers ::getMetaData()
     */
    public function testGetMetaDataByKey()
    {
        $stream = Stream::open($this->getFilePathFor('veryShort'), 'r+');
        $this->assertEquals($this->getFilePathFor('veryShort'), $stream->getMetaData('uri'));
        $this->assertEquals('r+', $stream->getMetaData('mode'));
        $this->assertEquals('0', $stream->getMetaData('unread_bytes'));
        $this->assertTrue($stream->getMetaData('seekable'));
    }

    public function testCloseKillsConnection()
    {
        $res = fopen($this->getFilePathFor('veryShort'), 'r+b');
        $stream = new Stream(new Resource($res));
        $this->assertEquals("stream", get_resource_type($stream->getResource()->getHandle()));
        $this->assertEquals("stream", get_resource_type($res));
        $this->assertTrue($stream->getResource()->isConnected());
        $stream->close();
        $this->assertFalse($stream->getResource()->isConnected());
        // if you call getHandle() after calling close() and you're
        // expecting a closed resource, you will be unpleasantly
        // surprised... getResource() reopens if connection is set to lazy (this one is)
    }

    public function testDestructKillsConnection()
    {
        $res = fopen($this->getFilePathFor('veryShort'), 'r+b');
        $stream = new Stream(new Resource($res));
        $this->assertEquals("stream", get_resource_type($res));
        $stream = null;
        $this->assertNotEquals("stream", get_resource_type($res));
    }

    /**
     * @covers ::getUri()
     */
    public function testGetURIReturnsStreamUri()
    {
        $stream = Stream::open($this->getFilePathFor('veryShort'));
        $this->assertEquals("vfs://root/testfiles/veryShort.csv", $stream->getUri());
    }

    public function testReadGetsCorrectNumChars()
    {
        $stream = Stream::open($this->getFilePathFor('headerDoubleQuote'));
        $this->assertEquals("Bank Name,City,ST,CERT,Acquiring Institution,Closing Date,Updated Date\nFirst CornerStone Bank,\"King ", $chars = $stream->read(100));
        $this->assertEquals(100, strlen($chars));
    }

    /**
     * @expectedException CSVelte\Exception\IOException
     * @expectedExceptionCode CSVelte\Exception\IOException::ERR_NOT_READABLE
     */
    public function testReadThrowsExceptionIfNotReadable()
    {
        $stream = Stream::open($this->getFilePathFor('headerDoubleQuote'), 'w');
        $stream->read(10);
    }

    public function testReadReturnsFalseAtEof()
    {
        $stream = Stream::open($this->getFilePathFor('headerDoubleQuote'));
        $stream->read(2534);
        $this->assertFalse($stream->read(1));
    }

    public function testReadLineGetsLineUpToEol()
    {
        $stream = Stream::open($this->getFilePathFor('headerDoubleQuote'));
        $this->assertEquals("Bank Name,City,ST,CERT,Acquiring Institution,Closing Date,Updated Date\n", $stream->readLine("\n"));
        $this->assertEquals("First CornerStone Bank,\"King of\n", $stream->readLine("\n"));
        $this->assertEquals("\"\"Prussia\"\"\",PA,35312,First-Citizens Bank & Trust Company,6-May-16,25-May-16\n", $stream->readLine("\n"));
    }

    public function testReadLineGetsLineUpToEofThenReturnsFalse()
    {
        $stream = Stream::open($this->getFilePathFor('headerDoubleQuote'));
        $this->assertNotFalse($stream->readLine("\n"));
        for ($i = 0; $i < 34; $i++) $stream->readLine("\n");
        $this->assertFalse($stream->readLine());
    }

    public function testReadLineRespectsMaxLength()
    {
        $stream = Stream::open($this->getFilePathFor('headerDoubleQuote'));
        $this->assertEquals("Bank Name", $stream->readLine("\n", 10));
        $this->assertEquals(",City,ST,", $stream->readLine("\n", 10));
        $this->assertEquals("CERT,Acqu", $stream->readLine("\n", 10));
        $this->assertEquals("iring Ins", $stream->readLine("\n", 10));
        $this->assertEquals("titution,", $stream->readLine("\n", 10));
        $this->assertEquals("Closing D", $stream->readLine("\n", 10));
        $this->assertEquals("ate,Updat", $stream->readLine("\n", 10));
        $this->assertEquals("ed Date\n", $stream->readLine("\n", 10));
        $this->assertEquals("First CornerStone Bank,\"King of\n", $stream->readLine("\n", 100), "Ensure readline returns on newline regardless of maxlength argument.");
        $this->assertEquals("\"\"Prussia\"\"\",PA,35312,First-Citizens Bank & Trust", $stream->readLine("\n", 50));
        $this->assertEquals(" Company,6-May-16,25-May-16\n", $stream->readLine("\n", 50));
    }

    public function testReadLineCanAcceptAnyStringAsEol()
    {
        $stream = Stream::open($this->getFilePathFor('headerDoubleQuote'));
        $this->assertEquals("Bank Name", $stream->readLine("Name"));
        $this->assertEquals(",", $stream->readLine(","));
        $this->assertEquals("City,", $stream->readLine(","));
        $this->assertEquals("ST,CERT,Acquiring ", $stream->readLine(" "));
        $this->assertEquals("Instituti", $stream->readLine(" ", 10));
        $this->assertEquals("on,Closin", $stream->readLine(" ", 10));
        $this->assertEquals("g ", $stream->readLine(" ", 10));
        $this->assertEquals("Date,Updated Date\nF", $stream->readLine("F"));
    }

    public function testReadLineCanAcceptAnArrayOfEols()
    {
        $stream = Stream::open($this->getFilePathFor('headerDoubleQuote'));
        $eols = ["\n", " ", ","];
        $this->assertEquals("Bank ", $stream->readLine($eols));
        $this->assertEquals("Name,", $stream->readLine($eols));
        $this->assertEquals("City,", $stream->readLine($eols));
        $this->assertEquals("ST,", $stream->readLine($eols));
        $this->assertEquals("CERT,", $stream->readLine($eols));
        $this->assertEquals("Acquiring ", $stream->readLine($eols));
        $this->assertEquals("Institution,", $stream->readLine($eols));
        $this->assertEquals("Closing ", $stream->readLine($eols));
        $this->assertEquals("Date,", $stream->readLine($eols));
        $this->assertEquals("Updated ", $stream->readLine($eols));
        $this->assertEquals("Date\n", $stream->readLine($eols));
        $this->assertEquals("First ", $stream->readLine($eols));
        $this->assertEquals("CornerStone ", $stream->readLine($eols));
        $this->assertEquals("Bank,", $stream->readLine($eols));

        // make sure maxlength still works too
        $this->assertEquals("\"King of\n", $stream->readLine([".", "!"], 10));
        $this->assertEquals("\"\"Prussia", $stream->readLine([".", "!"], 10));
    }

    /**
     * @covers ::rewind()
     */
    public function testRewindReturnsPointerToBeginning()
    {
        $stream = Stream::open($this->getFilePathFor('veryShort'));
        $stream->read(15);
        $this->assertEquals(",boz,bork\n", $stream->readLine("\n"), "Just make sure we are somewhere in the middle of the stream.");
        $this->assertNull($stream->rewind(), "Stream::rewind should return null.");
        $this->assertEquals("foo,bar,baz\n", $stream->readLine("\n"), "Now we should be at the beginning again.");
    }

    /**
     * @covers ::write()
     */
    public function testwriteWritesDataAndReturnsNumBytesWritten()
    {
        $stream = Stream::open($fn = $this->getFilePathFor('veryShort'), 'a+');
        $data = "thisisten!";
        $this->assertEquals(strlen($data), $stream->write($data));
        $stream->rewind();
        $this->assertEquals("foo,bar,baz\nbin,boz,bork\nlib,bil,ilb\nthisisten!", $stream->read(50));
    }

    /**
     * @covers ::seek()
     */
    public function testSeekableStreamCanBeSeekd()
    {
        $stream = Stream::open($this->getFilePathFor('veryShort'), 'r+b');
        $this->assertTrue($stream->seek(10, SEEK_SET));
        $this->assertEquals("z\nbin,boz,", $stream->read(10));
        $this->assertTrue($stream->seek(5, SEEK_CUR));
        $this->assertEquals("lib,b", $stream->read(5));
        $this->assertTrue($stream->seek(-15, SEEK_END));
        $this->assertEquals("rk\nlib,bil", $stream->read(10));
    }

    /**
     * @covers ::isSeekable()
     */
    public function testSeekableStreamsReturnTrueOnIsSeekable()
    {
        $seekableStream = Stream::open($this->getFilePathFor('veryShort'));
        $this->assertTrue($seekableStream->isSeekable());
        $nonSeekableStream = Stream::open('php://output', 'w');
        $this->assertFalse($nonSeekableStream->isSeekable());
    }

    /**
     * @covers ::isReadable()
     */
    public function testSeekableStreamsReturnTrueOnIsReadable()
    {
        $readableStream = Stream::open($this->getFilePathFor('veryShort'));
        $this->assertTrue($readableStream->isReadable());
        $nonReadableStream = Stream::open('php://output', 'w');
        $this->assertFalse($nonReadableStream->isReadable());
    }

    /**
     * @covers ::isWritable()
     */
    public function testSeekableStreamsReturnTrueOnIsWritable()
    {
        $writableStream = Stream::open('php://output', 'w');
        $this->assertTrue($writableStream->isWritable());
        $nonWritableStream = Stream::open($this->getFilePathFor('veryShort'), 'rb');
        $this->assertFalse($nonWritableStream->isWritable());
    }

//    /**
//     * @expectedException NotYetImplementedException
//     */
//    public function testSeekableStreamsThrowNotYetImplementedOnSeekLineMethod()
//    {
//        $stream = Stream::open($this->getFilePathFor('veryShort'));
//        $this->assertTrue($stream->isSeekable());
//        $stream->seekLine(1);
//    }

    public function testStreamCanConvertStringIntoStreamWithStreamize()
    {
        $csv_string = $this->getFileContentFor('veryShort');
        $csv_stream = streamize($csv_string);
        $this->assertEquals($csv_string, $csv_stream->read(37));
    }

    public function testStreamCanConvertEmptyStringIntoStreamWithStreamizeWithNoParams()
    {
        $csv_stream = streamize();
        $this->assertEquals('', $csv_stream->read(10));
    }

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
        $csv_stream = streamize($csv_obj);
        $this->assertEquals($csv_string, $csv_stream->read(37));
    }

    public function testStreamizeCanStreamSplFileObject()
    {
        $fileObj = new SplFileObject($fn = $this->getFilePathFor('headerCommaQuoteNonnumeric'));
        $this->assertInstanceOf(Streamable::class, $stream = streamize($fileObj));
        $this->assertEquals(file_get_contents($fn), $stream->__toString());
    }

    // public function testStreamizeCanStreamSplFileObjectAndSetCorrectPosition()
    // {
    //     $fileObj = new SplFileObject($fn = $this->getFilePathFor('headerCommaQuoteNonnumeric'));
    //     $fileObj->read($pos = 25);
    //     $stream = streamize($fileObj);
    //     $this->assertEquals($pos, $stream->tell());
    //     //$this->assertEquals($pos, $fileObj->ftell());
    // }

    /**
     * @expectedException CSVelte\Exception\IOException
     * @expectedExceptionCode CSVelte\Exception\IOException::ERR_NOT_WRITABLE
     */
    public function testWriteToNonWritableStreamThrowsIOException()
    {
        $stream = Stream::open('php://input', 'r');
        $stream->write('foo');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testStreamThrowsExceptionIfContextIsNotAnArray()
    {
        $stream = Stream::open('php://input', 'r', 'hamburgers');
    }

    public function testStreamGetContents()
    {
        $stream = Stream::open($filename = $this->getFilePathFor('headerDoubleQuote'));
        $this->assertStringEqualsFile($filename, $stream->getContents());
    }

    public function testStreamGetContentsReadsStartingFromPositionItsIn()
    {
        $stream = Stream::open($filename = $this->getFilePathFor('headerDoubleQuote'));
        $onehundred = $stream->read(100);
        $expected = substr(file_get_contents($filename), 100);
        $this->assertEquals($expected, $stream->getContents());
    }

    public function testStreamToStringReadsEntireStream()
    {
        $stream = Stream::open($filename = $this->getFilePathFor('headerDoubleQuote'));
        $onehundred = $stream->read(100);
        $expected = file_get_contents($filename);
        $this->assertEquals($expected, $stream->__toString());
        $this->assertEquals($expected, (string) $stream);
    }

    public function testStreamToStringReturnsPointerToOriginalPosition()
    {
        $stream = Stream::open($filename = $this->getFilePathFor('headerDoubleQuote'));
        $onehundred = $stream->read(100);
        $expected = file_get_contents($filename);
        $this->assertEquals($expected, $stream->__toString(), "Ensure that __toString() returns entire contents of stream");
        $this->assertEquals($expected, (string) $stream, "Ensure that casting to string eturns entire contents of stream");
        $expected = substr($expected, 100);
        $this->assertEquals($expected, $stream->getContents(), "Ensure that stream internal pointer was returned to its original position after retrieving entire contents with __toString()");
    }

    public function testStreamCanGetPositionWithTell()
    {
        $stream = Stream::open($filename = $this->getFilePathFor('headerDoubleQuote'));
        $stream->seek($onehundred = 100);
        $this->assertEquals($onehundred, $stream->tell());
    }

    public function testStreamCanGetSize()
    {
        $stream = Stream::open($filename = $this->getFilePathFor('veryShort'));
        $this->assertEquals($expected = filesize($filename), $stream->getSize());
        // requires a second call to getSize() in order to get full test coverage
        $this->assertEquals($expected, $stream->getSize());
    }

    // @TODO refactor -- rather than all the calls to if ($this->resource) inside
    // my Stream class, instead add a $resource->detach() method that tells
    // the resource to no longer respond
    public function testStreamDetachRemovesStreamFromUnderlyingStreamResourceLeavingItUnusableButNotBroken()
    {
        $stream = Stream::open($filename = $this->getFilePathFor('veryShort'));
        $streamResource = $stream->getResource();
        $this->assertEquals($expectedName = "vfs://root/testfiles/veryShort.csv", $stream->getName());
        $this->assertEquals($expectedUri = "vfs://root/testfiles/veryShort.csv", $stream->getUri());
        $this->assertEquals($expectedSize = 37, $stream->getSize());
        $this->assertInternalType($expectedMetaType = "array", $stream->getMetaData());
        $this->assertTrue($stream->isSeekable());
        $this->assertTrue($stream->isReadable());
        $this->assertTrue($stream->isWritable());
        $this->assertInternalType($expectedResourceType = "resource", $streamResource->getHandle());
        $this->assertEquals($expectedResourceStreamType = "stream", get_resource_type($streamResource->getHandle()));
        $this->assertFalse($stream->eof());
        $this->assertEquals($expectedStrContent = "foo,bar,ba", $stream->read(10));
        $this->assertEquals($expectedStrContent = "z\nbin,boz,bork\nlib,bil,ilb\n", $stream->getContents());
        $this->assertEquals($expectedStrContent = "foo,bar,baz\nbin,boz,bork\nlib,bil,ilb\n", $stream->__toString());
        $this->assertTrue($stream->seek(25));
        $this->assertEquals(25, $stream->tell());
        $this->assertEquals(10, $stream->write("helloworld"));

        $detachedResource = $stream->detach();
        $this->assertEquals($streamResource(), $detachedResource(), "Ensure that the detach method returns the internal stream resource.");
        // dd($stream->getResource());
        $this->assertNull($stream->getResource());

        $streamResource = $stream->getResource();
        $this->assertNull($stream->getName());
        $this->assertNull($stream->getUri());
        $this->assertNull($stream->getSize());
        $this->assertNull($stream->getMetaData());
        $this->assertFalse($stream->isSeekable());
        $this->assertFalse($stream->isReadable());
        $this->assertFalse($stream->isWritable());
        //$this->assertNull($streamResource());
        $this->assertTrue($stream->eof());
        //$this->assertFalse($stream->read(10));
        $this->assertEquals("", $stream->getContents());
        $this->assertEquals("", $stream->__toString());
        //$this->assertFalse($stream->seek(25));
        $this->assertFalse($stream->tell());
        //$this->assertFalse($stream->write("helloworld"));
    }

    /**
     * @expectedException CSVelte\Exception\IOException
     */
    public function testDetachedStreamThrowsExceptionOnRead()
    {
        $stream = Stream::open($filename = $this->getFilePathFor('veryShort'));
        $this->assertEquals($stream->getResource(), $stream->detach());
        $stream->read(10);
    }

    /**
     * @expectedException CSVelte\Exception\IOException
     */
    public function testDetachedStreamThrowsExceptionOnWrite()
    {
        $stream = Stream::open($filename = $this->getFilePathFor('veryShort'));
        $this->assertEquals($stream->getResource(), $stream->detach());
        $stream->write("helloworld");
    }

    /**
     * @expectedException CSVelte\Exception\IOException
     */
    public function testDetachedStreamThrowsExceptionOnSeek()
    {
        $stream = Stream::open($filename = $this->getFilePathFor('veryShort'));
        $this->assertEquals($stream->getResource(), $stream->detach());
        $stream->seek(10);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testStreamizeWithIntegerThrowsInvalidArgumentException()
    {
        $intval = 1;
        streamize($intval);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testStreamizeWithNonStringObjectThrowsInvalidArgumentException()
    {
        $obj = new \stdClass;
        streamize($obj);
    }

    public function testStreamizeWithNoArguments()
    {
        $stream = streamize();
        $this->assertTrue($stream->isReadable());
        $this->assertTrue($stream->isWritable());
        $this->assertTrue($stream->isSeekable());
        $this->assertEquals("", $stream->read(10));
        $this->assertTrue($stream->eof());
        $this->assertEquals(10, $stream->write("helloworld"));
        $this->assertEquals("helloworld", (string) $stream);
    }

    public function testStreamizeStream()
    {
        $stream = streamize("helloworld");
        $streamcopy = streamize($stream);
        $this->assertEquals((string) $stream, (string) $streamcopy);
    }

    public function testBufferStreamInstantiateBufferSize()
    {
        $stream = new BufferStream();
        $this->assertEquals(['hwm' => 16384], $stream->getMetadata());
        $this->assertEquals(16384, $stream->getMetadata('hwm'));

        $stream = new BufferStream(100);
        $this->assertEquals(['hwm' => 100], $stream->getMetadata());
        $this->assertEquals(100, $stream->getMetadata('hwm'));
    }

    public function testBufferStreamInputOutput()
    {
        $buffer = new BufferStream();

        // write some data to the buffer
        $this->assertTrue($buffer->isWritable());
        $this->assertEquals(11, $buffer->write('helloworld,'));
        $this->assertEquals(8, $buffer->write('goodbye,'));
        $this->assertEquals(6, $buffer->write('cruel,'));
        $this->assertEquals(6, $buffer->write("world\n"));

        // now read to drain it
        $this->assertTrue($buffer->isReadable());
        $this->assertEquals('helloworld', $buffer->read(10));
        $this->assertEquals(',goodbye,cruel,', $buffer->read(15));

        // writing still adds tot he end of the buffer
        $this->assertEquals(16, $buffer->write("abcdefghijklmnop"));

        // read the rest of it...
        $this->assertEquals("world\nabcdefghijklmnop", $buffer->getContents());
    }

    public function testBufferStreamSeek()
    {
        $buffer = new BufferStream();
        $buffer->write($this->getFileContentFor('commaNewlineHeader'));
        $this->assertFalse($buffer->isSeekable());
        $this->assertFalse($buffer->seek(25));
        $this->assertFalse($buffer->tell());
    }

    public function testBufferStreamTellAndEOL()
    {
        $buffer = new BufferStream();
        $buffer->write($this->getFileContentFor('commaNewlineHeader'));
        $this->assertFalse($buffer->tell());
        $this->assertFalse($buffer->eof());
        $buffer->read(100000);
        $this->assertTrue($buffer->eof());
    }

    public function testBufferStreamGetSize()
    {
        $buffer = new BufferStream();
        $buffer->write($content = $this->getFileContentFor('commaNewlineHeader'));
        $this->assertEquals(strlen($content), $buffer->getSize());
        $buffer->read(100);
        $this->assertEquals(strlen($content) - 100, $buffer->getSize());
        $buffer->write('helloworld');
        $this->assertEquals(strlen($content) - 100 + 10, $buffer->getSize());
    }

    public function testBufferStreamWritesFailWhenMaxIsReached()
    {
        $buffer = new BufferStream(100);
        $this->assertEquals(['hwm' => 100], $buffer->getMetadata());
        $this->assertEquals(100, $buffer->getMetadata('hwm'));
        $this->assertEquals(0, $buffer->getSize());
        $this->assertFalse($buffer->isFull());
        $this->assertTrue($buffer->isEmpty());
        $buffer->write('helloworld');
        $buffer->write('helloworld');
        $buffer->write('helloworld');
        $buffer->write('helloworld');
        $buffer->write('helloworld');
        $buffer->write('helloworld');
        $buffer->write('helloworld');
        $buffer->write('helloworld');
        $buffer->write('helloworld');
        $this->assertEquals(90, $buffer->getSize());
        $this->assertEquals(5, $buffer->write('hello'));
        $this->assertEquals(10, $buffer->write('helloworld'));
        $this->assertFalse($buffer->write('helloworld'));
        $this->assertEquals(105, $buffer->getSize());
        // now read and then write 1 char at a time
        $this->assertEquals('helloworld', $buffer->read(10));
        $this->assertFalse($buffer->isFull());
        $this->assertFalse($buffer->isEmpty());
        $this->assertEquals(8, $buffer->write('12341234'));
        $this->assertFalse($buffer->write('1'));
        $this->assertTrue($buffer->isFull());
        $this->assertFalse($buffer->isEmpty());
    }

    public function testBufferStreamClose()
    {
        $buffer = new BufferStream(100);
        $buffer->write($content = $this->getFileContentFor('commaNewlineHeader'));
        $this->assertEquals('Bank Name,', $buffer->read(10));
        $buffer->close();
        $this->assertFalse($buffer->read(1));
    }

    public function testBufferStreamDetach()
    {
        $buffer = new BufferStream(100);
        $buffer->write($content = $this->getFileContentFor('headerCommaQuoteNonnumeric'));
        $detached = $buffer->detach();
        $this->assertFalse($buffer->read(1));
        $this->assertEquals($content, $detached);
    }

    public function testBufferStreamReadLine()
    {
        $buffer = new BufferStream();
        $buffer->write($content = $this->getFileContentFor('commaNewlineHeader'));
        $this->assertEquals("Bank Name,City,ST,CERT,Acquiring Institution,Closing Date,Updated Date\n", $buffer->readLine());
        $this->assertEquals("First CornerStone Bank,\"King of\n", $buffer->readLine());
    }

    public function testBufferStreamWriteLine()
    {
        $buffer = new BufferStream();
        $this->assertEquals(71, $buffer->writeLine($line1 = "Bank Name,City,ST,CERT,Acquiring Institution,Closing Date,Updated Date"));
        $this->assertEquals(32, $buffer->writeLine($line2 = "First CornerStone Bank,\"King of"));
        $this->assertEquals($line1 . PHP_EOL . $line2 . PHP_EOL, $buffer->getContents());

    }

    public function testBufferStreamReadChunk()
    {
        $buffer = new BufferStream();
        $buffer->write("This is a string of text that I put into the buffer. I will now try to remove a chunk from the middle of it.");
        $this->assertEquals('string', $buffer->readChunk(10, 6));
        $this->assertEquals("This is a  of text that I put into the buffer. I will now try to remove a chunk from the middle of it.", $buffer->getContents());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBufferStreamThrowsExceptionIfPassedBadSecondArgument()
    {
        $iter = new IteratorStream(
            new ArrayIterator([1,2,3,4]),
            'foo'
        );
    }

    public function testIteratorStreamUsingArrayIterator()
    {
        $array = explode("\n", $this->getFileContentFor('noHeaderCommaNoQuotes'));
        $iter = new ArrayIterator($array);
        $stream = new IteratorStream($iter);
        $this->assertEquals("1,Eldon Ba", $stream->read(10));
    }

    public function testIteratorStreamSmallerThanBufferHwm()
    {
        $array = explode("\n", $content = $this->getFileContentFor('noHeaderCommaNoQuotes'));
        $lenplusone = strlen($content) + 1;
        $iter = new ArrayIterator($array);
        $stream = new IteratorStream($iter, new BufferStream($lenplusone));
        $this->assertEquals("1,Eldon Ba", $stream->read(10));
    }

    public function testIteratorStreamUsingSplFileObject()
    {
        $content = $this->getFileContentFor('commaNewlineHeader');
        $fileObj = new SplFileObject($this->getFilePathFor('commaNewlineHeader'));
        $stream = new IteratorStream($fileObj, new BufferStream(1024));
        $this->assertEquals(substr($content, 0, 100), $stream->read(100));
        $this->assertEquals(substr($content, 100, 100), $stream->read(100));
        $this->assertEquals(substr($content, 200, 100), $stream->read(100));
        $this->assertEquals(substr($content, 300, 100), $stream->read(100));
        $this->assertEquals(substr($content, 400, 100), $stream->read(100));
        $this->assertEquals(substr($content, 500, 1000), $stream->read(1000));
    }

    public function testIteratorStreamToString()
    {
        $content = $this->getFileContentFor('commaNewlineHeader');
        $fileObj = new SplFileObject($this->getFilePathFor('commaNewlineHeader'));
        $stream = new IteratorStream($fileObj, new BufferStream(1024));
        $this->assertEquals($content, $stream->__toString());
    }

    public function testIteratorStreamReadThenToString()
    {
        $content = $this->getFileContentFor('commaNewlineHeader');
        $fileObj = new SplFileObject($this->getFilePathFor('commaNewlineHeader'));
        $stream = new IteratorStream($fileObj, new BufferStream(1024));
        $this->assertEquals(substr($content, 0, 100), $stream->read(100));
        $this->assertEquals($content, $stream->__toString());
    }

    public function testIteratorStreamReadThenGetContents()
    {
        $content = $this->getFileContentFor('commaNewlineHeader');
        $fileObj = new SplFileObject($this->getFilePathFor('commaNewlineHeader'));
        $stream = new IteratorStream($fileObj, new BufferStream(1024));
        $this->assertEquals(substr($content, 0, 100), $stream->read(100));
        $this->assertEquals(substr($content, 100), $stream->getContents());
    }

    public function testIteratorStreamBasicProperties()
    {
        $arr = ['foo,bar,baz','bar,boo,faz','baz,poo,razz'];
        $iter = new IteratorStream(new ArrayIterator($arr));
        $this->assertTrue($iter->isReadable());
        $this->assertFalse($iter->isWritable());
        $this->assertFalse($iter->isSeekable());
        $this->assertNull($iter->getSize());
    }

    public function testIteratorStreamSeekReturnsFalse()
    {
        $arr = ['foo,bar,baz','bar,boo,faz','baz,poo,razz'];
        $iter = new IteratorStream(new ArrayIterator($arr));
        $this->assertFalse($iter->seek(5));
    }

    public function testIteratorStreamMetaReturnsEmptyArrayWhenCalledWithNoParams()
    {
        $arr = ['foo,bar,baz','bar,boo,faz','baz,poo,razz'];
        $iter = new IteratorStream(new ArrayIterator($arr));
        $this->assertEquals([], $iter->getMetadata());
    }

    public function testIteratorStreamMetaReturnsNullWhenCalledWithNonexistantKey()
    {
        $arr = ['foo,bar,baz','bar,boo,faz','baz,poo,razz'];
        $iter = new IteratorStream(new ArrayIterator($arr));
        $this->assertNull($iter->getMetadata('foo'));
    }

    public function testIteratorStreamDetachUnsetsInternalPropertiesAndReturnsThem()
    {
        $arr = ['foo,bar,baz','bar,boo,faz','baz,poo,razz'];
        $iter = new ArrayIterator($arr);
        $buff = new BufferStream();
        $stream = new IteratorStream($iter, $buff);
        $this->assertInternalType('array', $ret = $stream->detach());
        $this->assertSame($iter, $ret[0]);
        $this->assertSame($buff, $ret[1]);
        $this->assertFalse($stream->isReadable());
        $this->assertFalse($stream->read(5));
    }

    public function testIteratorStreamWriteReturnsFalse()
    {
        $arr = ['foo,bar,baz','bar,boo,faz','baz,poo,razz'];
        $iter = new IteratorStream(new ArrayIterator($arr));
        $this->assertFalse($iter->write('foo'));
    }

    public function testIteratorStreamIsSeekableReturnsFalse()
    {
        $arr = ['foo,bar,baz','bar,boo,faz','baz,poo,razz'];
        $iter = new IteratorStream(new ArrayIterator($arr));
        $this->assertFalse($iter->isSeekable());
    }

//    public function testIteratorStreamTellReturnsPositionWithinBuffer()
    public function testIteratorStreamTellReturnsFalse()
    {
        $arr = ["foo,bar,baz\n","bar,boo,faz\n","baz,poo,razz\n"];
        $iter = new IteratorStream(new ArrayIterator($arr));
        $this->assertEquals("foo,bar,ba", $iter->read(10));
        // this should either return false or return an integer representing
        // the internal position within the stream
        $this->assertFalse($iter->tell());
    }

    public function testIteratorStreamCloseMethodClosesBufferAndIterator()
    {
        $arr = ["foo,bar,baz\n","bar,boo,faz\n","baz,poo,razz\n"];
        $iter = new IteratorStream(new ArrayIterator($arr));
        $iter->close();
        $this->assertFalse($iter->read(10));
    }

    public function testIteratorStreamWriteAlwaysReturnsFalse()
    {
        $arr = ["foo,bar,baz\n","bar,boo,faz\n","baz,poo,razz\n"];
        $iter = new IteratorStream(new ArrayIterator($arr));
        $this->assertFalse($iter->write('foo'));
    }


}
