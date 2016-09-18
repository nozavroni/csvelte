<?php
namespace CSVelteTest;

use CSVelte\IO\Resource;
use CSVelte\IO\Stream;
use function CSVelte\streamize;

/**
 * CSVelte functions tests
 *
 * @package   CSVelte Unit Tests
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class FunctionsTest extends UnitTestCase
{
    public function testStreamReturnsStreamObjectForOpenStreamResource()
    {
        $handle = fopen($this->getFilePathFor('veryShort'), $mode = 'r+b');
        $this->assertInstanceOf(Stream::class, $stream = streamize($handle));
        $this->assertEquals($handle, $stream->getResource()->getHandle());
        $this->assertEquals($mode, $stream->getResource()->getMode());
    }

    public function testStreamReturnsStreamObjectForPHPString()
    {
        $string = "All your base are belong to us";
        $this->assertInstanceOf(Stream::class, $stream = streamize($string));
        $res = $stream->getResource();
        $this->assertTrue(is_resource($res()));
        $this->assertTrue($res->isConnected());
        $this->assertEquals(strlen($string), $stream->getSize());
        $this->assertEquals($string, (string) $stream);
        $this->assertEquals('r+', $stream->getResource()->getMode());
    }
}
