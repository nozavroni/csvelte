<?php
/**
 * CSVelte: Slender, elegant CSV for PHP
 *
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV
 * standardization efforts, CSVelte was written in an effort to take all the
 * suck out of working with CSV.
 *
 * @copyright Copyright (c) 2018 Luke Visinoni
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   See LICENSE file (MIT license)
 */
namespace CSVelteTest\IO;

use CSVelte\IO\Stream;
use CSVelte\IO\StreamResource;
use InvalidArgumentException;

class StreamResourceTest extends IOTest
{
    public function testInstantiateStreamResource()
    {
        $sr = new StreamResource($this->getFilePathFor('veryShort'));
        $this->assertEquals($this->getFilePathFor('veryShort'), $sr->getUri());
        $this->assertEquals("r+b", $sr->getMode());
        $this->assertTrue($sr->isLazy());
        $this->assertTrue($sr->isReadable());
        $this->assertTrue($sr->isWritable());
        $this->assertFalse($sr->isConnected());
        $this->assertTrue(is_resource($sr->getHandle()));
        $this->assertTrue($sr->isConnected());
        $this->assertFalse($sr->getUseIncludePath());
        $this->assertEquals([], $sr->getContextOptions());
        $this->assertEquals([], $sr->getContextParams());
    }

    public function testOpenAndCloseResource()
    {
        $sr = new StreamResource($this->getFilePathFor('veryShort'));
        $this->assertFalse($sr->isConnected());
        $this->assertTrue($sr->connect());
        $this->assertTrue($sr->isConnected());
    }

//    public function testSetUriUsingObjectWithToStringMethod()
//    {
//        $stub = $this->createMock(Stream::class)
//            ->method('__toString')
//            ->willReturn($expected = 'php://temp');
//        $resource = new StreamResource('php://input');
//        dd($stub->__toString());
//        $resource->setUri($stub);
//        $this->assertEquals($expected, $resource->getUri());
//    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInstantiateStreamWithBadResourceTypeThrowsException()
    {
        $or = stream_context_create([
            'foo' => ['bar' => 'this is a context resource']
        ]);
        $sr = new StreamResource($or);
    }

    /**
     * @expectedException CSVelte\Exception\IOException
     * @expectedExceptionCode CSVelte\Exception\IOException::ERR_STREAM_CONNECTION_FAILED
     */
    public function testInstantiateStreamResourceWithBadUriThrowsException()
    {
        $sr = new StreamResource("I am not a uri", null, false);
    }

    /**
     * @expectedException CSVelte\Exception\IOException
     * @expectedExceptionCode CSVelte\Exception\IOException::ERR_STREAM_CONNECTION_FAILED
     */
    public function testConnectStreamResourceWithBadUriThrowsException()
    {
        $sr = new StreamResource("I am not a uri");
        $this->assertFalse($sr->isConnected());
        $sr->connect();
    }

    /**
     * @expectedException CSVelte\Exception\IOException
     * @expectedExceptionCode CSVelte\Exception\IOException::ERR_STREAM_CONNECTION_FAILED
     */
    public function testgetHandleStreamResourceWithBadUriThrowsException()
    {
        $sr = new StreamResource("I am not a uri");
        $this->assertFalse($sr->isConnected());
        $sr->getHandle();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInstantiateStreamResourceWithNonStringNonStreamResourceThrowsException()
    {
        $sr = new StreamResource(false);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetInvalidBaseModeThrowsException()
    {
        $sr = new StreamResource($this->getFilePathFor('veryShort'), 'luke');
    }

    public function testInstantiateALazyResource()
    {
        $sr = new StreamResource($this->getFilePathFor('veryShort'), null, true);
        $this->assertFalse($sr->isConnected());
        $this->assertTrue(is_resource($sr->getHandle()));
        $this->assertTrue($sr->isConnected());
    }

    public function testModeFlags()
    {
        $sr = new StreamResource($this->getFilePathFor('veryShort'), null, true);
        $this->assertEquals("r+b", $sr->getMode());
        $this->assertTrue($sr->isBinary());
        $this->assertFalse($sr->isText());
    }

    public function testInstantiateVariousModes()
    {
        $sr = new StreamResource($this->getFilePathFor('veryShort'), null, true);
        $this->assertEquals("r+b", $sr->getMode());
        $this->assertTrue($sr->isReadable());
        $this->assertTrue($sr->isWritable());
        $this->assertTrue($sr->isBinary());
        $this->assertFalse($sr->isCursorPositionedAtEnd());
        $this->assertTrue($sr->isCursorPositionedAtBeginning());
        $this->assertFalse($sr->isTruncated());
        $this->assertFalse($sr->attemptsFileCreation());
        $this->assertFalse($sr->rejectsExistingFiles());
        $this->assertFalse($sr->appendsWriteOps());
        $sr->setMode('r');
        $this->assertTrue($sr->isReadable());
        $this->assertFalse($sr->isWritable());
        $sr->setIsBinary(true);
        $this->assertEquals("rb", $sr->getMode());
        $this->assertTrue($sr->isReadable());
        $this->assertFalse($sr->isWritable());
        $sr->setIsPlus(true);
        $this->assertEquals("r+b", $sr->getMode());
        $sr->setBaseMode('x')
            ->setIsPlus(true)
            ->setIsText(true);
        $this->assertFalse($sr->isBinary());
        $this->assertTrue($sr->isText());
        $this->assertFalse($sr->isCursorPositionedAtEnd());
        $this->assertTrue($sr->isCursorPositionedAtBeginning());
        $this->assertFalse($sr->isTruncated());
        $this->assertTrue($sr->attemptsFileCreation());
        $this->assertTrue($sr->rejectsExistingFiles());
        $this->assertFalse($sr->appendsWriteOps());
        $sr->setBaseMode('a')
            ->connect();
        $this->assertEquals('a+t', $sr->getMode());
        $this->assertTrue($sr->isCursorPositionedAtEnd());
    }

    public function testSetContextAfterInstantiation()
    {
        $expContextOptions = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => ['foo' => 'bar', 'baz' => 'bin']
            ]
        ];
        $expContextParams = [
            "notification" => "stream_notification_callback"
        ];
        $res = new StreamResource('http://www.example.com/');
        $res->setContext($expContextOptions, $expContextParams);
        $res->connect();
        $meta = stream_get_meta_data($res->getHandle());
        $wrapper = $meta['wrapper_data'];
        $this->assertEquals($expContextOptions, $res->getContextOptions());
        $this->assertEquals($expContextParams, $res->getContextParams());
        $this->assertEquals($expContextOptions, stream_context_get_options($res->getContext()));
        // stream_context_get_params returns an array of params AND options, not just params...
        // important little detail that tripped me up for a bit...
        $this->assertEquals(array_merge($expContextParams, ['options' => $expContextOptions]), stream_context_get_params($res->getContext()));

    }

    // @todo there should be addContextOptions() to add rather than overwrite
    public function testSetOptsAndParamsOnOpenConnectionAndThenChangeThemLater()
    {
        $res = new StreamResource(
            $uri = "http://www.example.com/data/foo.csv",
            $mode = 'rb',
            $lazy = false,
            $use_inc_path = false,
            $options = ['http' => ['method' => 'POST']],
            $params = ['notification' => 'some_func_callback']
        );
        $this->assertTrue($res->isConnected());
        $this->assertEquals($options, stream_context_get_options($res->getContext()));
        $this->assertEquals($params + ["options" => $options], stream_context_get_params($res->getContext()));

        // now change them...
        $res->setContextOptions($newopts = ['header' => 'Content-Type: application/x-www-form-urlencoded'], 'http');
        $res->setContextParams($newparams = ['notification' => 'some_other_func']);

        // old options and params overwritten
        $this->assertEquals($newoptions = ['http' => $options['http'] + $newopts], stream_context_get_options($res->getContext()));
        $this->assertEquals(["options" => $newoptions] + $newparams, stream_context_get_params($res->getContext()));
    }

    public function testSetContextAfterInstantiationUsingContextResource()
    {
        $res = new StreamResource(
            $uri = "http://www.example.com/data/foo.csv",
            $mode = 'rb'
        );
        $context_options = ['http' => ['method' => 'POST']];
        $context_params = ['notification' => 'some_func_callback'];
        $context_resource = stream_context_create($context_options, $context_params);
        $res->setContextResource($context_resource);
        $this->assertEquals($context_resource, $res->getContext());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetContextResourceWithInvalidTypeThrowsException()
    {
        $res = new StreamResource(
            $uri = "http://www.example.com/data/foo.csv",
            $mode = 'rb'
        );
        $context_options = ['http' => ['method' => 'POST']];
        $context_params = ['notification' => 'some_func_callback'];
        $context_resource = stream_context_create($context_options, $context_params);
        $res->setContextResource("string");
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetContextResourceWithInvalidResourceTypeThrowsException()
    {
        $res = new StreamResource(
            $uri = "http://www.example.com/data/foo.csv",
            $mode = 'rb'
        );
        // $context_options = ['http' => ['method' => 'POST']];
        // $context_params = ['notification' => 'some_func_callback'];
        // $context_resource = stream_context_create($context_options, $context_params);
        $res->setContextResource($res->getHandle());
    }

    public function testUseResourceAsFunctionReturnsStreamObjectForResource()
    {
        $res = new StreamResource(
            $uri = "http://www.example.com/data/foo.csv",
            $mode = 'rb'
        );
        $stream = $res();
        $this->assertInstanceOf(Stream::class, $stream);
        $this->assertSame($res, $stream->getResource());
    }

    public function testInstantiateIOResourceAcceptsOpenResourceHandle()
    {
        $handle = fopen(
            'php://output',
            // 'http://www.example.com/',
            // realpath(__DIR__ . '/../../files/banklist.csv'),
            // $this->getFilePathFor('veryShort'),
            'w+',
            null,
            stream_context_create(['http' => ['method' => 'POST']])
        );
        // this is going to have to work like this...
        // $resource = StreamResource::factory($rh);
        // or maybe...
        // $resource = StreamResource::wrap($handle); // winner! nevermind...
        // $resource = StreamResource::accept($handle);
        // $resource = StreamResource::adopt($handle);
        $resource = new StreamResource($handle);
        // php://output, apparently, automatically changes this
        $this->assertEquals('wb', $resource->getMode());
        $this->assertTrue($resource->isWritable());
        $this->assertTrue($resource->isBinary());
        $this->assertFalse($resource->isReadable());
        $this->assertTrue($resource->isCursorPositionedAtBeginning());
        $this->assertFalse($resource->isCursorPositionedAtEnd());
        $this->assertTrue($resource->isTruncated());
    }
}
