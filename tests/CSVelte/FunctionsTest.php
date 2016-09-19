<?php
namespace CSVelteTest;

use CSVelte\IO\Resource;
use CSVelte\IO\Stream;
use \SplFileObject;
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
    public function testStreamizeReturnsStreamObjectForOpenStreamResource()
    {
        $handle = fopen($this->getFilePathFor('veryShort'), $mode = 'r+b');
        $this->assertInstanceOf(Stream::class, $stream = streamize($handle));
        $this->assertEquals($handle, $stream->getResource()->getHandle());
        $this->assertEquals($mode, $stream->getResource()->getMode());
    }

    public function testStreamizeReturnsStreamObjectForPHPString()
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

    public function testStreamizeReturnsStreamObjectForPHPStringableObject()
    {
        // Create a stub for non-existant StringableClass.
        $csv_obj = $this->getMockBuilder('StringableClass')
                        ->setMethods(['__toString'])
                        ->getMock();

        // configure the stub to return test string...
        $string = "All your base are belong to us";
        $csv_obj->method('__toString')
             ->willReturn($string);

        // test it...
        $this->assertInstanceOf(Stream::class, $stream = streamize($csv_obj));
        $res = $stream->getResource();
        $this->assertTrue(is_resource($res()));
        $this->assertTrue($res->isConnected());
        $this->assertEquals(strlen($string), $stream->getSize());
        $this->assertEquals($string, (string) $stream);
        $this->assertEquals('r+', $stream->getResource()->getMode());
    }

    // this will work for any Iterator class, not just SplFileObject
    public function testStreamizeReturnsStreamObjectForSplFileObject()
    {
        $file_obj = new SplFileObject($this->getFilePathFor('commaNewlineHeader'));
        $stream = streamize($file_obj);
        $this->assertEquals("Bank Name,City,ST,CERT,Acquiring", $stream->read(32));
        $this->assertEquals(" Institution,Closing Date,Update", $stream->read(32));
        $this->assertEquals("d Date\nFirst CornerStone Bank,\"K", $stream->read(32));
    }
}
