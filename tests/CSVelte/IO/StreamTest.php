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
    /**
     * @covers ::__construct()
     */
    public function testInstantiateIOStreamAcceptsStreamURI()
    {
        $stream = new Stream($this->getFilePathFor('veryShort'));
        $this->assertTrue(is_resource($stream->getResource()));
        $this->assertEquals("stream", get_resource_type($stream->getResource()));
    }

    /**
     * @covers ::__construct()
     */
    public function testInstantiateIOStreamAcceptsStreamResource()
    {
        $resource = fopen($this->getFilePathFor('veryShort'), 'r+');
        $stream = new Stream($resource);
        $this->assertSame($resource, $stream->getResource());
    }
}
