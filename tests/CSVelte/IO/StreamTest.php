<?php
namespace CSVelteTest\IO;

use CSVelte\IO\Stream;
use CSVelteTest\StreamWrapper\HttpStreamWrapper;
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
    public function setUp()
    {
        parent::setUp();
        stream_wrapper_unregister('http');
        stream_wrapper_register(
            'http',
            HttpStreamWrapper::class
        ) or die('Failed to register protocol');
    }

    public function tearDown()
    {
        parent::tearDown();
        stream_wrapper_restore('http');
    }

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
        $stream = new Stream('php://input', null, 'foo');
    }

    public function testInstantiateStreamWithContextOptionsAndStringURI()
    {
        $stream = new Stream('http://www.example.com/', 'rb', $expContext = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => ['foo' => 'bar', 'baz' => 'bin']
            ]
        ]);
        $meta = stream_get_meta_data($stream->getResource());
        $wrapper = $meta['wrapper_data'];
        $this->assertEquals($expContext, $wrapper->getContext());
    }

    /**
     * @expectedException CSVelte\Exception\IOException
     * @expectedExceptionCode CSVelte\Exception\IOException::ERR_INVALID_STREAM_URI
     */
    public function testInstantiateThrowsExceptionIfInvalidStreamURI()
    {
        $stream = new Stream('foo');
    }

    /**
     * @expectedException CSVelte\Exception\IOException
     * @expectedExceptionCode CSVelte\Exception\IOException::ERR_INVALID_STREAM_RESOURCE
     */
    public function testInstantiateThrowsExceptionIfInvalidStreamResource()
    {
        $stream = new Stream(new \stdClass());
    }

    /**
     * @covers ::getMetaData()
     */
    public function testGetMetaDataAll()
    {
        $stream = new Stream($this->getFilePathFor('veryShort'), 'r+');
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
        $stream = new Stream($this->getFilePathFor('veryShort'), 'r+');
        $this->assertEquals($this->getFilePathFor('veryShort'), $stream->getMetaData('uri'));
        $this->assertEquals('r+', $stream->getMetaData('mode'));
        $this->assertEquals('0', $stream->getMetaData('unread_bytes'));
        $this->assertTrue($stream->getMetaData('seekable'));
    }

    public function testCloseKillsConnection()
    {
        $res = fopen($this->getFilePathFor('veryShort'), 'r+b');
        $stream = new Stream($res);
        $this->assertEquals("stream", get_resource_type($stream->getResource()));
        $this->assertEquals("stream", get_resource_type($res));
        $stream->close();
        $this->assertNotEquals("stream", get_resource_type($stream->getResource()));
        $this->assertNotEquals("stream", get_resource_type($res));
    }

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
    public function testGetURIReturnsStreamUri()
    {
        $stream = new Stream($this->getFilePathFor('veryShort'));
        $this->assertEquals("vfs://root/testfiles/veryShort.csv", $stream->getUri());
    }

    public function testReadGetsCorrectNumChars()
    {
        $stream = new Stream($this->getFilePathFor('headerDoubleQuote'));
        $this->assertEquals("Bank Name,City,ST,CERT,Acquiring Institution,Closing Date,Updated Date\nFirst CornerStone Bank,\"King ", $chars = $stream->read(100));
        $this->assertEquals(100, strlen($chars));
    }

    /**
     * @expectedException CSVelte\Exception\IOException
     * @expectedExceptionCode CSVelte\Exception\IOException::ERR_NOT_READABLE
     */
    public function testReadThrowsExceptionIfNotReadable()
    {
        $stream = new Stream($this->getFilePathFor('headerDoubleQuote'), 'w');
        $stream->read(10);
    }

    public function testReadReturnsFalseAtEof()
    {
        $stream = new Stream($this->getFilePathFor('headerDoubleQuote'));
        $stream->read(2534);
        $this->assertFalse($stream->read(1));
    }

    public function testReadLineGetsLineUpToEol()
    {
        $stream = new Stream($this->getFilePathFor('headerDoubleQuote'));
        $this->assertEquals("Bank Name,City,ST,CERT,Acquiring Institution,Closing Date,Updated Date\n", $stream->readLine("\n"));
        $this->assertEquals("First CornerStone Bank,\"King of\n", $stream->readLine("\n"));
        $this->assertEquals("\"\"Prussia\"\"\",PA,35312,First-Citizens Bank & Trust Company,6-May-16,25-May-16\n", $stream->readLine("\n"));
    }

    public function testReadLineGetsLineUpToEofThenReturnsFalse()
    {
        $stream = new Stream($this->getFilePathFor('headerDoubleQuote'));
        $this->assertNotFalse($stream->readLine("\n"));
        for ($i = 0; $i < 34; $i++) $stream->readLine("\n");
        $this->assertFalse($stream->readLine());
    }

    public function testReadLineRespectsMaxLength()
    {
        $stream = new Stream($this->getFilePathFor('headerDoubleQuote'));
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
        $stream = new Stream($this->getFilePathFor('headerDoubleQuote'));
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
        $stream = new Stream($this->getFilePathFor('headerDoubleQuote'));
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
        $stream = new Stream($this->getFilePathFor('veryShort'));
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
        $stream = new Stream($fn = $this->getFilePathFor('veryShort'), 'a+');
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
        $stream = new Stream($this->getFilePathFor('veryShort'), 'r+b');
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
        $seekableStream = new Stream($this->getFilePathFor('veryShort'));
        $this->assertTrue($seekableStream->isSeekable());
        $nonSeekableStream = new Stream('php://output', 'w');
        $this->assertFalse($nonSeekableStream->isSeekable());
    }

    /**
     * @covers ::isReadable()
     */
    public function testSeekableStreamsReturnTrueOnIsReadable()
    {
        $readableStream = new Stream($this->getFilePathFor('veryShort'));
        $this->assertTrue($readableStream->isReadable());
        $nonReadableStream = new Stream('php://output', 'w');
        $this->assertFalse($nonReadableStream->isReadable());
    }

    /**
     * @covers ::isWritable()
     */
    public function testSeekableStreamsReturnTrueOnIsWritable()
    {
        $writableStream = new Stream('php://output', 'w');
        $this->assertTrue($writableStream->isWritable());
        $nonWritableStream = new Stream($this->getFilePathFor('veryShort'), 'rb');
        $this->assertFalse($nonWritableStream->isWritable());
    }

    /**
     * @covers ::streamize()
     */
    public function testStreamCanConvertStringIntoStreamWithStreamize()
    {
        $csv_string = $this->getFileContentFor('veryShort');
        $csv_stream = Stream::streamize($csv_string);
        $this->assertEquals($csv_string, $csv_stream->read(37));
    }

    /**
     * @covers ::streamize()
     */
    public function testStreamCanConvertEmptyStringIntoStreamWithStreamizeWithNoParams()
    {
        $csv_stream = Stream::streamize();
        $this->assertEquals('', $csv_stream->read(10));
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
        $this->assertEquals($csv_string, $csv_stream->read(37));
    }

    /**
     * @expectedException CSVelte\Exception\IOException
     * @expectedExceptionCode CSVelte\Exception\IOException::ERR_NOT_WRITABLE
     */
    public function testWriteToNonWritableStreamThrowsIOException()
    {
        $stream = new Stream('php://input', 'r');
        $stream->write('foo');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testStreamThrowsExceptionIfContextIsNotAnArray()
    {
        $stream = new Stream('php://input', 'r', 'hamburgers');
    }

    public function testStreamGetContents()
    {
        $stream = new Stream($filename = $this->getFilePathFor('headerDoubleQuote'));
        $this->assertStringEqualsFile($filename, $stream->getContents());
    }

    public function testStreamGetContentsReadsStartingFromPositionItsIn()
    {
        $stream = new Stream($filename = $this->getFilePathFor('headerDoubleQuote'));
        $onehundred = $stream->read(100);
        $expected = substr(file_get_contents($filename), 100);
        $this->assertEquals($expected, $stream->getContents());
    }

    public function testStreamToStringReadsEntireStream()
    {
        $stream = new Stream($filename = $this->getFilePathFor('headerDoubleQuote'));
        $onehundred = $stream->read(100);
        $expected = file_get_contents($filename);
        $this->assertEquals($expected, $stream->__toString());
        $this->assertEquals($expected, (string) $stream);
    }

    public function testStreamToStringReturnsPointerToOriginalPosition()
    {
        $stream = new Stream($filename = $this->getFilePathFor('headerDoubleQuote'));
        $onehundred = $stream->read(100);
        $expected = file_get_contents($filename);
        $this->assertEquals($expected, $stream->__toString(), "Ensure that __toString() returns entire contents of stream");
        $this->assertEquals($expected, (string) $stream, "Ensure that casting to string eturns entire contents of stream");
        $expected = substr($expected, 100);
        $this->assertEquals($expected, $stream->getContents(), "Ensure that stream internal pointer was returned to its original position after retrieving entire contents with __toString()");
    }

    public function testStreamCanGetPositionWithTell()
    {
        $stream = new Stream($filename = $this->getFilePathFor('headerDoubleQuote'));
        $stream->seek($onehundred = 100);
        $this->assertEquals($onehundred, $stream->tell());
    }

    public function testStreamCanGetSize()
    {
        $stream = new Stream($filename = $this->getFilePathFor('veryShort'));
        $this->assertEquals($expected = filesize($filename), $stream->getSize());
        // requires a second call to getSize() in order to get full test coverage
        $this->assertEquals($expected, $stream->getSize());
    }

    public function testStreamDetachRemovesStreamFromUnderlyingStreamResourceLeavingItUnusableButNotBroken()
    {
        $stream = new Stream($filename = $this->getFilePathFor('veryShort'));
        $streamResource = $stream->getResource();
        $this->assertEquals($expectedName = "vfs://root/testfiles/veryShort.csv", $stream->getName());
        $this->assertEquals($expectedUri = "vfs://root/testfiles/veryShort.csv", $stream->getUri());
        $this->assertEquals($expectedSize = 37, $stream->getSize());
        $this->assertInternalType($expectedMetaType = "array", $stream->getMetaData());
        $this->assertTrue($stream->isSeekable());
        $this->assertTrue($stream->isReadable());
        $this->assertTrue($stream->isWritable());
        $this->assertInternalType($expectedResourceType = "resource", $streamResource);
        $this->assertEquals($expectedResourceStreamType = "stream", get_resource_type($streamResource));
        $this->assertFalse($stream->eof());
        $this->assertEquals($expectedStrContent = "foo,bar,ba", $stream->read(10));
        $this->assertEquals($expectedStrContent = "z\nbin,boz,bork\nlib,bil,ilb\n", $stream->getContents());
        $this->assertEquals($expectedStrContent = "foo,bar,baz\nbin,boz,bork\nlib,bil,ilb\n", $stream->__toString());
        $this->assertTrue($stream->seek(25));
        $this->assertEquals(25, $stream->tell());
        $this->assertEquals(10, $stream->write("helloworld"));

        $detachedResource = $stream->detach();
        $this->assertEquals($streamResource, $detachedResource, "Ensure that the detach method returns the internal stream resource.");
        $this->assertNull($stream->getResource());

        $streamResource = $stream->getResource();
        $this->assertNull($stream->getName());
        $this->assertNull($stream->getUri());
        $this->assertNull($stream->getSize());
        $this->assertNull($stream->getMetaData());
        $this->assertFalse($stream->isSeekable());
        $this->assertFalse($stream->isReadable());
        $this->assertFalse($stream->isWritable());
        $this->assertNull($streamResource);
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
        $stream = new Stream($filename = $this->getFilePathFor('veryShort'));
        $this->assertEquals($stream->getResource(), $stream->detach());
        $stream->read(10);
    }

    /**
     * @expectedException CSVelte\Exception\IOException
     */
    public function testDetachedStreamThrowsExceptionOnWrite()
    {
        $stream = new Stream($filename = $this->getFilePathFor('veryShort'));
        $this->assertEquals($stream->getResource(), $stream->detach());
        $stream->write("helloworld");
    }

    /**
     * @expectedException CSVelte\Exception\IOException
     */
    public function testDetachedStreamThrowsExceptionOnSeek()
    {
        $stream = new Stream($filename = $this->getFilePathFor('veryShort'));
        $this->assertEquals($stream->getResource(), $stream->detach());
        $stream->seek(10);
    }

}
