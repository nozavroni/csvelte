<?php

use PHPUnit\Framework\TestCase;
use CSVelte\Input\File;
use CSVelte\Input\Stream;

/**
 * CSVelte\Input Tests
 *
 * @package   CSVelte Unit Tests
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class InputTest extends TestCase
{
    public function testCreateNewFile()
    {
        $file = new File(__DIR__ . '/../files/banklist.csv');
        $this->assertEquals($expectedFilename = 'banklist.csv', $file->name());
    }

    public function testStreamReadSpecifiedNumberOfCharacters()
    {
        $denizengarden = file_get_contents(__DIR__ . '/../files/banklist.csv');
        $stream = new Stream('file://' . realpath(__DIR__ . '/../files/banklist.csv'));
        $this->assertEquals($expectedName = 'banklist.csv', $stream->name());
        $this->assertEquals($expectedFirst100 = substr($denizengarden, 0, 100), $stream->read(100));
        // now make sure it picks up from where it left off...
        $this->assertEquals($expectedNext50 = substr($denizengarden, 100, 50), $stream->read(50));
    }
}
