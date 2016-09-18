<?php
namespace CSVelteTest\IO;

use CSVelte\IO\Stream;
use CSVelte\IO\Resource;

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
class ResourceTest extends IOTest
{
    // public function setUp()
    // {
    //     parent::setUp();
    // }
    //
    // public function tearDown()
    // {
    //     parent::tearDown();
    // }

    public function testInstantiateStreamResource()
    {
        $sr = new Resource($this->getFilePathFor('veryShort'));
        $this->assertEquals($this->getFilePathFor('veryShort'), $sr->getUri());
        $this->assertEquals("r+b", $sr->getMode());
        $this->assertFalse($sr->isLazy());
        $this->assertTrue($sr->isReadable());
        $this->assertTrue($sr->isWritable());
        $this->assertTrue($sr->isConnected());
        $this->assertTrue(is_resource($sr->getResource()));
    }

    /**
     * @expectedException CSVelte\Exception\IOException
     * @expectedExceptionCode CSVelte\Exception\IOException::ERR_STREAM_CONNECTION_FAILED
     */
    public function testInstantiateStreamResourceWithBadUriThrowsException()
    {
        $sr = new Resource("I am not a uri");
    }

    public function testInstantiateALazyResource()
    {
        $sr = new Resource($this->getFilePathFor('veryShort'), null, true);
        $this->assertFalse($sr->isConnected());
        $this->assertTrue(is_resource($sr->getResource()));
        $this->assertTrue($sr->isConnected());
    }
}
