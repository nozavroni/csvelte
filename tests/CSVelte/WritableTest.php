<?php
use PHPUnit\Framework\TestCase;
use CSVelte\Output\Stream;
/**
 * CSVelte\Writer Tests
 *
 * @package   CSVelte Unit Tests
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class WritableTest extends TestCase
{
    public function testWriteStream()
    {
        $stream = new Stream('php://memory');
        $data = "I,love,cake!\r\n";
        $this->assertEquals(strlen($data), $stream->write($data));
    }
}
